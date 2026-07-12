<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/categories.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/notifications.php';

requireAdmin();

$notifCounts = getNotificationCounts();
$totalNotifs = array_sum($notifCounts);

$displayName = trim($_SESSION['username'] ?? 'Admin');
$displayInitial = strtoupper(substr($displayName, 0, 1));
$displayRole = (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'Administrator' : 'Member';

$flash = getFlash();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        flashMessage('error', 'Invalid CSRF token.');
        header('Location: categories.php');
        exit;
    }
    $deleteId = (int) $_POST['delete_id'];
    if (deleteCategory($deleteId)) {
        flashMessage('success', 'Category deleted successfully.');
    } else {
        flashMessage('error', 'Failed to delete category.');
    }
    header('Location: categories.php');
    exit;
}

$categories = getCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova News - Categories</title>
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
            <a href="posts.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-newspaper mr-4"></i>
                Posts
            </a>
            <a href="categories.php" class="flex items-center px-6 py-4 bg-blue-600">
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
                <i class="fa-solid fa-file-contract mr-4"></i> User Subscriptions</a>

            <a href="payments.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-credit-card mr-4"></i>
                Payments
            </a>

            <a href="payment-services.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-money-bill-transfer mr-4"></i> Payment Services</a>
        </nav>

        <a href="/Nova_News/public/signin.php" class="flex items-center px-6 py-4 hover:bg-red-600">
            <i class="fa-solid fa-right-from-bracket mr-4"></i>
            Logout
        </a>

    </aside>

<!-- Main -->
    <div class="ml-72 flex flex-col h-screen">

        <header class="bg-white shadow h-16 flex justify-between items-center px-8 shrink-0">
            <h2 class="text-3xl font-bold">Categories</h2>
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

        <div class="flex-1 overflow-y-auto p-8 space-y-8">

            <?php if ($flash): ?>
                <div class="mb-6 px-5 py-4 rounded-xl shadow-sm text-sm font-medium <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' ?>">
                    <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-2"></i>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow">

                <div class="border-b p-5 flex justify-between items-center">
                    <h3 class="text-xl font-bold">All Categories</h3>
                    <a href="category-create.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
                        <i class="fa-solid fa-plus mr-1"></i> Add New Category
                    </a>
                </div>

                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-gray-50 text-left text-sm font-semibold text-gray-600">
                            <th class="p-5">ID</th>
                            <th class="p-5">Name</th>
                            <th class="p-5">Slug</th>
                            <th class="p-5">Created At</th>
                            <th class="p-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="5" class="p-10 text-center text-gray-500">
                                    <i class="fa-solid fa-folder-open text-4xl mb-3 block"></i>
                                    No categories found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categories as $cat): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="p-5 text-gray-500"><?= (int) $cat['id'] ?></td>
                                    <td class="p-5 font-medium"><?= htmlspecialchars($cat['name']) ?></td>
                                    <td class="p-5 text-gray-600"><?= htmlspecialchars($cat['slug']) ?></td>
                                    <td class="p-5 text-gray-500"><?= htmlspecialchars($cat['created_at']) ?></td>
                                    <td class="p-5 text-right">
                                        <a href="category-edit.php?id=<?= (int) $cat['id'] ?>" class="text-blue-600 hover:text-blue-800 mr-4" title="Edit">
                                            <i class="fa-solid fa-edit"></i> Edit
                                        </a>
                                        <form method="post" action="" class="inline" onsubmit="return confirm('Delete category &quot;<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>&quot;?');">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="delete_id" value="<?= (int) $cat['id'] ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                                <i class="fa-solid fa-trash"></i> Delete
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
