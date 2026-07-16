<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/user_subscription.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/notifications.php';

requireAdmin();

$notifCounts = getNotificationCounts();
$totalNotifs = array_sum($notifCounts);

$displayName = trim($_SESSION['username'] ?? 'Admin');
$displayInitial = strtoupper(substr($displayName, 0, 1));
$displayRole = (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'Administrator' : 'Member';

$flash = getFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        flashMessage('error', 'Invalid CSRF token.');
        header('Location: user-subscriptions.php');
        exit;
    }
    $deleteId = (int) $_POST['delete_id'];
    if (deleteUserSubscription($deleteId)) {
        flashMessage('success', 'User subscription deleted successfully.');
    } else {
        flashMessage('error', 'Failed to delete user subscription.');
    }
    header('Location: user-subscriptions.php');
    exit;
}

$subscriptions = getAllUserSubscriptions();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova News - User Subscriptions</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-gray-100">

    <aside class="fixed left-0 top-0 h-screen overflow-y-auto z-50 w-72 bg-slate-900 text-white flex flex-col">

        <div class="h-16 flex items-center px-6 border-b border-slate-700">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center text-2xl font-bold text-white"><?= htmlspecialchars($displayInitial) ?></div>
                <div>
                    <h3 class="font-semibold"><?= htmlspecialchars($displayName) ?></h3>
                    <p class="text-sm text-green-400">● <?= htmlspecialchars($displayRole) ?></p>
                </div>
            </div>
        </div>

        <nav class="mt-6 flex-1">
            <a href="index.php" class="flex items-center px-6 py-4 hover:bg-slate-800"><i class="fa-solid fa-house mr-4"></i> Dashboard</a>
            <a href="posts.php" class="flex items-center px-6 py-4 hover:bg-slate-800"><i class="fa-solid fa-newspaper mr-4"></i> Posts</a>
            <a href="likes.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-thumbs-up mr-4"></i> Likes &amp; Dislikes
            </a>
            <a href="comments.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-comments mr-4"></i> Comments
            </a>
            <a href="categories.php" class="flex items-center px-6 py-4 hover:bg-slate-800"><i class="fa-solid fa-folder mr-4"></i> Categories</a>
            <a href="users.php" class="flex items-center px-6 py-4 hover:bg-slate-800"><i class="fa-solid fa-users mr-4"></i> Users</a>
            <a href="plans.php" class="flex items-center px-6 py-4 hover:bg-slate-800"><i class="fa-solid fa-gem mr-4"></i> Subscription Plans</a>
            <a href="user-subscriptions.php" class="flex items-center px-6 py-4 bg-blue-600"><i class="fa-solid fa-file-contract mr-4"></i> User Subscriptions</a>
            <a href="payments.php" class="flex items-center px-6 py-4 hover:bg-slate-800"><i class="fa-solid fa-credit-card mr-4"></i> Payments</a>
            <a href="payment-services.php" class="flex items-center px-6 py-4 hover:bg-slate-800"><i class="fa-solid fa-money-bill-transfer mr-4"></i> Payment Services</a>
        </nav>
           <a href="/Nova_News/public/signin.php" class="flex items-center px-6 py-4 hover:bg-red-600"><i class="fa-solid fa-right-from-bracket mr-4"></i> Logout</a>

    </aside>

    <div class="ml-72 flex flex-col h-screen">

        <header class="bg-white shadow h-16 flex justify-between items-center px-8 shrink-0">
            <h2 class="text-3xl font-bold">User Subscriptions</h2>
            <div class="flex items-center gap-4">
                <?php include __DIR__ . '/../includes/admin-header.php'; ?>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-sm font-bold text-white"><?= htmlspecialchars($displayInitial) ?></div>
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

            <div id="action-toast" class="hidden fixed top-4 right-4 z-50 px-5 py-3 rounded-xl shadow-lg text-sm font-medium"></div>

            <div class="bg-white rounded-xl shadow">

                <div class="border-b p-5 flex justify-between items-center">
                    <h3 class="text-xl font-bold">All Subscriptions</h3>
                    <a href="user-subscription-create.php" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
                        <i class="fa-solid fa-plus mr-1"></i> Add New Subscription
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px]">
                        <thead>
                            <tr class="border-b bg-gray-50 text-left text-sm font-semibold text-gray-600">
                                <th class="p-5">ID</th>
                                <th class="p-5">User</th>
                                <th class="p-5">Plan</th>
                                <th class="p-5">Start Date</th>
                                <th class="p-5">End Date</th>
                                <th class="p-5">Status</th>
                                <th class="p-5">Payment</th>
                                <th class="p-5">Created</th>
                                <th class="p-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($subscriptions)): ?>
                                <tr>
                                    <td colspan="9" class="p-10 text-center text-gray-500">
                                        <i class="fa-solid fa-file-contract text-4xl mb-3 block"></i>
                                        No subscriptions found.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($subscriptions as $sub): ?>
                                    <tr class="border-b hover:bg-gray-50 <?= $sub['payment_status'] === 'pending' ? 'bg-amber-50/50' : '' ?>">
                                        <td class="p-5 text-gray-500"><?= (int) $sub['id'] ?></td>
                                        <td class="p-5 font-medium">
                                            <?= htmlspecialchars($sub['username']) ?>
                                            <div class="text-xs text-gray-500"><?= htmlspecialchars($sub['email']) ?></div>
                                        </td>
                                        <td class="p-5 text-gray-600"><?= htmlspecialchars($sub['plan_name']) ?></td>
                                        <td class="p-5 text-gray-600 text-sm"><?= htmlspecialchars($sub['start_date']) ?></td>
                                        <td class="p-5 text-gray-600 text-sm"><?= htmlspecialchars($sub['end_date']) ?></td>
                                        <td class="p-5">
                                            <?php if ($sub['status'] === 'active'): ?>
                                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-medium">Active</span>
                                            <?php elseif ($sub['status'] === 'expired'): ?>
                                                <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-sm font-medium">Expired</span>
                                            <?php else: ?>
                                                <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm font-medium">Cancelled</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-5">
                                            <?php if ($sub['payment_status'] === 'paid'): ?>
                                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-medium">Paid</span>
                                            <?php elseif ($sub['payment_status'] === 'pending'): ?>
                                                <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-sm font-medium">Pending</span>
                                            <?php else: ?>
                                                <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm font-medium">Failed</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="p-5 text-gray-500 text-sm"><?= htmlspecialchars(date('M j, Y', strtotime($sub['created_at']))) ?></td>
                                        <td class="p-5 text-right whitespace-nowrap">
                                            <?php if ($sub['payment_status'] === 'pending'): ?>
                                                <button onclick="approveSubscription(<?= (int) $sub['id'] ?>)"
                                                    class="text-green-600 hover:text-green-800 mr-3 font-semibold text-sm bg-green-50 px-3 py-1 rounded-lg hover:bg-green-100 transition">
                                                    <i class="fa-solid fa-check mr-1"></i> Approve
                                                </button>
                                                <button onclick="rejectSubscription(<?= (int) $sub['id'] ?>)"
                                                    class="text-red-600 hover:text-red-800 mr-3 font-semibold text-sm bg-red-50 px-3 py-1 rounded-lg hover:bg-red-100 transition">
                                                    <i class="fa-solid fa-times mr-1"></i> Reject
                                                </button>
                                            <?php endif; ?>
                                            <a href="user-subscription-edit.php?id=<?= (int) $sub['id'] ?>" class="text-blue-600 hover:text-blue-800 mr-3"><i class="fa-solid fa-edit"></i></a>
                                            <form method="post" action="" class="inline" onsubmit="return confirm('Delete this subscription?');">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="delete_id" value="<?= (int) $sub['id'] ?>">
                                                <button type="submit" class="text-red-600 hover:text-red-800"><i class="fa-solid fa-trash"></i></button>
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

    </div>

<script>
function showToast(message, type) {
    const toast = document.getElementById('action-toast');
    toast.textContent = message;
    toast.className = 'fixed top-4 right-4 z-50 px-5 py-3 rounded-xl shadow-lg text-sm font-medium ' +
        (type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white');
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 3000);
}

function handleResponse(response) {
    return response.json().then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Something went wrong.', 'error');
        }
    });
}

function approveSubscription(id) {
    if (!confirm('Approve this subscription?')) return;
    fetch('/Nova_News/admin/approve-subscription.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=approve&subscription_id=' + id
    }).then(handleResponse);
}

function rejectSubscription(id) {
    if (!confirm('Reject this subscription?')) return;
    fetch('/Nova_News/admin/approve-subscription.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=reject&subscription_id=' + id
    }).then(handleResponse);
}
</script>

</body>
</html>
