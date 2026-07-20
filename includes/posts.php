<?php
require_once __DIR__ . '/../config/db.php';

/**
 * Return the available categories for the site navigation.
 *
 * This file is included by includes/header.php and provides a
 * lightweight fallback when category data is not stored in the database.
 */
function getAllCategories(): array
{
    $db = getDB();
    $categories = [];

    $result = $db->query('
        SELECT c.id, c.name, c.slug, COUNT(p.id) AS article_count
        FROM categories c
        LEFT JOIN posts p ON p.category_id = c.id AND p.status = "published"
        GROUP BY c.id
        ORDER BY c.name ASC
    ');
    if ($result && method_exists($result, 'fetch_assoc')) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }

    return $categories;
}

function getPostStats(): array
{
    $db = getDB();
    $stats = ['total' => 0, 'free_count' => 0, 'premium_count' => 0];

    $result = $db->query("SELECT post_type, COUNT(*) AS total FROM posts GROUP BY post_type");
    if ($result && method_exists($result, 'fetch_assoc')) {
        while ($row = $result->fetch_assoc()) {
            if (($row['post_type'] ?? '') === 'premium') {
                $stats['premium_count'] = (int) $row['total'];
            } elseif (($row['post_type'] ?? '') === 'free') {
                $stats['free_count'] = (int) $row['total'];
            }
        }
    }

    $stats['total'] = $stats['free_count'] + $stats['premium_count'];
    return $stats;
}

function getAllPosts(int $page = 1, int $limit = 10, string $type = 'all', int $offset = 0, string $status = 'all'): array
{
    $db = getDB();
    $page = max(1, $page);
    $limit = max(1, $limit);
    $offset = max(0, $offset);

    $query = 'SELECT p.*, u.username AS author_name FROM posts p LEFT JOIN users u ON p.created_by = u.id';
    $conditions = [];

    if ($type !== 'all') {
        $conditions[] = "p.post_type = '" . str_replace(["'", "\\"], ["\\'", "\\\\"], $type) . "'";
    }

    if ($status !== 'all') {
        $conditions[] = "p.status = '" . str_replace(["'", "\\"], ["\\'", "\\\\"], $status) . "'";
    }

    if (!empty($conditions)) {
        $query .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $query .= ' ORDER BY p.created_at DESC LIMIT ' . (int) $limit . ' OFFSET ' . (int) $offset;

    $result = $db->query($query);
    $posts = [];

    if ($result && method_exists($result, 'fetch_assoc')) {
        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }
    }

    return ['posts' => $posts, 'page' => $page, 'limit' => $limit, 'total' => count($posts)];
}

function getPostById(int $id): ?array
{
    $db = getDB();
    $stmt = $db->prepare('SELECT p.*, u.username AS author_name FROM posts p LEFT JOIN users u ON p.created_by = u.id WHERE p.id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
    }
    return null;
}

function getPostBySlug(string $slug): ?array
{
    $db = getDB();
    $stmt = $db->prepare('SELECT p.*, u.username AS author_name, c.name AS category_name FROM posts p LEFT JOIN users u ON p.created_by = u.id LEFT JOIN categories c ON p.category_id = c.id WHERE p.slug = ?');
    if ($stmt) {
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
    }
    return null;
}

function createPost(string $title, string $slug, string $content, ?string $excerpt, ?string $imageUrl, string $postType, ?int $categoryId, string $status, int $createdBy, int $isFeatured = 0, int $isBreaking = 0, int $isEditorsPick = 0): int|false
{
    $db = getDB();
    $stmt = $db->prepare('INSERT INTO posts (title, slug, content, excerpt, image_url, post_type, category_id, is_featured, is_breaking, is_editors_pick, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    if ($stmt) {
        $stmt->bind_param('ssssssiiiiss', $title, $slug, $content, $excerpt, $imageUrl, $postType, $categoryId, $isFeatured, $isBreaking, $isEditorsPick, $status, $createdBy);
        if ($stmt->execute()) {
            return $db->insert_id;
        }
    }
    return false;
}

