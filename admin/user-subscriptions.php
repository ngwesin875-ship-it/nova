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
<body class="bg-slate-100">

    <?php include __DIR__ . '/../includes/admin-sidebar.php'; ?>

    <div class="ml-64 flex flex-col h-screen">

        <?php $pageTitle = 'User Subscriptions'; include __DIR__ . '/../includes/admin-topbar.php'; ?>

        <div class="flex-1 overflow-y-auto p-6 space-y-6">

            <?php if ($flash): ?>
                <div class="mb-6 px-5 py-4 rounded-xl shadow-sm text-xs font-medium <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' ?>">
                    <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-2"></i>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div id="action-toast" class="hidden fixed top-4 right-4 z-50 px-5 py-3 rounded-xl shadow-lg text-xs font-medium"></div>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60">

                <div class="border-b p-5 flex justify-between items-center">
                    <h3 class="text-xl font-bold">All Subscriptions</h3>
                    <a href="user-subscription-create.php" class="px-3 py-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl text-xs shadow-sm transition-all text-sm font-semibold hover:bg-blue-700 transition">
                        <i class="fa-solid fa-plus mr-1"></i> Add New Subscription
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px]">
                        <thead>
                            <tr class="border-b bg-slate-50/50 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="px-3 py-2 text-xs">ID</th>
                                <th class="px-3 py-2 text-xs">User</th>
                                <th class="px-3 py-2 text-xs">Plan</th>
                                <th class="px-3 py-2 text-xs">Start Date</th>
                                <th class="px-3 py-2 text-xs">End Date</th>
                                <th class="px-3 py-2 text-xs">Status</th>
                                <th class="px-3 py-2 text-xs">Payment</th>
                                <th class="px-3 py-2 text-xs">Created</th>
                                <th class="px-3 py-2 text-xs text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($subscriptions)): ?>
                                <tr>
                                    <td colspan="9" class="p-10 text-center text-slate-500">
                                        <i class="fa-solid fa-file-contract text-4xl mb-3 block"></i>
                                        No subscriptions found.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($subscriptions as $sub): ?>
                                    <tr class="border-b hover:bg-slate-50/50 transition-colors <?= $sub['payment_status'] === 'pending' ? 'bg-amber-50/50' : '' ?>">
                                        <td class="px-3 py-2 text-xs text-slate-500"><?= (int) $sub['id'] ?></td>
                                        <td class="px-3 py-2 text-xs font-medium">
                                            <?= htmlspecialchars($sub['username']) ?>
                                            <div class="text-xs text-slate-500"><?= htmlspecialchars($sub['email']) ?></div>
                                        </td>
                                        <td class="px-3 py-2 text-xs text-slate-500"><?= htmlspecialchars($sub['plan_name']) ?></td>
                                        <td class="px-3 py-2 text-xs text-slate-500 text-sm"><?= htmlspecialchars($sub['start_date']) ?></td>
                                        <td class="px-3 py-2 text-xs text-slate-500 text-sm"><?= htmlspecialchars($sub['end_date']) ?></td>
                                        <td class="px-3 py-2 text-xs">
                                            <?php if ($sub['status'] === 'active'): ?>
                                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium">Active</span>
                                            <?php elseif ($sub['status'] === 'expired'): ?>
                                                <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-medium">Expired</span>
                                            <?php else: ?>
                                                <span class="bg-red-100 text-red-600 px-3 py-1 rounded-full text-xs font-medium">Cancelled</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-3 py-2 text-xs">
                                            <?php if ($sub['payment_status'] === 'paid'): ?>
                                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium">Paid</span>
                                            <?php elseif ($sub['payment_status'] === 'pending'): ?>
                                                <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-medium">Pending</span>
                                            <?php else: ?>
                                                <span class="bg-red-100 text-red-600 px-3 py-1 rounded-full text-xs font-medium">Failed</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-3 py-2 text-xs text-slate-500 text-sm"><?= htmlspecialchars(date('M j, Y', strtotime($sub['created_at']))) ?></td>
                                        <td class="px-3 py-2 text-xs text-right whitespace-nowrap">
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
    toast.className = 'fixed top-4 right-4 z-50 px-5 py-3 rounded-xl shadow-lg text-xs font-medium ' +
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
