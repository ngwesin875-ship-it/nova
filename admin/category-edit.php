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

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$category = getCategoryById($id);

if (!$category) {
    flashMessage('error', 'Category not found.');
    header('Location: categories.php');
    exit;
}

$name = $category['name'];
$slug = $category['slug'];
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errorMessage = 'Invalid CSRF token.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');

        if ($name === '') {
            $errorMessage = 'Category name is required.';
        } elseif ($slug === '') {
            $errorMessage = 'Slug is required.';
        } else {
            if (updateCategory($id, $name, $slug)) {
                flashMessage('success', 'Category "' . htmlspecialchars($name) . '" updated successfully.');
                header('Location: categories.php');
                exit;
            } else {
                $errorMessage = 'Failed to update category. The name or slug may already exist.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova News - Edit Category</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-slate-100">

<?php include __DIR__ . '/../includes/admin-sidebar.php'; ?>

<!-- Main -->
    <div class="ml-64 flex flex-col h-screen">

        <?php $pageTitle = 'Edit Category'; include __DIR__ . '/../includes/admin-topbar.php'; ?>

        <div class="flex-1 overflow-y-auto p-6 space-y-6">

            <?php if ($errorMessage): ?>
                <div class="mb-6 px-5 py-4 rounded-xl shadow-sm text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                    <i class="fa-solid fa-exclamation-circle mr-2"></i>
                    <?= htmlspecialchars($errorMessage) ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 max-w-2xl">

                <div class="border-b p-5 flex items-center gap-3">
                    <i class="fa-solid fa-folder text-blue-600 text-xl"></i>
                    <h3 class="text-xl font-bold"><?= htmlspecialchars($category['name']) ?></h3>
                </div>

                <form method="post" action="" class="px-3 py-2 text-xs space-y-5">
                    <?= csrfField() ?>

                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-900 mb-1">Name <span class="text-red-600">*</span></label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required
                               oninput="autoSlug(this.value)"
                               class="w-full px-3 py-1.5 text-xs.5 border border-slate-200 rounded-xl border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                    </div>

                    <div>
                        <label for="slug" class="block text-xs font-semibold text-slate-900 mb-1">Slug <span class="text-red-600">*</span></label>
                        <input type="text" id="slug" name="slug" value="<?= htmlspecialchars($slug) ?>" required
                               class="w-full px-3 py-1.5 text-xs.5 border border-slate-200 rounded-xl border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none font-mono text-sm">
                        <p class="text-xs text-slate-500 mt-1">Auto-generated from name, but can be edited manually.</p>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                            <i class="fa-solid fa-save mr-1"></i> Update Category
                        </button>
                        <a href="categories.php" class="px-6 py-2.5 border border-slate-200 text-slate-900 rounded-lg font-semibold hover:bg-slate-50/50 transition-colors transition">
                            Cancel
                        </a>
                    </div>
                </form>

            </div>

        </div>

    </div>

<script>
function autoSlug(value) {
    const slugField = document.getElementById('slug');
    if (slugField.dataset.manual === 'true') return;
    slugField.value = value
        .toLowerCase()
        .trim()
        .replace(/[ä]/g, 'ae').replace(/[ö]/g, 'oe').replace(/[ü]/g, 'ue').replace(/[ß]/g, 'ss')
        .replace(/[^a-z0-9-]/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
}

document.getElementById('slug').addEventListener('input', function () {
    this.dataset.manual = this.value !== '' ? 'true' : 'false';
});
</script>

</body>
</html>
