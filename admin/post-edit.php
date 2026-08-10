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
<body class="bg-slate-100">

<?php include __DIR__ . '/../includes/admin-sidebar.php'; ?>

<!-- Main -->
    <div class="ml-64 flex flex-col h-screen">

        <?php $pageTitle = 'Edit Post'; include __DIR__ . '/../includes/admin-topbar.php'; ?>

        <div class="flex-1 overflow-y-auto p-6 space-y-6">

            <?php if ($errorMessage): ?>
                <div class="mb-6 px-5 py-4 rounded-xl shadow-sm text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                    <i class="fa-solid fa-exclamation-circle mr-2"></i>
                    <?= htmlspecialchars($errorMessage) ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 max-w-4xl">

                <div class="border-b p-5 flex items-center gap-3">
                    <i class="fa-solid fa-file-lines text-blue-600 text-xl"></i>
                    <h3 class="text-xl font-bold"><?= htmlspecialchars($post['title']) ?></h3>
                </div>

                <form method="post" action="" enctype="multipart/form-data" class="px-3 py-2 text-xs space-y-5">
                    <?= csrfField() ?>

                    <div class="grid md:grid-cols-2 gap-5">
                        <div>
                            <label for="title" class="block text-xs font-semibold text-slate-900 mb-1">Title <span class="text-red-600">*</span></label>
                            <input type="text" id="title" name="title" value="<?= htmlspecialchars($title) ?>" required
                                   oninput="autoSlug(this.value)"
                                   class="w-full px-3 py-1.5 text-xs.5 border border-slate-200 rounded-xl border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                        </div>
                        <div>
                            <label for="slug" class="block text-xs font-semibold text-slate-900 mb-1">Slug <span class="text-red-600">*</span></label>
                            <input type="text" id="slug" name="slug" value="<?= htmlspecialchars($slug) ?>" required
                                   class="w-full px-3 py-1.5 text-xs.5 border border-slate-200 rounded-xl border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none font-mono text-sm">
                            <p class="text-xs text-slate-500 mt-1">Auto-generated from title.</p>
                        </div>
                    </div>

                    <div>
                        <label for="content" class="block text-xs font-semibold text-slate-900 mb-1">Content <span class="text-red-600">*</span></label>
                        <textarea id="content" name="content" rows="16" required
                                  class="w-full px-3 py-1.5 text-xs.5 border border-slate-200 rounded-xl border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none font-mono text-sm leading-relaxed"><?= htmlspecialchars($content) ?></textarea>
                    </div>

                    <div class="grid md:grid-cols-2 gap-5">
                        <div>
                            <label for="excerpt" class="block text-xs font-semibold text-slate-900 mb-1">Excerpt</label>
                            <textarea id="excerpt" name="excerpt" rows="3" maxlength="500"
                                      class="w-full px-3 py-1.5 text-xs.5 border border-slate-200 rounded-xl border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none text-sm"><?= htmlspecialchars($excerpt) ?></textarea>
                            <p class="text-xs text-slate-500 mt-1">Short summary (max 500 chars).</p>
                        </div>
                        <div>
                            <label for="image_file" class="block text-xs font-semibold text-slate-900 mb-1">Featured Image</label>
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
                                   class="w-full px-3 py-1.5 text-xs.5 border border-slate-200 rounded-xl border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-600 file:font-semibold hover:file:bg-blue-100">
                            <p class="text-xs text-slate-500 mt-1"><?= $imageUrl ? 'Leave empty to keep current image.' : 'Allowed: JPG, PNG, GIF, WEBP (max 5MB).' ?></p>
                            <div id="image-preview-container" class="mt-3 hidden">
                                <img id="image-preview" src="" alt="Preview" class="w-full max-w-xs h-40 object-cover rounded-lg border border-slate-200">
                            </div>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-5 gap-5">
                        <div>
                            <label for="post_type" class="block text-xs font-semibold text-slate-900 mb-1">Post Type</label>
                            <select id="post_type" name="post_type" class="w-full px-3 py-1.5 text-xs.5 border border-slate-200 rounded-xl border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                                <option value="free" <?= $postType === 'free' ? 'selected' : '' ?>>Free</option>
                                <option value="premium" <?= $postType === 'premium' ? 'selected' : '' ?>>Premium</option>
                            </select>
                        </div>
                        <div>
                            <label for="category_id" class="block text-xs font-semibold text-slate-900 mb-1">Category</label>
                            <select id="category_id" name="category_id" class="w-full px-3 py-1.5 text-xs.5 border border-slate-200 rounded-xl border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                                <option value="">— No Category —</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= (int) $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="status" class="block text-xs font-semibold text-slate-900 mb-1">Status</label>
                            <select id="status" name="status" class="w-full px-3 py-1.5 text-xs.5 border border-slate-200 rounded-xl border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                                <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Published</option>
                                <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Draft</option>
                            </select>
                        </div>
                        <div class="flex items-end pb-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_featured" value="1" <?= $isFeatured ? 'checked' : '' ?> class="w-4 h-4 text-blue-600 border-slate-200 rounded focus:ring-blue-500">
                                <span class="text-xs font-semibold text-slate-900">Featured</span>
                            </label>
                        </div>
                        <div class="flex items-end pb-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_breaking" value="1" <?= $isBreaking ? 'checked' : '' ?> class="w-4 h-4 text-red-600 border-slate-200 rounded focus:ring-red-500">
                                <span class="text-xs font-semibold text-slate-900">Breaking</span>
                            </label>
                        </div>
                        <div class="flex items-end pb-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_editors_pick" value="1" <?= $isEditorsPick ? 'checked' : '' ?> class="w-4 h-4 text-purple-600 border-slate-200 rounded focus:ring-purple-500">
                                <span class="text-xs font-semibold text-slate-900">Editor's Pick</span>
                            </label>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <a href="posts.php" class="px-6 py-2.5 border border-slate-200 text-slate-900 rounded-lg font-semibold hover:bg-slate-50/50 transition-colors transition">
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

document.getElementById('image_file').addEventListener('change', function () {
    var file = this.files[0];
    var container = document.getElementById('image-preview-container');
    var preview = document.getElementById('image-preview');
    if (file && file.type.startsWith('image/')) {
        var reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            container.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    } else {
        preview.src = '';
        container.classList.add('hidden');
    }
});
</script>

</body>
</html>
