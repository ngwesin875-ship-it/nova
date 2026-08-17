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
<body class="bg-slate-100">

    <?php include __DIR__ . '/../includes/admin-sidebar.php'; ?>

    <div class="ml-64 flex flex-col h-screen">

        <?php $pageTitle = 'Premium Members'; include __DIR__ . '/../includes/admin-topbar.php'; ?>

        <div class="flex-1 overflow-y-auto p-6 space-y-6">

            <?php if ($flash): ?>
                <div class="mb-6 px-5 py-4 rounded-xl shadow-sm text-xs font-medium <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' ?>">
                    <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-2"></i>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-slate-500 text-xs font-medium">Total Revenue</p>
                        <h2 class="text-4xl font-bold mt-1"><?= number_format($totalRevenue, 0) ?> <span class="text-lg">MMK</span></h2>
                        <p class="text-green-500 text-sm mt-1"><?= number_format($paymentCount) ?> successful payments</p>
                    </div>
                    <div class="w-16 h-16 rounded-xl bg-yellow-100 flex items-center justify-center">
                        <i class="fa-solid fa-coins text-yellow-600 text-3xl"></i>
                    </div>
                </div>
            </div>

            <?php if ($pendingApprovals > 0): ?>
                <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 flex items-center gap-3 text-sm text-amber-800">
                    <i class="fa-solid fa-clock"></i>
                    <span><strong><?= $pendingApprovals ?></strong> payment<?= $pendingApprovals > 1 ? 's' : '' ?> pending approval. Set status to "Success" to activate premium access.</span>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60">

                <div class="border-b p-5">
                    <h3 class="text-xl font-bold">All Payments</h3>
                </div>

                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-slate-50/50 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            <th class="px-3 py-2 text-xs">No</th>
                            <th class="px-3 py-2 text-xs">User</th>
                            <th class="px-3 py-2 text-xs">Plan</th>
                            <th class="px-3 py-2 text-xs">Amount</th>
                            <th class="px-3 py-2 text-xs">Method</th>
                            <th class="px-3 py-2 text-xs">Account Name</th>
                            <th class="px-3 py-2 text-xs">Phone</th>
                            <th class="px-3 py-2 text-xs">Receipt</th>
                            <th class="px-3 py-2 text-xs">Status</th>
                            <th class="px-3 py-2 text-xs">Paid At</th>
                            <th class="px-3 py-2 text-xs text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($payments)): ?>
                            <tr>
                                <td colspan="11" class="p-10 text-center text-slate-500">
                                    <i class="fa-solid fa-credit-card text-4xl mb-3 block"></i>
                                    No payments found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $seq = isset($page, $limit) ? ($page - 1) * $limit + 1 : 1; ?>
                            <?php foreach ($payments as $payment): ?>
                                <tr class="border-b hover:bg-slate-50/50 transition-colors">
                                    <td class="px-3 py-2 text-xs text-slate-500"><?= $seq++ ?></td>
                                    <td class="px-3 py-2 text-xs font-medium"><?= htmlspecialchars($payment['username']) ?></td>
                                    <td class="px-3 py-2 text-xs text-slate-500"><?= htmlspecialchars($payment['plan_name']) ?></td>
                                    <td class="px-3 py-2 text-xs"><?= number_format((float) $payment['amount'], 0) ?> MMK</td>
                                    <td class="px-3 py-2 text-xs"><?= htmlspecialchars($payment['payment_method']) ?></td>
                                    <td class="px-3 py-2 text-xs"><?= htmlspecialchars($payment['account_name'] ?? '-') ?></td>
                                    <td class="px-3 py-2 text-xs"><?= htmlspecialchars($payment['account_phone'] ?? '-') ?></td>
                                    <td class="px-3 py-2 text-xs">
                                        <?php if (!empty($payment['receipt_image'])): ?>
                                            <a href="/Nova_News/<?= htmlspecialchars($payment['receipt_image']) ?>" target="_blank" class="inline-block">
                                                <img src="/Nova_News/<?= htmlspecialchars($payment['receipt_image']) ?>" alt="Receipt" class="w-12 h-12 object-cover rounded-lg border shadow-sm hover:scale-105 transition cursor-pointer">
                                            </a>
                                        <?php else: ?>
                                            <span class="text-slate-500">No receipt</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-3 py-2 text-xs">
                                        <?php if ($payment['status'] === 'success'): ?>
                                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium">Success</span>
                                        <?php elseif ($payment['status'] === 'pending'): ?>
                                            <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-medium">Pending</span>
                                        <?php elseif ($payment['status'] === 'failed'): ?>
                                            <span class="bg-red-100 text-red-600 px-3 py-1 rounded-full text-xs font-medium">Failed</span>
                                        <?php else: ?>
                                            <span class="bg-slate-100 text-slate-900 px-3 py-1 rounded-full text-xs font-medium">Refunded</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-3 py-2 text-xs text-slate-500 text-sm"><?= htmlspecialchars(date('M j, Y g:i A', strtotime($payment['paid_at']))) ?></td>
                                    <td class="px-3 py-2 text-xs text-right whitespace-nowrap">
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
