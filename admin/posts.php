<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/posts.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/notifications.php';

requireAdmin();

$notifCounts = getNotificationCounts();
$totalNotifs = array_sum($notifCounts);

$displayName = trim($_SESSION['username'] ?? 'Admin');
$displayInitial = strtoupper(substr($displayName, 0, 1));
$displayRole = (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'Administrator' : 'Member';

$flash = getFlash();

// Handle featured toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_featured'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        flashMessage('error', 'Invalid CSRF token.');
    } else {
        $toggleId = (int) $_POST['toggle_featured'];
        toggleFeatured($toggleId);
        flashMessage('success', 'Featured status toggled.');
    }
    header('Location: posts.php');
    exit;
}

// Handle breaking toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_breaking'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        flashMessage('error', 'Invalid CSRF token.');
    } else {
        $toggleId = (int) $_POST['toggle_breaking'];
        toggleBreaking($toggleId);
        flashMessage('success', 'Breaking status toggled.');
    }
    header('Location: posts.php');
    exit;
}

// Handle editor's pick toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_editors_pick'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        flashMessage('error', 'Invalid CSRF token.');
    } else {
        $toggleId = (int) $_POST['toggle_editors_pick'];
        toggleEditorsPick($toggleId);
        flashMessage('success', "Editor's Pick status toggled.");
    }
    header('Location: posts.php');
    exit;
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        flashMessage('error', 'Invalid CSRF token.');
        header('Location: posts.php');
        exit;
    }
    $deleteId = (int) $_POST['delete_id'];
    if (deletePost($deleteId)) {
        flashMessage('success', 'Post deleted successfully.');
    } else {
        flashMessage('error', 'Failed to delete post.');
    }
    header('Location: posts.php');
    exit;
}

$type = $_GET['type'] ?? 'all';
$status = $_GET['status'] ?? 'all';
$search = trim($_GET['search'] ?? '');

$posts = getPostsPaginated(1, 999999, $type, $status, $search);
$total = count($posts);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova News - Posts</title>
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
                <i class="fa-solid fa-house mr-4"></i>
                Dashboard
            </a>
            <a href="posts.php" class="flex items-center px-6 py-4 bg-blue-600">
                <i class="fa-solid fa-newspaper mr-4"></i>
                Posts
            </a>
            <a href="categories.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-folder mr-4"></i>
                Categories
            </a>
            <a href="users.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-users mr-4"></i>
                Users
            </a>
            <a href="plans.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-gem mr-4"></i>
                Subscription Plans
            </a>

            <a href="user-subscriptions.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-file-contract mr-4"></i> User Subscriptions
            </a>
            <a href="payments.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-credit-card mr-4"></i>
                Payments
            </a>
            <a href="payment-services.php" class="flex items-center px-6 py-4 hover:bg-slate-800"><i class="fa-solid fa-money-bill-transfer mr-4"></i> Payment Services</a>
        </nav>
        <a href="/Nova_News/public/logout.php" class="flex items-center px-6 py-4 hover:bg-red-600">
            <i class="fa-solid fa-right-from-bracket mr-4"></i>
            Logout
        </a>

    </aside>

