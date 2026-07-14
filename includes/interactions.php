<?php
require_once __DIR__ . '/../config/db.php';

/* ── Comments ────────────────────────────────────────────── */

if (!function_exists('getCommentsByPost')) {
    function getCommentsByPost(int $postId): array
    {
        $db = getDB();
        $stmt = $db->prepare('
            SELECT c.*, u.username, u.avatar
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.post_id = ?
            ORDER BY c.created_at ASC
        ');
        if (!$stmt) return [];

        $stmt->bind_param('i', $postId);
        $stmt->execute();
        $result = $stmt->get_result();

        $flat = [];
        while ($row = $result->fetch_assoc()) {
            $row['replies'] = [];
            $flat[] = $row;
        }

        $grouped = [];
        $byId = [];
        foreach ($flat as $c) {
            $byId[$c['id']] = $c;
        }
        foreach ($flat as $c) {
            if ($c['parent_id'] && isset($byId[$c['parent_id']])) {
                $byId[$c['parent_id']]['replies'][] = &$byId[$c['id']];
            } else {
                $grouped[] = &$byId[$c['id']];
            }
        }

        return $grouped;
    }
}

if (!function_exists('getCommentCount')) {
    function getCommentCount(int $postId): int
    {
        $db = getDB();
        $stmt = $db->prepare('SELECT COUNT(*) AS total FROM comments WHERE post_id = ?');
        if (!$stmt) return 0;

        $stmt->bind_param('i', $postId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return (int) ($row['total'] ?? 0);
    }
}

if (!function_exists('addComment')) {
    function addComment(int $postId, int $userId, string $content, ?int $parentId = null): int|false
    {
        $db = getDB();
        $content = trim($content);
        if ($content === '') return false;

        if ($parentId !== null) {
            $check = $db->prepare('SELECT id FROM comments WHERE id = ? AND post_id = ?');
            if ($check) {
                $check->bind_param('ii', $parentId, $postId);
                $check->execute();
                if ($check->get_result()->num_rows === 0) {
                    $parentId = null;
                }
            }
        }

        $stmt = $db->prepare('INSERT INTO comments (post_id, user_id, parent_id, content) VALUES (?, ?, ?, ?)');
        if (!$stmt) return false;

        $stmt->bind_param('iiis', $postId, $userId, $parentId, $content);
        if ($stmt->execute()) {
            return $db->insert_id;
        }
        return false;
    }
}

if (!function_exists('deleteComment')) {
    function deleteComment(int $commentId, int $userId): bool
    {
        $db = getDB();
        $stmt = $db->prepare('DELETE FROM comments WHERE id = ? AND user_id = ?');
        if (!$stmt) return false;

        $stmt->bind_param('ii', $commentId, $userId);
        return $stmt->execute() && $stmt->affected_rows > 0;
    }
}

/* ── Likes / Dislikes ────────────────────────────────────── */

if (!function_exists('getLikeCounts')) {
    function getLikeCounts(int $postId): array
    {
        $db = getDB();
        $stmt = $db->prepare('
            SELECT type, COUNT(*) AS total
            FROM likes_dislikes
            WHERE post_id = ?
            GROUP BY type
        ');
        if (!$stmt) return ['likes' => 0, 'dislikes' => 0];

        $stmt->bind_param('i', $postId);
        $stmt->execute();
        $result = $stmt->get_result();

        $counts = ['likes' => 0, 'dislikes' => 0];
        while ($row = $result->fetch_assoc()) {
            $counts[$row['type'] . 's'] = (int) $row['total'];
        }
        return $counts;
    }
}

if (!function_exists('getUserVote')) {
    function getUserVote(int $postId, int $userId): ?string
    {
        $db = getDB();
        $stmt = $db->prepare('SELECT type FROM likes_dislikes WHERE post_id = ? AND user_id = ?');
        if (!$stmt) return null;

        $stmt->bind_param('ii', $postId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['type'];
        }
        return null;
    }
}

if (!function_exists('toggleLike')) {
    function toggleLike(int $postId, int $userId): array
    {
        $db = getDB();
        $current = getUserVote($postId, $userId);

        if ($current === 'like') {
            $stmt = $db->prepare('DELETE FROM likes_dislikes WHERE post_id = ? AND user_id = ?');
            if ($stmt) {
                $stmt->bind_param('ii', $postId, $userId);
                $stmt->execute();
            }
        } else {
            if ($current === 'dislike') {
                $stmt = $db->prepare('DELETE FROM likes_dislikes WHERE post_id = ? AND user_id = ?');
                if ($stmt) {
                    $stmt->bind_param('ii', $postId, $userId);
                    $stmt->execute();
                }
            }
            $stmt = $db->prepare('INSERT INTO likes_dislikes (post_id, user_id, type) VALUES (?, ?, "like")');
            if ($stmt) {
                $stmt->bind_param('ii', $postId, $userId);
                $stmt->execute();
            }
        }

        $counts = getLikeCounts($postId);
        return [
            'user_vote' => getUserVote($postId, $userId),
            'likes'     => $counts['likes'],
            'dislikes'  => $counts['dislikes'],
        ];
    }
}

if (!function_exists('toggleDislike')) {
    function toggleDislike(int $postId, int $userId): array
    {
        $db = getDB();
        $current = getUserVote($postId, $userId);

        if ($current === 'dislike') {
            $stmt = $db->prepare('DELETE FROM likes_dislikes WHERE post_id = ? AND user_id = ?');
            if ($stmt) {
                $stmt->bind_param('ii', $postId, $userId);
                $stmt->execute();
            }
        } else {
            if ($current === 'like') {
                $stmt = $db->prepare('DELETE FROM likes_dislikes WHERE post_id = ? AND user_id = ?');
                if ($stmt) {
                    $stmt->bind_param('ii', $postId, $userId);
                    $stmt->execute();
                }
            }
            $stmt = $db->prepare('INSERT INTO likes_dislikes (post_id, user_id, type) VALUES (?, ?, "dislike")');
            if ($stmt) {
                $stmt->bind_param('ii', $postId, $userId);
                $stmt->execute();
            }
        }

        $counts = getLikeCounts($postId);
        return [
            'user_vote' => getUserVote($postId, $userId),
            'likes'     => $counts['likes'],
            'dislikes'  => $counts['dislikes'],
        ];
    }
}

/* ── Render helper (used by article.php + API endpoints) ── */

if (!function_exists('renderComments')) {
    function renderComments(array $comments, int $postId): void
    {
        foreach ($comments as $comment):
            $avatar   = $comment['avatar'] ? '/Nova_News/' . htmlspecialchars($comment['avatar']) : null;
            $username = htmlspecialchars($comment['username']);
            $text     = nl2br(htmlspecialchars($comment['content']));
            $date     = date('M j, Y \a\t g:i A', strtotime($comment['created_at']));
            $cid      = (int) $comment['id'];
            $isOwner  = isLoggedIn() && currentUserId() === (int) $comment['user_id'];
?>
<div class="flex gap-3" id="comment-<?= $cid ?>">
    <div class="shrink-0">
        <?php if ($avatar): ?>
            <img src="<?= $avatar ?>" alt="<?= $username ?>" class="w-9 h-9 rounded-full object-cover">
        <?php else: ?>
            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white text-xs font-bold">
                <?= strtoupper(mb_substr($comment['username'], 0, 1)) ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 mb-1">
            <span class="text-sm font-semibold text-slate-800"><?= $username ?></span>
            <span class="text-xs text-slate-400"><?= $date ?></span>
            <?php if ($isOwner): ?>
                <button onclick="deleteComment(<?= $cid ?>, <?= $postId ?>)" class="text-xs text-red-400 hover:text-red-600 ml-auto" title="Delete"><i class="fa-solid fa-trash-can"></i></button>
            <?php endif; ?>
        </div>
        <div class="text-sm text-slate-700 leading-relaxed"><?= $text ?></div>
        <button onclick="toggleReplyForm(<?= $cid ?>)" class="text-xs text-[#5B41FF] hover:text-[#4830DF] mt-1 font-medium">Reply</button>

        <div id="reply-form-<?= $cid ?>" class="hidden mt-2">
            <div class="flex gap-2">
                <input id="reply-input-<?= $cid ?>" type="text" placeholder="Write a reply..." maxlength="2000"
                    class="flex-1 px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#5B41FF]/30">
                <button onclick="submitReply(<?= $postId ?>, <?= $cid ?>)"
                    class="px-3 py-1.5 bg-[#5B41FF] text-white text-xs font-semibold rounded-lg hover:bg-[#4830DF] transition">Send</button>
            </div>
        </div>

        <?php if (!empty($comment['replies'])): ?>
            <div class="mt-3 pl-4 border-l-2 border-slate-100 space-y-3">
                <?php renderComments($comment['replies'], $postId); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
        endforeach;
    }
}

/* ── Admin CRUD helpers for comments ──────────────────────── */

if (!function_exists('getCommentsPaginated')) {
    function getCommentsPaginated(int $page = 1, int $limit = 15, string $search = '', string $postId = ''): array
    {
        $db = getDB();
        $page   = max(1, $page);
        $limit  = max(1, $limit);
        $offset = ($page - 1) * $limit;

        $conditions = [];
        $params     = [];
        $types      = '';

        if ($postId !== '' && $postId !== 'all') {
            $conditions[] = 'c.post_id = ?';
            $params[]     = (int) $postId;
            $types       .= 'i';
        }

        if ($search !== '') {
            $conditions[] = '(u.username LIKE ? OR p.title LIKE ? OR c.content LIKE ?)';
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $types .= 'sss';
        }

        $sql = 'SELECT c.*, u.username, p.title AS post_title, p.slug AS post_slug,
                       pc.content AS parent_content
                FROM comments c
                JOIN users u ON c.user_id = u.id
                JOIN posts p ON c.post_id = p.id
                LEFT JOIN comments pc ON c.parent_id = pc.id';

        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY c.created_at DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;
        $types   .= 'ii';

        $stmt = $db->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }
}

if (!function_exists('getCommentsCount')) {
    function getCommentsCount(string $search = '', string $postId = ''): int
    {
        $db = getDB();

        $conditions = [];
        $params     = [];
        $types      = '';

        if ($postId !== '' && $postId !== 'all') {
            $conditions[] = 'c.post_id = ?';
            $params[]     = (int) $postId;
            $types       .= 'i';
        }

        if ($search !== '') {
            $conditions[] = '(u.username LIKE ? OR p.title LIKE ? OR c.content LIKE ?)';
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
            $types .= 'sss';
        }

        $sql = 'SELECT COUNT(*) AS total
                FROM comments c
                JOIN users u ON c.user_id = u.id
                JOIN posts p ON c.post_id = p.id';

        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $stmt = $db->prepare($sql);
        if (!$stmt) return 0;

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return (int) ($row['total'] ?? 0);
    }
}

if (!function_exists('getCommentByIdAdmin')) {
    function getCommentByIdAdmin(int $id): ?array
    {
        $db = getDB();
        $stmt = $db->prepare('
            SELECT c.*, u.username, p.title AS post_title
            FROM comments c
            JOIN users u ON c.user_id = u.id
            JOIN posts p ON c.post_id = p.id
            WHERE c.id = ?
        ');
        if (!$stmt) return null;

        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }
}

if (!function_exists('deleteCommentAdmin')) {
    function deleteCommentAdmin(int $id): bool
    {
        $db = getDB();
        $stmt = $db->prepare('DELETE FROM comments WHERE id = ?');
        if (!$stmt) return false;

        $stmt->bind_param('i', $id);
        return $stmt->execute() && $stmt->affected_rows > 0;
    }
}

if (!function_exists('getPostsForDropdown')) {
    function getPostsForDropdown(): array
    {
        $db = getDB();
        $result = $db->query('SELECT id, title FROM posts ORDER BY title ASC');
        $posts = [];
        if ($result && method_exists($result, 'fetch_assoc')) {
            while ($row = $result->fetch_assoc()) {
                $posts[] = $row;
            }
        }
        return $posts;
    }
}

if (!function_exists('getUsersForDropdown')) {
    function getUsersForDropdown(): array
    {
        $db = getDB();
        $result = $db->query('SELECT id, username FROM users ORDER BY username ASC');
        $users = [];
        if ($result && method_exists($result, 'fetch_assoc')) {
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
        }
        return $users;
    }
}

/* ── Admin CRUD helpers for likes_dislikes ────────────────── */

if (!function_exists('getLikesDislikesPaginated')) {
    function getLikesDislikesPaginated(int $page = 1, int $limit = 15, string $search = '', string $type = 'all'): array
    {
        $db = getDB();
        $page   = max(1, $page);
        $limit  = max(1, $limit);
        $offset = ($page - 1) * $limit;

        $conditions = [];
        $params     = [];
        $types      = '';

        if ($type !== 'all' && in_array($type, ['like', 'dislike'])) {
            $conditions[] = 'ld.type = ?';
            $params[]     = $type;
            $types       .= 's';
        }

        if ($search !== '') {
            $conditions[] = '(u.username LIKE ? OR p.title LIKE ?)';
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
            $types .= 'ss';
        }

        $sql = 'SELECT ld.*, u.username, p.title AS post_title, p.slug AS post_slug
                FROM likes_dislikes ld
                JOIN users u ON ld.user_id = u.id
                JOIN posts p ON ld.post_id = p.id';

        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY ld.created_at DESC LIMIT ? OFFSET ?';
        $params[] = $limit;
        $params[] = $offset;
        $types   .= 'ii';

        $stmt = $db->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }
}

if (!function_exists('getLikesDislikesCount')) {
    function getLikesDislikesCount(string $search = '', string $type = 'all'): int
    {
        $db = getDB();

        $conditions = [];
        $params     = [];
        $types      = '';

        if ($type !== 'all' && in_array($type, ['like', 'dislike'])) {
            $conditions[] = 'ld.type = ?';
            $params[]     = $type;
            $types       .= 's';
        }

        if ($search !== '') {
            $conditions[] = '(u.username LIKE ? OR p.title LIKE ?)';
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
            $types .= 'ss';
        }

        $sql = 'SELECT COUNT(*) AS total
                FROM likes_dislikes ld
                JOIN users u ON ld.user_id = u.id
                JOIN posts p ON ld.post_id = p.id';

        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $stmt = $db->prepare($sql);
        if (!$stmt) return 0;

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return (int) ($row['total'] ?? 0);
    }
}

if (!function_exists('getLikeDislikeById')) {
    function getLikeDislikeById(int $id): ?array
    {
        $db = getDB();
        $stmt = $db->prepare('
            SELECT ld.*, u.username, p.title AS post_title
            FROM likes_dislikes ld
            JOIN users u ON ld.user_id = u.id
            JOIN posts p ON ld.post_id = p.id
            WHERE ld.id = ?
        ');
        if (!$stmt) return null;

        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        return null;
    }
}

if (!function_exists('createLikeDislike')) {
    function createLikeDislike(int $postId, int $userId, string $type): int|false
    {
        $db = getDB();
        if (!in_array($type, ['like', 'dislike'])) return false;

        $stmt = $db->prepare('INSERT INTO likes_dislikes (post_id, user_id, type) VALUES (?, ?, ?)');
        if (!$stmt) return false;

        $stmt->bind_param('iis', $postId, $userId, $type);
        if ($stmt->execute()) {
            return $db->insert_id;
        }
        return false;
    }
}

if (!function_exists('updateLikeDislike')) {
    function updateLikeDislike(int $id, int $postId, int $userId, string $type): bool
    {
        $db = getDB();
        if (!in_array($type, ['like', 'dislike'])) return false;

        $stmt = $db->prepare('UPDATE likes_dislikes SET post_id = ?, user_id = ?, type = ? WHERE id = ?');
        if (!$stmt) return false;

        $stmt->bind_param('iisi', $postId, $userId, $type, $id);
        return $stmt->execute();
    }
}

if (!function_exists('deleteLikeDislike')) {
    function deleteLikeDislike(int $id): bool
    {
        $db = getDB();
        $stmt = $db->prepare('DELETE FROM likes_dislikes WHERE id = ?');
        if (!$stmt) return false;

        $stmt->bind_param('i', $id);
        return $stmt->execute() && $stmt->affected_rows > 0;
    }
}
