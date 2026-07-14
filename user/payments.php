<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/payments.php';

requireLogin();
if (isAdmin()) { header('Location: /Nova_News/admin/index.php'); exit; }

$flash = getFlash();
$payments = getUserPayments(currentUserId());
$pageTitle = 'My Payments - Nova News';
include __DIR__ . '/../includes/header.php';
?>

<main class="flex-1">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-extrabold text-theme-adaptive">Payments History</h1>
                <p class="text-slate-500 text-sm mt-1">View your payment history and subscription receipts.</p>
            </div>
            <a href="subscribe.php" class="px-5 py-2.5 bg-gradient-to-r from-amber-500 to-yellow-500 text-slate-900 font-bold rounded-xl hover:brightness-110 transition text-sm shadow-lg">
                <i class="fa-solid fa-plus mr-1"></i> New Subscription
            </a>
        </div>

        <?php if ($flash): ?>
            <div class="mb-6 px-5 py-4 rounded-xl shadow-sm text-sm font-medium <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' ?>">
                <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-2"></i>
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">

            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="p-5">ID</th>
                        <th class="p-5">Plan</th>
                        <th class="p-5">Amount</th>
                        <th class="p-5">Method</th>
                        <th class="p-5">Account</th>
                        <th class="p-5">Receipt</th>
                        <th class="p-5">Status</th>
                        <th class="p-5">Date</th>
                    </tr>
                </thead>
                <tbody>

                    <?php if (empty($payments)): ?>
                        <tr>
                                <td colspan="8" class="p-10 text-center text-slate-500">
                                <i class="fa-solid fa-credit-card text-4xl mb-3 block text-slate-400"></i>
                                <p class="text-lg font-medium mb-1">No payments yet</p>
                                <p class="text-sm">Subscribe to a plan to see your payment history here.</p>
                                <a href="subscribe.php" class="inline-block mt-4 px-5 py-2.5 bg-gradient-to-r from-amber-500 to-yellow-500 text-slate-900 font-bold rounded-xl hover:brightness-110 transition text-sm">
                                    View Plans
                                </a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr class="border-b border-slate-200 hover:bg-slate-50 transition-colors">
                                <td class="p-5 text-slate-500 font-mono text-sm">#<?= (int) $payment['id'] ?></td>
                                <td class="p-5 font-medium text-slate-900"><?= htmlspecialchars($payment['plan_name']) ?></td>
                                <td class="p-5 font-semibold text-amber-600">$<?= number_format((float) $payment['amount'], 2) ?></td>
                                <td class="p-5 text-slate-600"><?= htmlspecialchars($payment['payment_method']) ?></td>
                                <td class="p-5 text-slate-600 text-sm"><?= htmlspecialchars($payment['account_name'] ?? '-') ?><br><span class="text-slate-500"><?= htmlspecialchars($payment['account_phone'] ?? '') ?></span></td>
                                <td class="p-5">
                                    <?php if (!empty($payment['receipt_image'])): ?>
                                        <a href="/Nova_News/<?= htmlspecialchars($payment['receipt_image']) ?>" target="_blank" class="text-blue-500 hover:text-blue-600 text-sm">
                                            <i class="fa-solid fa-image"></i> View
                                        </a>
                                    <?php else: ?>
                                        <span class="text-slate-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-5">
                                    <?php if ($payment['status'] === 'success'): ?>
                                        <span class="bg-green-500/10 text-green-600 border border-green-500/30 px-3 py-1 rounded-full text-sm font-medium">Success</span>
                                    <?php elseif ($payment['status'] === 'pending'): ?>
                                        <span class="bg-yellow-500/10 text-yellow-600 border border-yellow-500/30 px-3 py-1 rounded-full text-sm font-medium">Pending</span>
                                    <?php elseif ($payment['status'] === 'failed'): ?>
                                        <span class="bg-red-500/10 text-red-600 border border-red-500/30 px-3 py-1 rounded-full text-sm font-medium">Failed</span>
                                    <?php else: ?>
                                        <span class="bg-gray-500/10 text-gray-600 border border-gray-500/30 px-3 py-1 rounded-full text-sm font-medium">Refunded</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-5 text-slate-500 text-sm"><?= htmlspecialchars(date('M j, Y g:i A', strtotime($payment['paid_at']))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

        </div>

        <?php if (!empty($payments)): ?>
            <p class="text-xs text-slate-500 mt-4 text-center">Showing <?= count($payments) ?> payment<?= count($payments) !== 1 ? 's' : '' ?></p>
        <?php endif; ?>

    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