<!-- Main -->
    <div class="ml-72 flex flex-col h-screen">

        <header class="bg-white shadow h-16 flex justify-between items-center px-8 shrink-0">
            <h2 class="text-3xl font-bold">Posts</h2>
            <div class="flex items-center gap-6">
                <button class="relative">
                    <i class="fa-regular fa-bell text-xl"></i>
                    <?php if ($totalNotifs > 0): ?>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center"><?= $totalNotifs > 9 ? '9+' : $totalNotifs ?></span>
                    <?php endif; ?>
                </button>
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
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by title..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Type</label>
                        <select name="type" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="all" <?= $type === 'all' ? 'selected' : '' ?>>All Types</option>
                            <option value="free" <?= $type === 'free' ? 'selected' : '' ?>>Free</option>
                            <option value="premium" <?= $type === 'premium' ? 'selected' : '' ?>>Premium</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                        <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All Statuses</option>
                            <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option>
                            <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
                            <i class="fa-solid fa-filter mr-1"></i> Filter
                        </button>
                        <a href="posts.php" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50 transition">
                            <i class="fa-solid fa-undo mr-1"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow w-full overflow-x-auto">

                <div class="border-b p-5 flex justify-between items-center">
                    <h3 class="text-xl font-bold">All Posts <span class="text-sm font-normal text-gray-500">(<?= number_format($total) ?> total)</span></h3>
                    <a href="post-create.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
                        <i class="fa-solid fa-plus mr-1"></i> Add New Post
                    </a>
                </div>
            
                <table class="w-full">
                
                    <thead calss="w-full">
                        <tr class="border-b bg-gray-50 text-left text-sm font-semibold text-gray-600">
                             <th class="p-5">ID</th>
                            <th class="p-5">Title</th>
                            <th class="p-5">Category</th>
                            <th class="p-5">Author</th>
                            <th class="p-5">Type</th>
                            <th class="p-5">Featured</th>
                            <th class="p-5">Breaking</th>
                            <th class="p-5">Editor's Pick</th>
                            <th class="p-5">Status</th>
                            <th class="p-5">Created</th>
                            <th class="p-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="min-w-full">
                        <?php if (empty($posts)): ?>
                            <tr>
                                <td colspan="11" class="p-10 text-center text-gray-500">
                                    <i class="fa-solid fa-file-lines text-4xl mb-3 block"></i>
                                    No posts found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($posts as $post): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="p-5 text-gray-500"><?= (int) $post['id'] ?></td>
                                    <td class="p-5 font-medium max-w-xs truncate"><?= htmlspecialchars($post['title']) ?></td>
                                    <td class="p-5 text-gray-600"><?= htmlspecialchars($post['category_name'] ?? '-') ?></td>
                                    <td class="p-5 text-gray-600"><?= htmlspecialchars($post['author_name'] ?? '-') ?></td>
                                    <td class="p-5">
                                        <?php if (($post['post_type'] ?? 'free') === 'premium'): ?>
                                            <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-medium">Premium</span>
                                        <?php else: ?>
                                            <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm font-medium">Free</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-5">
                                        <?php if (($post['is_featured'] ?? 0)): ?>
                                            <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-sm font-medium">Featured</span>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-sm">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-5">
                                        <?php if (($post['is_breaking'] ?? 0)): ?>
                                            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm font-medium">Breaking</span>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-sm">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-5">
                                        <?php if (($post['is_editors_pick'] ?? 0)): ?>
                                            <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-sm font-medium">Editor's Pick</span>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-sm">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-5">
                                        <?php if (($post['status'] ?? 'draft') === 'published'): ?>
                                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-medium">Published</span>
                                        <?php else: ?>
                                            <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-sm font-medium">Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-5 text-gray-500 text-sm"><?= htmlspecialchars(date('M j, Y', strtotime($post['created_at']))) ?></td>
                                    <td class="p-5 text-right whitespace-nowrap">
                                        <form method="post" action="" class="inline">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="toggle_featured" value="<?= (int) $post['id'] ?>">
                                            <button type="submit" class="text-amber-600 hover:text-amber-800 mr-3" title="Toggle Featured">
                                                <i class="fa-solid <?= ($post['is_featured'] ?? 0) ? 'fa-star' : 'fa-star-half-alt' ?>"></i>
                                            </button>
                                        </form>
                                        <form method="post" action="" class="inline">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="toggle_breaking" value="<?= (int) $post['id'] ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-800 mr-3" title="Toggle Breaking">
                                                <i class="fa-solid <?= ($post['is_breaking'] ?? 0) ? 'fa-bolt' : 'fa-bolt-lightning' ?>"></i>
                                            </button>
                                        </form>
                                        <form method="post" action="" class="inline">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="toggle_editors_pick" value="<?= (int) $post['id'] ?>">
                                            <button type="submit" class="text-purple-600 hover:text-purple-800 mr-3" title="Toggle Editor's Pick">
                                                <i class="fa-solid <?= ($post['is_editors_pick'] ?? 0) ? 'fa-award' : 'fa-certificate' ?>"></i>
                                            </button>
                                        </form>
                                        <a href="post-edit.php?id=<?= (int) $post['id'] ?>" class="text-blue-600 hover:text-blue-800 mr-3" title="Edit">
                                            <i class="fa-solid fa-edit"></i>
                                        </a>
                                        <form method="post" action="" class="inline" onsubmit="return confirm('Delete post &quot;<?= htmlspecialchars($post['title'], ENT_QUOTES) ?>&quot;?');">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="delete_id" value="<?= (int) $post['id'] ?>">
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

        </div>

    </div>

</body>
</html>
