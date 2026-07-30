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
$errors = [];
$old = ['post_id' => '', 'user_id' => '', 'type' => 'like'];

$posts = getPostsForDropdown();
$users = getUsersForDropdown();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        flashMessage('error', 'Invalid CSRF token.');
        header('Location: like-create.php');
        exit;
    }

    $old['post_id'] = (int) ($_POST['post_id'] ?? 0);
    $old['user_id'] = (int) ($_POST['user_id'] ?? 0);
    $old['type'] = $_POST['type'] ?? 'like';

    if (empty($old['post_id'])) $errors[] = 'Post is required.';
    if (empty($old['user_id'])) $errors[] = 'User is required.';
    if (!in_array($old['type'], ['like', 'dislike'])) $errors[] = 'Type must be like or dislike.';

    if (empty($errors)) {
        $result = createLikeDislike($old['post_id'], $old['user_id'], $old['type']);
        if ($result) {
            flashMessage('success', 'Vote created successfully.');
            header('Location: likes.php');
            exit;
        } else {
            $errors[] = 'Failed to create vote.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova News - Create Vote</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-gray-100">

    <?php include __DIR__ . '/../includes/admin-sidebar.php'; ?>

    <div class="ml-72 flex flex-col h-screen">
        <?php $pageTitle = 'Create Vote'; include __DIR__ . '/../includes/admin-topbar.php'; ?>

        <div class="flex-1 overflow-y-auto p-8 space-y-8">

            <?php if ($flash): ?>
                <div class="mb-6 px-5 py-4 rounded-xl shadow-sm text-sm font-medium <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' ?>">
                    <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-2"></i>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="mb-6 px-5 py-4 rounded-xl shadow-sm text-sm font-medium bg-red-100 text-red-800 border border-red-200">
                    <i class="fa-solid fa-exclamation-circle mr-2"></i>
                    <ul class="list-disc list-inside">
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6">
                <form method="post" action="">
                    <?= csrfField() ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Post</label>
                            <select name="post_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Post</option>
                                <?php foreach ($posts as $post): ?>
                                    <option value="<?= (int) $post['id'] ?>" <?= $old['post_id'] == $post['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($post['title']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">User</label>
                            <select name="user_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select User</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= (int) $user['id'] ?>" <?= $old['user_id'] == $user['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($user['username']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                            <select name="type" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="like" <?= $old['type'] === 'like' ? 'selected' : '' ?>>Like</option>
                                <option value="dislike" <?= $old['type'] === 'dislike' ? 'selected' : '' ?>>Dislike</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-8 flex gap-4">
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">Create Vote</button>
                        <a href="likes.php" class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg font-semibold hover:bg-gray-300 transition">Cancel</a>
                    </div>
                </form>
            </div>

        </div>
    </div>

</body>
</html>
