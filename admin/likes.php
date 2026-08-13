<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/interactions.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/notifications.php';

requireAdmin();

$notifCounts = getNotificationCounts();
$totalNotifs = array_sum($notifCounts);

$displayName = trim($_SESSION['username'] ?? 'Admin');
$displayInitial = strtoupper(substr($displayName, 0, 1));
$displayRole = (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'Administrator' : 'Member';

$flash = getFlash();

$page   = max(1, (int)($_GET['page'] ?? 1));
$search = trim($_GET['search'] ?? '');
$type   = $_GET['type'] ?? 'all';

$total  = getLikesDislikesCount($search, $type);
$perPage = 15;
$totalPages = max(1, (int)ceil($total / $perPage));
$page = min($page, $totalPages);

$likes = getLikesDislikesPaginated($page, $perPage, $search, $type);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova News - Likes &amp; Dislikes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-slate-100">

<?php include __DIR__ . '/../includes/admin-sidebar.php'; ?>

<!-- Main -->
    <div class="ml-64 flex flex-col h-screen">
        <?php $pageTitle = 'Likes &amp; Dislikes'; include __DIR__ . '/../includes/admin-topbar.php'; ?>

        <div class="flex-1 overflow-y-auto p-6 space-y-6">

            <?php if ($flash): ?>
                <div class="mb-6 px-5 py-4 rounded-xl shadow-sm text-xs font-medium <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' ?>">
                    <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-2"></i>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 mb-5 p-4">
                <form method="get" action="" class="flex flex-wrap items-end gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-semibold text-slate-900 mb-1">Search</label>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by username or post title..." class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-xl border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-900 mb-1">Type</label>
                        <select name="type" class="px-3 py-1.5 text-xs border border-slate-200 rounded-xl border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                            <option value="all" <?= $type === 'all' ? 'selected' : '' ?>>All Types</option>
                            <option value="like" <?= $type === 'like' ? 'selected' : '' ?>>Like</option>
                            <option value="dislike" <?= $type === 'dislike' ? 'selected' : '' ?>>Dislike</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-3 py-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl text-xs shadow-sm transition-all text-sm font-semibold hover:bg-blue-700 transition">
                            <i class="fa-solid fa-filter mr-1"></i> Filter
                        </button>
                        <a href="likes.php" class="px-3 py-1.5 text-xs border border-slate-200 text-slate-900 rounded-lg text-sm font-semibold hover:bg-slate-50/50 transition-colors transition">
                            <i class="fa-solid fa-undo mr-1"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 w-full overflow-x-auto">
                <div class="border-b p-5 flex justify-between items-center">
                    <h3 class="text-xl font-bold">All Votes <span class="text-xs font-normal text-slate-500">(<?= number_format($total) ?> total)</span></h3>
                </div>

                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-slate-50/50 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            <th class="px-3 py-2 text-xs">No</th>
                            <th class="px-3 py-2 text-xs">User</th>
                            <th class="px-3 py-2 text-xs">Post</th>
                            <th class="px-3 py-2 text-xs">Type</th>
                            <th class="px-3 py-2 text-xs">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($likes)): ?>
                            <tr>
                                <td colspan="5" class="p-10 text-center text-slate-500">
                                    <i class="fa-solid fa-thumbs-up text-4xl mb-3 block"></i>
                                    No likes or dislikes found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $seq = isset($page, $limit) ? ($page - 1) * $limit + 1 : 1; ?>
                            <?php foreach ($likes as $like): ?>
                                <tr class="border-b hover:bg-slate-50/50 transition-colors">
                                    <td class="px-3 py-2 text-xs text-slate-500"><?= $seq++ ?></td>
                                    <td class="px-3 py-2 text-xs font-medium text-blue-600"><?= htmlspecialchars($like['username']) ?></td>
                                    <td class="px-3 py-2 text-xs max-w-xs line-clamp-2 whitespace-normal break-words">
                                        <a href="/Nova_News/public/article.php?slug=<?= htmlspecialchars($like['post_slug']) ?>" target="_blank" class="hover:underline">
                                            <?= htmlspecialchars($like['post_title']) ?>
                                        </a>
                                    </td>
                                    <td class="px-3 py-2 text-xs">
                                        <?php if ($like['type'] === 'like'): ?>
                                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium">
                                                <i class="fa-solid fa-thumbs-up mr-1"></i> Like
                                            </span>
                                        <?php else: ?>
                                            <span class="bg-red-100 text-red-600 px-3 py-1 rounded-full text-xs font-medium">
                                                <i class="fa-solid fa-thumbs-down mr-1"></i> Dislike
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-3 py-2 text-xs text-slate-500 text-sm"><?= htmlspecialchars(date('M j, Y', strtotime($like['created_at']))) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="flex items-center justify-center gap-2 mt-6">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($type) ?>" class="px-3 py-2 bg-white border rounded-lg text-xs font-medium hover:bg-slate-50/50 transition-colors">
                            <i class="fa-solid fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    <?php
                    $start = max(1, $page - 2);
                    $end   = min($totalPages, $page + 2);
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($type) ?>"
                           class="px-3 py-2 rounded-lg text-xs font-medium <?= $i === $page ? 'bg-blue-600 text-white' : 'bg-white border hover:bg-slate-50/50 transition-colors' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&type=<?= urlencode($type) ?>" class="px-3 py-2 bg-white border rounded-lg text-xs font-medium hover:bg-slate-50/50 transition-colors">
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>

</body>
</html>
