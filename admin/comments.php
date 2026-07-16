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

$page    = max(1, (int)($_GET['page'] ?? 1));
$search  = trim($_GET['search'] ?? '');
$postId  = $_GET['post_id'] ?? 'all';

$total   = getCommentsCount($search, $postId);
$perPage = 15;
$totalPages = max(1, (int)ceil($total / $perPage));
$page = min($page, $totalPages);

$comments = getCommentsPaginated($page, $perPage, $search, $postId);
$posts    = getPostsForDropdown();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova News - Comments</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-gray-100">

<!-- Sidebar -->
    <aside class="fixed left-0 top-0 h-screen overflow-y-auto z-50 w-72 bg-slate-900 text-white flex flex-col">
        <div class="h-16 flex items-center px-6 border-b border-slate-700">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center text-2xl font-bold text-white">
                    <?= htmlspecialchars($displayInitial) ?>
                </div>
                <div>
                    <h3 class="font-semibold"><?= htmlspecialchars($displayName) ?></h3>
                    <p class="text-sm text-green-400">● <?= htmlspecialchars($displayRole) ?></p>
                </div>
            </div>
        </div>
        <nav class="mt-6 flex-1">
            <a href="index.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-house mr-4"></i> Dashboard
            </a>
            <a href="posts.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-newspaper mr-4"></i> Posts
            </a>
            <a href="likes.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-thumbs-up mr-4"></i> Likes &amp; Dislikes
            </a>
            <a href="comments.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-comments mr-4"></i> Comments
            </a>
            <a href="categories.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-folder mr-4"></i> Categories
            </a>
            <a href="users.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-users mr-4"></i> Users
            </a>
            <a href="plans.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-gem mr-4"></i> Subscription Plans
            </a>
            <a href="user-subscriptions.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-file-contract mr-4"></i> User Subscriptions
            </a>
            <a href="payments.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-credit-card mr-4"></i> Payments
            </a>
            <a href="payment-services.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-money-bill-transfer mr-4"></i> Payment Services
            </a>
           
        </nav>
        <a href="/Nova_News/public/logout.php" class="flex items-center px-6 py-4 hover:bg-red-600">
            <i class="fa-solid fa-right-from-bracket mr-4"></i> Logout
        </a>
    </aside>

<!-- Main -->
    <div class="ml-72 flex flex-col h-screen">
        <header class="bg-white shadow h-16 flex justify-between items-center px-8 shrink-0">
            <h2 class="text-xl font-bold">Comments</h2>
            <div class="flex items-center gap-6">
                <?php include __DIR__ . '/../includes/admin-header.php'; ?>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-sm font-bold text-white">
                        <?= htmlspecialchars($displayInitial) ?>
                    </div>
                    <span class="font-semibold"><?= htmlspecialchars($displayName) ?></span>
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8 space-y-10">

            <?php if ($flash): ?>
                <div class="mb-6 px-5 py-4 rounded-xl shadow-sm text-sm font-medium <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' ?>">
                    <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-2"></i>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="bg-white rounded-xl shadow mb-7 p-6">
                <form method="get" action="" class="flex flex-wrap items-end gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Search</label>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by username, post, or content..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Post</label>
                        <select name="post_id" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="all" <?= $postId === 'all' ? 'selected' : '' ?>>All Posts</option>
                            <?php foreach ($posts as $post): ?>
                                <option value="<?= (int) $post['id'] ?>" <?= $postId === (string) $post['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(mb_strimwidth($post['title'], 0, 40, '...')) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
                            <i class="fa-solid fa-filter mr-1"></i> Filter
                        </button>
                        <a href="comments.php" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50 transition">
                            <i class="fa-solid fa-undo mr-1"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow w-full overflow-x-auto">
                <div class="border-b p-5">
                    <h3 class="text-xl font-bold">All Comments <span class="text-sm font-normal text-gray-500">(<?= number_format($total) ?> total)</span></h3>
                </div>

                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-gray-50 text-left text-sm font-semibold text-gray-600">
                            <th class="p-5">ID</th>
                            <th class="p-5">User</th>
                            <th class="p-5">Post</th>
                            <th class="p-5">Parent</th>
                            <th class="p-5">Content</th>
                            <th class="p-5">Created</th>
                            <th class="p-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($comments)): ?>
                            <tr>
                                <td colspan="7" class="p-10 text-center text-gray-500">
                                    <i class="fa-solid fa-comments text-4xl mb-3 block"></i>
                                    No comments found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($comments as $c): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="p-5 text-gray-500"><?= (int) $c['id'] ?></td>
                                    <td class="p-5 font-medium text-blue-700"><?= htmlspecialchars($c['username']) ?></td>
                                    <td class="p-5 max-w-[150px] truncate">
                                        <a href="/Nova_News/public/article.php?slug=<?= htmlspecialchars($c['post_slug']) ?>" target="_blank" class="hover:underline">
                                            <?= htmlspecialchars(mb_strimwidth($c['post_title'], 0, 35, '...')) ?>
                                        </a>
                                    </td>
                                    <td class="p-5 text-gray-500 text-sm">
                                        <?php if ($c['parent_id']): ?>
                                            <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-xs">#<?= (int) $c['parent_id'] ?></span>
                                        <?php else: ?>
                                            <span class="text-gray-300">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-5 max-w-[250px] truncate text-sm text-gray-700"><?= htmlspecialchars(mb_strimwidth($c['content'], 0, 50, '...')) ?></td>
                                    <td class="p-5 text-gray-500 text-sm"><?= htmlspecialchars(date('M j, Y', strtotime($c['created_at']))) ?></td>
                                    <td class="p-5 text-right whitespace-nowrap">
                                        <form method="post" action="comment-delete.php" class="inline" onsubmit="return confirm('Delete this comment?');">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="flex items-center justify-center gap-2 mt-6">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&post_id=<?= urlencode($postId) ?>" class="px-3 py-2 bg-white border rounded-lg text-sm font-medium hover:bg-gray-50">
                            <i class="fa-solid fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    <?php
                    $start = max(1, $page - 2);
                    $end   = min($totalPages, $page + 2);
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&post_id=<?= urlencode($postId) ?>"
                           class="px-3 py-2 rounded-lg text-sm font-medium <?= $i === $page ? 'bg-blue-600 text-white' : 'bg-white border hover:bg-gray-50' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&post_id=<?= urlencode($postId) ?>" class="px-3 py-2 bg-white border rounded-lg text-sm font-medium hover:bg-gray-50">
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
    </div>

</body>
</html>
