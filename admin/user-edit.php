<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/notifications.php';

requireAdmin();

$notifCounts = getNotificationCounts();
$totalNotifs = array_sum($notifCounts);

$displayName = trim($_SESSION['username'] ?? 'Admin');
$displayInitial = strtoupper(substr($displayName, 0, 1));
$displayRole = (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'Administrator' : 'Member';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$user = getUserById($id);

if (!$user) {
    flashMessage('error', 'User not found.');
    header('Location: users.php');
    exit;
}

$selfId = (int) ($_SESSION['user_id'] ?? 0);
$errorMessage = '';

$username = $user['username'];
$email = $user['email'];
$role = $user['role'];
$avatar = $user['avatar'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errorMessage = 'Invalid CSRF token.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';

        if ($username === '') {
            $errorMessage = 'Username is required.';
        } elseif ($email === '') {
            $errorMessage = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = 'Invalid email format.';
        } elseif ($password !== '' && strlen($password) < 6) {
            $errorMessage = 'Password must be at least 6 characters.';
        } else {
            $newAvatar = $avatar;
            $removeAvatar = isset($_POST['remove_avatar']);

            if ($removeAvatar) {
                deleteAvatar($newAvatar);
                $newAvatar = null;
            }

            if (isset($_FILES['avatar_file']) && $_FILES['avatar_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploaded = uploadAvatar($_FILES['avatar_file']);
                if ($uploaded === false) {
                    $errorMessage = 'Invalid avatar file. Allowed: JPG, PNG, GIF, WEBP (max 2MB).';
                } else {
                    if ($newAvatar) {
                        deleteAvatar($newAvatar);
                    }
                    $newAvatar = $uploaded;
                }
            }

            if (!$errorMessage) {
                $pwd = $password !== '' ? $password : null;
                if (updateUser($id, $username, $email, $pwd, $role, $newAvatar)) {
                    flashMessage('success', 'User "' . htmlspecialchars($username) . '" updated successfully.');
                    header('Location: users.php');
                    exit;
                } else {
                    $errorMessage = 'Failed to update user. The email may already exist.';
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
    <title>Nova News - Edit User</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-gray-100">

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
            <a href="index.php" class="flex items-center px-6 py-4 hover:bg-slate-800"><i class="fa-solid fa-house mr-4"></i> Dashboard</a>
            <a href="posts.php" class="flex items-center px-6 py-4 hover:bg-slate-800"><i class="fa-solid fa-newspaper mr-4"></i> Posts</a>
            <a href="categories.php" class="flex items-center px-6 py-4 hover:bg-slate-800"><i class="fa-solid fa-folder mr-4"></i> Categories</a>
            <a href="users.php" class="flex items-center px-6 py-4 bg-blue-600"><i class="fa-solid fa-users mr-4"></i> Users</a>
            <a href="#" class="flex items-center px-6 py-4 hover:bg-slate-800"><i class="fa-solid fa-gem mr-4"></i> Subscription Plans</a>
            <a href="payments.php" class="flex items-center px-6 py-4 hover:bg-slate-800"><i class="fa-solid fa-credit-card mr-4"></i> Payments</a>

            <a href="#" class="flex items-center px-6 py-4 hover:bg-slate-800"><i class="fa-solid fa-gear mr-4"></i> Settings</a>
            <a href="/Nova_News/public/signin.php" class="flex items-center px-6 py-4 hover:bg-red-600"><i class="fa-solid fa-right-from-bracket mr-4"></i> Logout</a>
        </nav>

    </aside>

    <div class="ml-72 flex flex-col h-screen">

        <header class="bg-white shadow h-16 flex justify-between items-center px-8 shrink-0">
            <h2 class="text-3xl font-bold">Edit User</h2>
            <div class="flex items-center gap-6">
                <?php include __DIR__ . '/../includes/admin-header.php'; ?>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-sm font-bold text-white"><?= htmlspecialchars($displayInitial) ?></div>
                    <span class="font-semibold"><?= htmlspecialchars($displayName) ?></span>
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8 space-y-8">

            <?php if ($errorMessage): ?>
                <div class="mb-6 px-5 py-4 rounded-xl shadow-sm text-sm font-medium bg-red-100 text-red-800 border border-red-200">
                    <i class="fa-solid fa-exclamation-circle mr-2"></i> <?= htmlspecialchars($errorMessage) ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow max-w-2xl">

                <div class="border-b p-5 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-sm font-bold text-white">
                        <?= strtoupper(substr($user['username'], 0, 1)) ?>
                    </div>
                    <h3 class="text-xl font-bold"><?= htmlspecialchars($user['username']) ?></h3>
                </div>

                <form method="post" action="" enctype="multipart/form-data" class="p-5 space-y-5">
                    <?= csrfField() ?>

                    <div class="grid md:grid-cols-2 gap-5">
                        <div>
                            <label for="username" class="block text-sm font-semibold text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
                            <input type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-5">
                        <div>
                            <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                            <input type="password" id="password" name="password" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none" placeholder="Leave blank to keep current">
                            <p class="text-xs text-gray-500 mt-1">Min 6 characters. Leave empty to keep current.</p>
                        </div>
                        <div>
                            <label for="role" class="block text-sm font-semibold text-gray-700 mb-1">Role</label>
                            <select id="role" name="role" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                                <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>User</option>
                                <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Avatar</label>
                        <?php if ($avatar): ?>
                            <div class="mb-3 flex items-center gap-3">
                                <img src="/Nova_News/<?= htmlspecialchars($avatar) ?>" alt="Avatar" class="w-12 h-12 rounded-full object-cover">
                                <label class="flex items-center gap-1.5 text-sm text-red-600 cursor-pointer">
                                    <input type="checkbox" name="remove_avatar" value="1"> Remove current avatar
                                </label>
                            </div>
                        <?php endif; ?>
                        <input type="file" id="avatar_file" name="avatar_file" accept="image/jpeg,image/png,image/gif,image/webp" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-700 file:font-semibold hover:file:bg-blue-100">
                        <p class="text-xs text-gray-500 mt-1"><?= $avatar ? 'Leave empty to keep current.' : 'Allowed: JPG, PNG, GIF, WEBP (max 2MB).' ?></p>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <a href="users.php" class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition"><i class="fa-solid fa-arrow-left mr-1"></i> Back to Users</a>
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                            <i class="fa-solid fa-save mr-1"></i> Update User
                        </button>
                    </div>
                </form>

            </div>

        </div>

    </div>

</body>
</html>
