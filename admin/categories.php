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
<body class="bg-slate-100">

<?php include __DIR__ . '/../includes/admin-sidebar.php'; ?>

<!-- Main -->
    <div class="ml-64 flex flex-col h-screen">

        <?php $pageTitle = 'Categories'; include __DIR__ . '/../includes/admin-topbar.php'; ?>

        <div class="flex-1 overflow-y-auto p-6 space-y-6">

            <?php if ($flash): ?>
                <div class="mb-6 px-5 py-4 rounded-xl shadow-sm text-xs font-medium <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' ?>">
                    <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-2"></i>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60">

                <div class="border-b p-5 flex justify-between items-center">
                    <h3 class="text-xl font-bold">All Categories</h3>
                    <a href="category-create.php" class="px-3 py-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl text-xs shadow-sm transition-all text-sm font-semibold hover:bg-blue-700 transition">
                        <i class="fa-solid fa-plus mr-1"></i> Add New Category
                    </a>
                </div>

                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-slate-50/50 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            <th class="px-3 py-2 text-xs">No</th>
                            <th class="px-3 py-2 text-xs">Name</th>
                            <th class="px-3 py-2 text-xs">Slug</th>
                            <th class="px-3 py-2 text-xs">Created At</th>
                            <th class="px-3 py-2 text-xs text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="5" class="p-10 text-center text-slate-500">
                                    <i class="fa-solid fa-folder-open text-4xl mb-3 block"></i>
                                    No categories found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $seq = isset($page, $limit) ? ($page - 1) * $limit + 1 : 1; ?>
                            <?php foreach ($categories as $cat): ?>
                                <tr class="border-b hover:bg-slate-50/50 transition-colors">
                                    <td class="px-3 py-2 text-xs text-slate-500"><?= $seq++ ?></td>
                                    <td class="px-3 py-2 text-xs font-medium"><?= htmlspecialchars($cat['name']) ?></td>
                                    <td class="px-3 py-2 text-xs text-slate-500"><?= htmlspecialchars($cat['slug']) ?></td>
                                    <td class="px-3 py-2 text-xs text-slate-500"><?= htmlspecialchars($cat['created_at']) ?></td>
                                    <td class="px-3 py-2 text-xs text-right">
                                        <a href="category-edit.php?id=<?= (int) $cat['id'] ?>" class="text-blue-500 hover:text-blue-600 hover:bg-blue-50 p-1.5 rounded-lg transition-colors mr-4" title="Edit">
                                            <i class="fa-solid fa-edit"></i> Edit
                                        </a>
                                        <form method="post" action="" class="inline" onsubmit="return confirm('Delete category &quot;<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>&quot;?');">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="delete_id" value="<?= (int) $cat['id'] ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-600 hover:bg-red-50 p-1.5 rounded-lg transition-colors" title="Delete">
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