function updatePost(int $id, string $title, string $slug, string $content, ?string $excerpt, ?string $imageUrl, string $postType, ?int $categoryId, string $status, int $isFeatured = 0, int $isBreaking = 0, int $isEditorsPick = 0): bool
{
    $db = getDB();
    $stmt = $db->prepare('UPDATE posts SET title = ?, slug = ?, content = ?, excerpt = ?, image_url = ?, post_type = ?, category_id = ?, is_featured = ?, is_breaking = ?, is_editors_pick = ?, status = ? WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('ssssssiiiisi', $title, $slug, $content, $excerpt, $imageUrl, $postType, $categoryId, $isFeatured, $isBreaking, $isEditorsPick, $status, $id);
        return $stmt->execute();
    }
    return false;
}

function toggleFeatured(int $id): bool
{
    $db = getDB();
    $stmt = $db->prepare('UPDATE posts SET is_featured = NOT is_featured WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
    return false;
}

function toggleBreaking(int $id): bool
{
    $db = getDB();
    $stmt = $db->prepare('UPDATE posts SET is_breaking = NOT is_breaking WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
    return false;
}

function toggleEditorsPick(int $id): bool
{
    $db = getDB();
    $stmt = $db->prepare('UPDATE posts SET is_editors_pick = NOT is_editors_pick WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
    return false;
}

function incrementViewCount(int $id): void
{
    $db = getDB();
    $stmt = $db->prepare('UPDATE posts SET view_count = view_count + 1 WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
}

function getFeaturedPosts(int $limit = 4): array
{
    $db = getDB();
    $stmt = $db->prepare('
        SELECT p.*, u.username AS author_name, c.name AS category_name
        FROM posts p
        LEFT JOIN users u ON p.created_by = u.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = "published" AND p.is_featured = 1
        ORDER BY p.created_at DESC
        LIMIT ?
    ');
    if ($stmt) {
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $posts = [];
        if ($result && method_exists($result, 'fetch_assoc')) {
            while ($row = $result->fetch_assoc()) {
                $posts[] = $row;
            }
        }
        return $posts;
    }
    return [];
}

function getTrendingPosts(int $limit = 3): array
{
    $db = getDB();
    $stmt = $db->prepare('
        SELECT p.*, u.username AS author_name, c.name AS category_name
        FROM posts p
        LEFT JOIN users u ON p.created_by = u.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = "published"
        ORDER BY p.view_count DESC, p.created_at DESC
        LIMIT ?
    ');
    if ($stmt) {
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $posts = [];
        if ($result && method_exists($result, 'fetch_assoc')) {
            while ($row = $result->fetch_assoc()) {
                $posts[] = $row;
            }
        }
        return $posts;
    }
    return [];
}

function deletePost(int $id): bool
{
    $db = getDB();
    $stmt = $db->prepare('DELETE FROM posts WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
    return false;
}

function getPostsCount(string $type = 'all', string $status = 'all', string $search = ''): int
{
    $db = getDB();
    $conditions = [];
    $params = [];
    $types = '';

    if ($type !== 'all') {
        $conditions[] = 'post_type = ?';
        $params[] = $type;
        $types .= 's';
    }

    if ($status !== 'all') {
        $conditions[] = 'status = ?';
        $params[] = $status;
        $types .= 's';
    }

    if ($search !== '') {
        $conditions[] = 'title LIKE ?';
        $params[] = '%' . $search . '%';
        $types .= 's';
    }

    $sql = 'SELECT COUNT(*) AS total FROM posts';
    if (!empty($conditions)) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $stmt = $db->prepare($sql);
    if ($stmt && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result) {
            $row = $result->fetch_assoc();
            return (int) ($row['total'] ?? 0);
        }
    }
    return 0;
}

function getPostsPaginated(int $page = 1, int $limit = 10, string $type = 'all', string $status = 'all', string $search = '', ?int $featured = null): array
{
    $db = getDB();
    $page = max(1, $page);
    $limit = max(1, $limit);
    $offset = ($page - 1) * $limit;

    $conditions = [];
    $params = [];
    $types = '';

    if ($type !== 'all') {
        $conditions[] = 'p.post_type = ?';
        $params[] = $type;
        $types .= 's';
    }

    if ($status !== 'all') {
        $conditions[] = 'p.status = ?';
        $params[] = $status;
        $types .= 's';
    }

    if ($search !== '') {
        $conditions[] = 'p.title LIKE ?';
        $params[] = '%' . $search . '%';
        $types .= 's';
    }

    if ($featured !== null) {
        $conditions[] = 'p.is_featured = ?';
        $params[] = $featured;
        $types .= 'i';
    }

    $sql = 'SELECT p.*, u.username AS author_name, c.name AS category_name
            FROM posts p
            LEFT JOIN users u ON p.created_by = u.id
            LEFT JOIN categories c ON p.category_id = c.id';

    if (!empty($conditions)) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $sql .= ' ORDER BY p.created_at DESC LIMIT ? OFFSET ?';
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    $stmt = $db->prepare($sql);
    if ($stmt) {
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $posts = [];
        if ($result && method_exists($result, 'fetch_assoc')) {
            while ($row = $result->fetch_assoc()) {
                $posts[] = $row;
            }
        }
        return $posts;
    }
    return [];
}

function getPostsByCategory(int $categoryId, int $page = 1, int $limit = 10, string $type = 'all'): array
{
    $db = getDB();
    $page = max(1, $page);
    $limit = max(1, $limit);
    $offset = ($page - 1) * $limit;

    $conditions = ["p.category_id = ?", "p.status = 'published'"];
    $params = [$categoryId];
    $types = 'i';

    if ($type !== 'all') {
        $conditions[] = 'p.post_type = ?';
        $params[] = $type;
        $types .= 's';
    }

    $sql = 'SELECT p.*, u.username AS author_name, c.name AS category_name
            FROM posts p
            LEFT JOIN users u ON p.created_by = u.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE ' . implode(' AND ', $conditions) . '
            ORDER BY p.created_at DESC LIMIT ? OFFSET ?';
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    $stmt = $db->prepare($sql);
    if ($stmt) {
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $posts = [];
        if ($result && method_exists($result, 'fetch_assoc')) {
            while ($row = $result->fetch_assoc()) {
                $posts[] = $row;
            }
        }
        return $posts;
    }
    return [];
}

function getBreakingPosts(int $limit = 5): array
{
    $db = getDB();
    $stmt = $db->prepare('
        SELECT p.*, u.username AS author_name, c.name AS category_name
        FROM posts p
        LEFT JOIN users u ON p.created_by = u.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = "published" AND p.is_breaking = 1
        ORDER BY p.created_at DESC
        LIMIT ?
    ');
    if ($stmt) {
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $posts = [];
        if ($result && method_exists($result, 'fetch_assoc')) {
            while ($row = $result->fetch_assoc()) {
                $posts[] = $row;
            }
        }
        return $posts;
    }
    return [];
}

function getEditorsPickPost(): ?array
{
    $db = getDB();
    $stmt = $db->prepare('
        SELECT p.*, u.username AS author_name, c.name AS category_name
        FROM posts p
        LEFT JOIN users u ON p.created_by = u.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = "published" AND p.is_editors_pick = 1
        ORDER BY p.created_at DESC
        LIMIT 1
    ');
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
    }
    return null;
}

const UPLOAD_DIR = __DIR__ . '/../uploads/posts/';
const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

function uploadImage(array $file): string|false
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) {
        return false;
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        return false;
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $dest = UPLOAD_DIR . $filename;

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return 'uploads/posts/' . $filename;
    }

    return false;
}

function deleteImage(string $path): void
{
    if ($path) {
        $full = __DIR__ . '/../' . $path;
        if (file_exists($full)) {
            @unlink($full);
        }
    }
}

function formatContent(string $html): string
{
    $html = trim($html);
    if ($html === '') {
        return '';
    }

    $html = htmlspecialchars($html, ENT_QUOTES, 'UTF-8');

    $paragraphs = preg_split('/\n{2,}/', $html);
    $parts = [];

    foreach ($paragraphs as $para) {
        $para = trim($para);
        if ($para === '') {
            continue;
        }
        $para = nl2br($para, false);
        $parts[] = '<p>' . $para . '</p>';
    }

    return implode("\n", $parts);
}
