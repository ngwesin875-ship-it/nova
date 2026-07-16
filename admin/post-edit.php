<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/posts.php';
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
$post = getPostById($id);

if (!$post) {
    flashMessage('error', 'Post not found.');
    header('Location: posts.php');
    exit;
}

$categories = getCategories();
$errorMessage = '';

$title = $post['title'];
$slug = $post['slug'];
$content = $post['content'];
$excerpt = $post['excerpt'] ?? '';
$imageUrl = $post['image_url'] ?? '';
$postType = $post['post_type'] ?? 'free';
$categoryId = $post['category_id'];
$status = $post['status'] ?? 'draft';
$isFeatured = (int) ($post['is_featured'] ?? 0);
$isBreaking = (int) ($post['is_breaking'] ?? 0);
$isEditorsPick = (int) ($post['is_editors_pick'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errorMessage = 'Invalid CSRF token.';
    } else {
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $content = $_POST['content'] ?? '';
        $excerpt = trim($_POST['excerpt'] ?? '');
        $postType = $_POST['post_type'] ?? 'free';
        $categoryId = $_POST['category_id'] !== '' ? (int) $_POST['category_id'] : null;
        $status = $_POST['status'] ?? 'published';
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $isBreaking = isset($_POST['is_breaking']) ? 1 : 0;
        $isEditorsPick = isset($_POST['is_editors_pick']) ? 1 : 0;

        if ($title === '') {
            $errorMessage = 'Title is required.';
        } elseif ($slug === '') {
            $errorMessage = 'Slug is required.';
        } elseif ($content === '') {
            $errorMessage = 'Content is required.';
        } else {
            $imageUrl = $post['image_url'];
            $removeImage = isset($_POST['remove_image']);

            if ($removeImage) {
                deleteImage($imageUrl);
                $imageUrl = null;
            }

            if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploaded = uploadImage($_FILES['image_file']);
                if ($uploaded === false) {
                    $errorMessage = 'Invalid image file. Allowed: JPG, PNG, GIF, WEBP (max 5MB).';
                } else {
                    if ($imageUrl) {
                        deleteImage($imageUrl);
                    }
                    $imageUrl = $uploaded;
                }
            }

            if (!$errorMessage) {
                if (updatePost($id, $title, $slug, $content, $excerpt ?: null, $imageUrl, $postType, $categoryId, $status, $isFeatured, $isBreaking, $isEditorsPick)) {
                flashMessage('success', 'Post "' . htmlspecialchars($title) . '" updated successfully.');
                header('Location: posts.php');
                exit;
            } else {
                $errorMessage = 'Failed to update post. The slug may already exist.';
            }
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
    <title>Nova News - Edit Post</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-gray-100">

<!-- Sidebar -->
    <aside class="fixed left-0 top-0 h-screen overflow-y-auto z-50 w-72 bg-slate-900 text-white">

        <div class="h-16 flex items-center px-6 border-b border-slate-700">
            <i class="fa-solid fa-newspaper text-2xl mr-3 text-blue-500"></i>
            <h1 class="text-2xl font-bold">NOVA NEWS</h1>
        </div>

        <div class="p-6 border-b border-slate-700">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-blue-600 flex items-center justify-center text-2xl font-bold text-white">
                    <?= htmlspecialchars($displayInitial) ?>
                </div>
                <div>
                    <h3 class="font-semibold"><?= htmlspecialchars($displayName) ?></h3>
                    <p class="text-sm text-green-400">● <?= htmlspecialchars($displayRole) ?></p>
                </div>
            </div>
        </div>

        <nav class="mt-6">
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
            <a href="payments.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-credit-card mr-4"></i>
                Payments
            </a>

            <a href="#" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-gear mr-4"></i>
                Settings
            </a>
            <a href="/Nova_News/public/signin.php" class="flex items-center px-6 py-4 hover:bg-red-600">
                <i class="fa-solid fa-right-from-bracket mr-4"></i>
                Logout
            </a>
        </nav>

    </aside>

<!-- Main -->
    <div class="ml-72 flex flex-col h-screen">

        <header class="bg-white shadow h-16 flex justify-between items-center px-8 shrink-0">
            <h2 class="text-3xl font-bold">Edit Post</h2>
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

        <div class="flex-1 overflow-y-auto p-8 space-y-8">

            <?php if ($errorMessage): ?>
                <div class="mb-6 px-5 py-4 rounded-xl shadow-sm text-sm font-medium bg-red-100 text-red-800 border border-red-200">
                    <i class="fa-solid fa-exclamation-circle mr-2"></i>
                    <?= htmlspecialchars($errorMessage) ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow max-w-4xl">

                <div class="border-b p-5 flex items-center gap-3">
                    <i class="fa-solid fa-file-lines text-blue-600 text-xl"></i>
                    <h3 class="text-xl font-bold"><?= htmlspecialchars($post['title']) ?></h3>
                </div>

                <form method="post" action="" enctype="multipart/form-data" class="p-5 space-y-5">
                    <?= csrfField() ?>

                    <div class="grid md:grid-cols-2 gap-5">
                        <div>
                            <label for="title" class="block text-sm font-semibold text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                            <input type="text" id="title" name="title" value="<?= htmlspecialchars($title) ?>" required
                                   oninput="autoSlug(this.value)"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label for="slug" class="block text-sm font-semibold text-gray-700 mb-1">Slug <span class="text-red-500">*</span></label>
                            <input type="text" id="slug" name="slug" value="<?= htmlspecialchars($slug) ?>" required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono text-sm">
                            <p class="text-xs text-gray-500 mt-1">Auto-generated from title.</p>
                        </div>
                    </div>

                    <div>
                        <label for="content" class="block text-sm font-semibold text-gray-700 mb-1">Content <span class="text-red-500">*</span></label>
                        <textarea id="content" name="content" rows="16" required
                                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono text-sm leading-relaxed"><?= htmlspecialchars($content) ?></textarea>
                    </div>

                    <div class="grid md:grid-cols-2 gap-5">
                        <div>
                            <label for="excerpt" class="block text-sm font-semibold text-gray-700 mb-1">Excerpt</label>
                            <textarea id="excerpt" name="excerpt" rows="3" maxlength="500"
                                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none text-sm"><?= htmlspecialchars($excerpt) ?></textarea>
                            <p class="text-xs text-gray-500 mt-1">Short summary (max 500 chars).</p>
                        </div>
                        <div>
                            <label for="image_file" class="block text-sm font-semibold text-gray-700 mb-1">Featured Image</label>
                            <?php if ($imageUrl): ?>
                                <div class="mb-3 flex items-center gap-3">
                                    <img src="/Nova_News/<?= htmlspecialchars($imageUrl) ?>" alt="Preview" class="w-24 h-16 object-cover rounded border">
                                    <label class="flex items-center gap-1.5 text-sm text-red-600 cursor-pointer">
                                        <input type="checkbox" name="remove_image" value="1" onchange="this.closest('label').nextElementSibling.classList.toggle('hidden')">
                                        Remove current image
                                    </label>
                                </div>
                            <?php endif; ?>
                            <input type="file" id="image_file" name="image_file" accept="image/jpeg,image/png,image/gif,image/webp"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 file:font-semibold hover:file:bg-blue-100">
                            <p class="text-xs text-gray-500 mt-1"><?= $imageUrl ? 'Leave empty to keep current image.' : 'Allowed: JPG, PNG, GIF, WEBP (max 5MB).' ?></p>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-5 gap-5">
                        <div>
                            <label for="post_type" class="block text-sm font-semibold text-gray-700 mb-1">Post Type</label>
                            <select id="post_type" name="post_type" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                <option value="free" <?= $postType === 'free' ? 'selected' : '' ?>>Free</option>
                                <option value="premium" <?= $postType === 'premium' ? 'selected' : '' ?>>Premium</option>
                            </select>
                        </div>
                        <div>
                            <label for="category_id" class="block text-sm font-semibold text-gray-700 mb-1">Category</label>
                            <select id="category_id" name="category_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                <option value="">— No Category —</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= (int) $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                            <select id="status" name="status" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option>
                                <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                            </select>
                        </div>
                        <div class="flex items-end pb-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_featured" value="1" <?= $isFeatured ? 'checked' : '' ?> class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span class="text-sm font-semibold text-gray-700">Featured</span>
                            </label>
                        </div>
                        <div class="flex items-end pb-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_breaking" value="1" <?= $isBreaking ? 'checked' : '' ?> class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                <span class="text-sm font-semibold text-gray-700">Breaking</span>
                            </label>
                        </div>
                        <div class="flex items-end pb-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_editors_pick" value="1" <?= $isEditorsPick ? 'checked' : '' ?> class="w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500">
                                <span class="text-sm font-semibold text-gray-700">Editor's Pick</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <a href="posts.php" class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition">
                            <i class="fa-solid fa-arrow-left mr-1"></i> Back to Posts
                        </a>
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                            <i class="fa-solid fa-save mr-1"></i> Update Post
                        </button>
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
