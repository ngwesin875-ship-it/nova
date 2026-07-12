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

$flash = getFlash();

$selfId = (int) ($_SESSION['user_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        flashMessage('error', 'Invalid CSRF token.');
        header('Location: users.php');
        exit;
    }
    $deleteId = (int) $_POST['delete_id'];
    if ($deleteId === $selfId) {
        flashMessage('error', 'You cannot delete your own account.');
        header('Location: users.php');
        exit;
    }
    if (deleteUser($deleteId)) {
        flashMessage('success', 'User deleted successfully.');
    } else {
        flashMessage('error', 'Failed to delete user.');
    }
    header('Location: users.php');
    exit;
}

$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$limit = 10;
$search = trim($_GET['search'] ?? '');

$total = getUsersCount($search);
$totalPages = max(1, (int) ceil($total / $limit));
$page = min($page, $totalPages);
$users = getUsersPaginated($page, $limit, $search);

function qs(array $overrides = []): string
{
    $params = array_merge($_GET, $overrides);
    unset($params['page']);
    return http_build_query($params);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova News - Users</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-gray-100">

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
            <a href="categories.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-folder mr-4"></i> Categories
            </a>
            <a href="users.php" class="flex items-center px-6 py-4 bg-blue-600">
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
            <a href="payment-services.php" class="flex items-center px-6 py-4 hover:bg-slate-800"><i class="fa-solid fa-money-bill-transfer mr-4"></i> Payment Services</a>
        </nav>

         <a href="/Nova_News/public/signin.php" class="flex items-center px-6 py-4 hover:bg-red-600">
            <i class="fa-solid fa-right-from-bracket mr-4"></i> Logout
        </a>

    </aside>

    <div class="ml-72 flex flex-col h-screen">

        <header class="bg-white shadow h-16 flex justify-between items-center px-8 shrink-0">
            <h2 class="text-3xl font-bold">Users</h2>
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

            <div class="bg-white rounded-xl shadow mb-6 p-5">
                <form method="get" action="" class="flex flex-wrap items-end gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Search</label>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by username or email..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
                            <i class="fa-solid fa-filter mr-1"></i> Filter
                        </button>
                        <a href="users.php" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50 transition">
                            <i class="fa-solid fa-undo mr-1"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow">

                <div class="border-b p-5">
                    <h3 class="text-xl font-bold">All Users <span class="text-sm font-normal text-gray-500">(<?= number_format($total) ?> total)</span></h3>
                </div>

                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-gray-50 text-left text-sm font-semibold text-gray-600">
                            <th class="p-5">ID</th>
                            <th class="p-5">User</th>
                            <th class="p-5">Email</th>
                            <th class="p-5">Role</th>
                            <th class="p-5">Subscription</th>
                            <th class="p-5">Created</th>
                            <th class="p-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" class="p-10 text-center text-gray-500">
                                    <i class="fa-solid fa-users text-4xl mb-3 block"></i>
                                    No users found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="p-5 text-gray-500"><?= (int) $user['id'] ?></td>
                                    <td class="p-5">
                                        <div class="flex items-center gap-3">
                                            <?php if (!empty($user['avatar'])): ?>
                                                <img src="/Nova_News/<?= htmlspecialchars($user['avatar']) ?>" alt="" class="w-8 h-8 rounded-full object-cover">
                                            <?php else: ?>
                                                <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-xs font-bold text-white">
                                                    <?= strtoupper(substr($user['username'], 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                            <span class="font-medium"><?= htmlspecialchars($user['username']) ?></span>
                                        </div>
                                    </td>
                                    <td class="p-5 text-gray-600"><?= htmlspecialchars($user['email']) ?></td>
                                    <td class="p-5">
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-sm font-medium">Admin</span>
                                        <?php else: ?>
                                            <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm font-medium">User</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-5">
                                        <?php
                                        $subStatus = $user['sub_status'] ?? 'inactive';
                                        if ($subStatus === 'active'):
                                        ?>
                                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-medium">Active</span>
                                        <?php else: ?>
                                            <span class="bg-gray-100 text-gray-500 px-3 py-1 rounded-full text-sm font-medium">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-5 text-gray-500 text-sm"><?= htmlspecialchars(date('M j, Y', strtotime($user['created_at']))) ?></td>
                                    <td class="p-5 text-right whitespace-nowrap">
                                        <a href="user-edit.php?id=<?= (int) $user['id'] ?>" class="text-blue-600 hover:text-blue-800 mr-4" title="Edit">
                                            <i class="fa-solid fa-edit"></i> Edit
                                        </a>
                                        <?php if ((int) $user['id'] !== $selfId): ?>
                                            <form method="post" action="" class="inline" onsubmit="return confirm('Delete user &quot;<?= htmlspecialchars($user['username'], ENT_QUOTES) ?>&quot;?');">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="delete_id" value="<?= (int) $user['id'] ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                                    <i class="fa-solid fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>

                <?php if ($totalPages > 1): ?>
                    <div class="border-t p-5 flex justify-between items-center">
                        <p class="text-sm text-gray-500">Page <?= $page ?> of <?= $totalPages ?></p>
                        <div class="flex gap-2">
                            <?php if ($page > 1): ?>
                                <a href="?<?= qs(['page' => $page - 1]) ?>" class="px-3 py-1.5 border border-gray-300 rounded text-sm font-medium hover:bg-gray-50 transition"><i class="fa-solid fa-chevron-left mr-1"></i> Previous</a>
                            <?php endif; ?>
                            <?php if ($page < $totalPages): ?>
                                <a href="?<?= qs(['page' => $page + 1]) ?>" class="px-3 py-1.5 border border-gray-300 rounded text-sm font-medium hover:bg-gray-50 transition">Next <i class="fa-solid fa-chevron-right ml-1"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

        </div>

    </div>

</body>
</html>
