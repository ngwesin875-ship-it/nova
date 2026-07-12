<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/payments.php';
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
        header('Location: payments.php');
        exit;
    }
    $deleteId = (int) $_POST['delete_id'];
    if (deletePayment($deleteId)) {
        flashMessage('success', 'Payment deleted successfully.');
    } else {
        flashMessage('error', 'Failed to delete payment.');
    }
    header('Location: payments.php');
    exit;
}

$payments = getAllPayments();

$totalRevenue = 0.0;
$paymentCount = 0;
$pendingApprovals = 0;
$db = getDB();
$revResult = $db->query("SELECT COALESCE(SUM(amount), 0) AS total, COUNT(*) AS count FROM payments WHERE status = 'success'");
if ($revResult) {
    $row = $revResult->fetch_assoc();
    $totalRevenue = (float) ($row['total'] ?? 0);
    $paymentCount = (int) ($row['count'] ?? 0);
}
$pendingApprovals = $notifCounts['pending_payments'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova News - Premium Member</title>
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
            <a href="categories.php" class="flex items-center px-6 py-4 hover:bg-slate-800"><i class="fa-solid fa-folder mr-4"></i> Categories</a>
            <a href="users.php" class="flex items-center px-6 py-4 hover:bg-slate-800"><i class="fa-solid fa-users mr-4"></i> Users</a>
            <a href="plans.php" class="flex items-center px-6 py-4 hover:bg-slate-800"><i class="fa-solid fa-gem mr-4"></i> Subscription Plans</a>
            <a href="user-subscriptions.php" class="flex items-center px-6 py-4 hover:bg-slate-800"><i class="fa-solid fa-file-contract mr-4"></i> User Subscriptions</a>
            <a href="payments.php" class="flex items-center px-6 py-4 bg-blue-600"><i class="fa-solid fa-credit-card mr-4"></i> Payments</a>
            <a href="payment-services.php" class="flex items-center px-6 py-4 hover:bg-slate-800"><i class="fa-solid fa-money-bill-transfer mr-4"></i> Payment Services</a>
            
        </nav>

            <a href="/Nova_News/public/signin.php" class="flex items-center px-6 py-4 hover:bg-red-600"><i class="fa-solid fa-right-from-bracket mr-4"></i> Logout</a>
    </aside>

    <div class="ml-72 flex flex-col h-screen">

        <header class="bg-white shadow h-16 flex justify-between items-center px-8 shrink-0">
            <h2 class="text-3xl font-bold">Premium Members</h2>
            <div class="flex items-center gap-6">
                <button class="relative">
                    <i class="fa-regular fa-bell text-xl"></i>
                    <?php if ($totalNotifs > 0): ?>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center"><?= $totalNotifs > 9 ? '9+' : $totalNotifs ?></span>
                    <?php endif; ?>
                </button>
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

            <div class="bg-white rounded-xl shadow p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Total Revenue</p>
                        <h2 class="text-4xl font-bold mt-1">$<?= number_format($totalRevenue, 2) ?></h2>
                        <p class="text-green-500 text-sm mt-1"><?= number_format($paymentCount) ?> successful payments</p>
                    </div>
                    <div class="w-16 h-16 rounded-xl bg-yellow-100 flex items-center justify-center">
                        <i class="fa-solid fa-dollar-sign text-yellow-600 text-3xl"></i>
                    </div>
                </div>
            </div>

            <?php if ($pendingApprovals > 0): ?>
                <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 flex items-center gap-3 text-sm text-amber-800">
                    <i class="fa-solid fa-clock"></i>
                    <span><strong><?= $pendingApprovals ?></strong> payment<?= $pendingApprovals > 1 ? 's' : '' ?> pending approval. Set status to "Success" to activate premium access.</span>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow">

                <div class="border-b p-5">
                    <h3 class="text-xl font-bold">All Payments</h3>
                </div>

                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-gray-50 text-left text-sm font-semibold text-gray-600">
                            <th class="p-5">ID</th>
                            <th class="p-5">User</th>
                            <th class="p-5">Plan</th>
                            <th class="p-5">Amount</th>
                            <th class="p-5">Method</th>
                            <th class="p-5">Account Name</th>
                            <th class="p-5">Phone</th>
                            <th class="p-5">Receipt</th>
                            <th class="p-5">Status</th>
                            <th class="p-5">Paid At</th>
                            <th class="p-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($payments)): ?>
                            <tr>
                                <td colspan="11" class="p-10 text-center text-gray-500">
                                    <i class="fa-solid fa-credit-card text-4xl mb-3 block"></i>
                                    No payments found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($payments as $payment): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="p-5 text-gray-500"><?= (int) $payment['id'] ?></td>
                                    <td class="p-5 font-medium"><?= htmlspecialchars($payment['username']) ?></td>
                                    <td class="p-5 text-gray-600"><?= htmlspecialchars($payment['plan_name']) ?></td>
                                    <td class="p-5">$<?= number_format((float) $payment['amount'], 2) ?></td>
                                    <td class="p-5"><?= htmlspecialchars($payment['payment_method']) ?></td>
                                    <td class="p-5"><?= htmlspecialchars($payment['account_name'] ?? '-') ?></td>
                                    <td class="p-5"><?= htmlspecialchars($payment['account_phone'] ?? '-') ?></td>
                                    <td class="p-5">
                                        <?php if (!empty($payment['receipt_image'])): ?>
                                            <a href="/Nova_News/<?= htmlspecialchars($payment['receipt_image']) ?>" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">
                                                <i class="fa-solid fa-image"></i> View
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-5">
                                        <?php if ($payment['status'] === 'success'): ?>
                                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-medium">Success</span>
                                        <?php elseif ($payment['status'] === 'pending'): ?>
                                            <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-sm font-medium">Pending</span>
                                        <?php elseif ($payment['status'] === 'failed'): ?>
                                            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm font-medium">Failed</span>
                                        <?php else: ?>
                                            <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-sm font-medium">Refunded</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-5 text-gray-500 text-sm"><?= htmlspecialchars(date('M j, Y g:i A', strtotime($payment['paid_at']))) ?></td>
                                    <td class="p-5 text-right whitespace-nowrap">
                                        <a href="payment-edit.php?id=<?= (int) $payment['id'] ?>" class="text-blue-600 hover:text-blue-800 mr-4"><i class="fa-solid fa-edit"></i> Edit</a>
                                        <form method="post" action="" class="inline" onsubmit="return confirm('Delete payment #<?= (int) $payment['id'] ?>?');">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="delete_id" value="<?= (int) $payment['id'] ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-800"><i class="fa-solid fa-trash"></i> Delete</button>
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
