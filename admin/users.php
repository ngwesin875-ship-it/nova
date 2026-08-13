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
    if (!isset($overrides['page'])) {
        unset($params['page']);
    }
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
<body class="bg-slate-100">

    <?php include __DIR__ . '/../includes/admin-sidebar.php'; ?>

    <div class="ml-64 flex flex-col h-screen">

        <?php $pageTitle = 'Users'; include __DIR__ . '/../includes/admin-topbar.php'; ?>

        <div class="flex-1 overflow-y-auto p-6 space-y-6">

            <?php if ($flash): ?>
                <div class="mb-6 px-5 py-4 rounded-xl shadow-sm text-xs font-medium <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' ?>">
                    <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-2"></i>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 mb-6 p-5">
                <form method="get" action="" class="flex flex-wrap items-end gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-semibold text-slate-900 mb-1">Search</label>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by username or email..." class="w-full px-3 py-1.5 text-xs border border-slate-200 rounded-xl border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="px-3 py-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl text-xs shadow-sm transition-all text-sm font-semibold hover:bg-blue-700 transition">
                            <i class="fa-solid fa-filter mr-1"></i> Filter
                        </button>
                        <a href="users.php" class="px-3 py-1.5 text-xs border border-slate-200 text-slate-900 rounded-lg text-sm font-semibold hover:bg-slate-50/50 transition-colors transition">
                            <i class="fa-solid fa-undo mr-1"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60">

                <div class="border-b p-5">
                    <h3 class="text-xl font-bold">All Users <span class="text-xs font-normal text-slate-500">(<?= number_format($total) ?> total)</span></h3>
                </div>

                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-slate-50/50 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            <th class="px-3 py-2 text-xs">No</th>
                            <th class="px-3 py-2 text-xs">User</th>
                            <th class="px-3 py-2 text-xs">Email</th>
                            <th class="px-3 py-2 text-xs">Role</th>
                            <th class="px-3 py-2 text-xs">Subscription</th>
                            <th class="px-3 py-2 text-xs">Created</th>
                            <th class="px-3 py-2 text-xs text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="7" class="p-10 text-center text-slate-500">
                                    <i class="fa-solid fa-users text-4xl mb-3 block"></i>
                                    No users found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $seq = isset($page, $limit) ? ($page - 1) * $limit + 1 : 1; ?>
                            <?php foreach ($users as $user): ?>
                                <tr class="border-b hover:bg-slate-50/50 transition-colors">
                                    <td class="px-3 py-2 text-xs text-slate-500"><?= $seq++ ?></td>
                                    <td class="px-3 py-2 text-xs">
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
                                    <td class="px-3 py-2 text-xs text-slate-500"><?= htmlspecialchars($user['email']) ?></td>
                                    <td class="px-3 py-2 text-xs">
                                        <?php if ($user['role'] === 'admin'): ?>
                                            <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-medium">Admin</span>
                                        <?php else: ?>
                                            <span class="bg-slate-100 text-slate-900 px-3 py-1 rounded-full text-xs font-medium">User</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-3 py-2 text-xs">
                                        <?php
                                        $subStatus = $user['sub_status'] ?? 'inactive';
                                        if ($subStatus === 'active'):
                                        ?>
                                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium">Active</span>
                                        <?php else: ?>
                                            <span class="bg-slate-100 text-slate-500 px-3 py-1 rounded-full text-xs font-medium">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-3 py-2 text-xs text-slate-500 text-sm"><?= htmlspecialchars(date('M j, Y', strtotime($user['created_at']))) ?></td>
                                    <td class="px-3 py-2 text-xs text-right whitespace-nowrap">
                                        <a href="user-edit.php?id=<?= (int) $user['id'] ?>" class="text-blue-500 hover:text-blue-600 hover:bg-blue-50 p-1.5 rounded-lg transition-colors mr-4" title="Edit">
                                            <i class="fa-solid fa-edit"></i> Edit
                                        </a>
                                        <?php if ((int) $user['id'] !== $selfId): ?>
                                            <form method="post" action="" class="inline" onsubmit="return confirm('Delete user &quot;<?= htmlspecialchars($user['username'], ENT_QUOTES) ?>&quot;?');">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="delete_id" value="<?= (int) $user['id'] ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-600 hover:bg-red-50 p-1.5 rounded-lg transition-colors" title="Delete">
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
                        <p class="text-sm text-slate-500">Page <?= $page ?> of <?= $totalPages ?></p>
                        <div class="flex gap-2">
                            <?php if ($page > 1): ?>
                                <a href="users.php?<?= qs(['page' => $page - 1]) ?>" class="px-3 py-1.5 border border-slate-200 rounded text-xs font-medium text-slate-700 hover:text-blue-600 hover:bg-slate-50/50 transition-colors"><i class="fa-solid fa-chevron-left mr-1"></i> Previous</a>
                            <?php else: ?>
                                <span class="px-3 py-1.5 border border-slate-200 rounded text-xs font-medium text-slate-400 bg-slate-50 opacity-50 cursor-not-allowed"><i class="fa-solid fa-chevron-left mr-1"></i> Previous</span>
                            <?php endif; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="users.php?<?= qs(['page' => $page + 1]) ?>" class="px-3 py-1.5 border border-slate-200 rounded text-xs font-medium text-slate-700 hover:text-blue-600 hover:bg-slate-50/50 transition-colors">Next <i class="fa-solid fa-chevron-right ml-1"></i></a>
                            <?php else: ?>
                                <span class="px-3 py-1.5 border border-slate-200 rounded text-xs font-medium text-slate-400 bg-slate-50 opacity-50 cursor-not-allowed">Next <i class="fa-solid fa-chevron-right ml-1"></i></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

        </div>

    </div>

</body>
</html>
