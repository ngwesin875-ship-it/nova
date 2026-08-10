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

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$payment = getPaymentById($id);

if (!$payment) {
    flashMessage('error', 'Payment not found.');
    header('Location: payments.php');
    exit;
}

$errorMessage = '';

$subscriptionId = (int) $payment['subscription_id'];
$amount = (float) $payment['amount'];
$paymentMethod = $payment['payment_method'];
$accountName = $payment['account_name'] ?? '';
$accountPhone = $payment['account_phone'] ?? '';
$receiptImage = $payment['receipt_image'] ?? '';
$status = $payment['status'];

$subscriptions = getUserSubscriptions();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errorMessage = 'Invalid CSRF token.';
    } else {
        $subscriptionId = (int) ($_POST['subscription_id'] ?? 0);
        $amount = (float) ($_POST['amount'] ?? 0);
        $paymentMethod = trim($_POST['payment_method'] ?? '');
        $accountName = trim($_POST['account_name'] ?? '');
        $accountPhone = trim($_POST['account_phone'] ?? '');
        $status = trim($_POST['status'] ?? 'pending');

        if ($subscriptionId <= 0) {
            $errorMessage = 'Please select a subscription.';
        } elseif ($amount <= 0) {
            $errorMessage = 'Amount must be greater than 0.';
        } elseif ($paymentMethod === '') {
            $errorMessage = 'Payment method is required.';
        } elseif ($accountName === '') {
            $errorMessage = 'Account name is required.';
        } elseif ($accountPhone === '') {
            $errorMessage = 'Account phone is required.';
        } elseif (!in_array($status, ['pending', 'success', 'failed', 'refunded'])) {
            $errorMessage = 'Invalid status selected.';
        } else {
            $newReceipt = null;
            if (!empty($_FILES['receipt_image']['name'])) {
                $targetDir = __DIR__ . '/../uploads/receipts/';
                if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
                $ext = strtolower(pathinfo($_FILES['receipt_image']['name'], PATHINFO_EXTENSION));
                $newFilename = 'receipt_' . $id . '_' . time() . '.' . $ext;
                $targetPath = $targetDir . $newFilename;
                if (move_uploaded_file($_FILES['receipt_image']['tmp_name'], $targetPath)) {
                    $newReceipt = 'uploads/receipts/' . $newFilename;
                }
            }
            if (updatePayment($id, $subscriptionId, $amount, $paymentMethod, $accountName, $accountPhone, $newReceipt, $status)) {
                $subDb = getDB();
                if ($status === 'success') {
                    $subStmt = $subDb->prepare('UPDATE user_subscriptions SET payment_status = "paid" WHERE id = ?');
                    if ($subStmt) {
                        $subStmt->bind_param('i', $subscriptionId);
                        $subStmt->execute();
                    }
                } elseif (in_array($status, ['failed', 'refunded'])) {
                    $subStmt = $subDb->prepare('UPDATE user_subscriptions SET payment_status = ? WHERE id = ?');
                    if ($subStmt) {
                        $subStmt->bind_param('si', $status, $subscriptionId);
                        $subStmt->execute();
                    }
                }
                flashMessage('success', 'Payment updated successfully.');
                header('Location: payments.php');
                exit;
            } else {
                $errorMessage = 'Failed to update payment.';
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
    <title>Nova News - Edit Payment</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-slate-100">

    <?php include __DIR__ . '/../includes/admin-sidebar.php'; ?>

    <div class="ml-64 flex flex-col h-screen">

        <?php $pageTitle = 'Edit Payment'; include __DIR__ . '/../includes/admin-topbar.php'; ?>

        <div class="flex-1 overflow-y-auto p-6 space-y-6">

            <?php if ($errorMessage): ?>
                <div class="mb-6 px-5 py-4 rounded-xl shadow-sm text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                    <i class="fa-solid fa-exclamation-circle mr-2"></i> <?= htmlspecialchars($errorMessage) ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 max-w-2xl">

                <div class="border-b p-5 flex items-center gap-3">
                    <i class="fa-solid fa-credit-card text-blue-600 text-xl"></i>
                    <h3 class="text-xl font-bold">Payment #<?= (int) $payment['id'] ?></h3>
                </div>

                <form method="post" action="" enctype="multipart/form-data" class="px-3 py-2 text-xs space-y-5">
                    <?= csrfField() ?>

                    <div>
                        <label for="subscription_id" class="block text-xs font-semibold text-slate-900 mb-1">Subscription <span class="text-red-600">*</span></label>
                        <select id="subscription_id" name="subscription_id" required class="w-full px-3 py-1.5 text-xs.5 border border-slate-200 rounded-xl border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                            <option value="">-- Select Subscription --</option>
                            <?php foreach ($subscriptions as $sub): ?>
                                <option value="<?= (int) $sub['id'] ?>" <?= $subscriptionId === (int) $sub['id'] ? 'selected' : '' ?>>
                                    #<?= (int) $sub['id'] ?> - <?= htmlspecialchars($sub['username']) ?> (<?= htmlspecialchars($sub['plan_name']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="grid md:grid-cols-3 gap-5">
                        <div>
                            <label for="amount" class="block text-xs font-semibold text-slate-900 mb-1">Amount (MMK) <span class="text-red-600">*</span></label>
                            <input type="number" id="amount" name="amount" value="<?= number_format($amount, 0) ?>" step="1" min="1" required class="w-full px-3 py-1.5 text-xs.5 border border-slate-200 rounded-xl border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                        </div>
                        <div>
                            <label for="payment_method" class="block text-xs font-semibold text-slate-900 mb-1">Payment Method <span class="text-red-600">*</span></label>
                            <select id="payment_method" name="payment_method" required class="w-full px-3 py-1.5 text-xs.5 border border-slate-200 rounded-xl border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                                <option value="">-- Select --</option>
                                <option value="Kpay" <?= $paymentMethod === 'Kpay' ? 'selected' : '' ?>>Kpay</option>
                                <option value="Wave Pay" <?= $paymentMethod === 'Wave Pay' ? 'selected' : '' ?>>Wave Pay</option>
                                <option value="AYA Pay" <?= $paymentMethod === 'AYA Pay' ? 'selected' : '' ?>>AYA Pay</option>
                            </select>
                        </div>
                        <div>
                            <label for="status" class="block text-xs font-semibold text-slate-900 mb-1">Status <span class="text-red-600">*</span></label>
                            <select id="status" name="status" required class="w-full px-3 py-1.5 text-xs.5 border border-slate-200 rounded-xl border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                                <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="success" <?= $status === 'success' ? 'selected' : '' ?>>Success</option>
                                <option value="failed" <?= $status === 'failed' ? 'selected' : '' ?>>Failed</option>
                                <option value="refunded" <?= $status === 'refunded' ? 'selected' : '' ?>>Refunded</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-5">
                        <div>
                            <label for="account_name" class="block text-xs font-semibold text-slate-900 mb-1">Account Name <span class="text-red-600">*</span></label>
                            <input type="text" id="account_name" name="account_name" value="<?= htmlspecialchars($accountName) ?>" required class="w-full px-3 py-1.5 text-xs.5 border border-slate-200 rounded-xl border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                        </div>
                        <div>
                            <label for="account_phone" class="block text-xs font-semibold text-slate-900 mb-1">Account Phone <span class="text-red-600">*</span></label>
                            <input type="text" id="account_phone" name="account_phone" value="<?= htmlspecialchars($accountPhone) ?>" required class="w-full px-3 py-1.5 text-xs.5 border border-slate-200 rounded-xl border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                        </div>
                    </div>

                    <div>
                        <label for="receipt_image" class="block text-xs font-semibold text-slate-900 mb-1">Receipt Image</label>
                        <?php if ($receiptImage): ?>
                            <div class="mb-2">
                                <a href="/Nova_News/<?= htmlspecialchars($receiptImage) ?>" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">
                                    <i class="fa-solid fa-image"></i> View Current Receipt
                                </a>
                            </div>
                        <?php endif; ?>
                        <input type="file" id="receipt_image" name="receipt_image" accept="image/*" class="w-full px-3 py-1.5 text-xs.5 border border-slate-200 rounded-xl border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100">
                        <p class="text-xs text-slate-500 mt-1">Leave empty to keep current receipt.</p>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <a href="payments.php" class="px-6 py-2.5 border border-slate-200 text-slate-900 rounded-lg font-semibold hover:bg-slate-50/50 transition-colors transition"><i class="fa-solid fa-arrow-left mr-1"></i> Back to Payments</a>
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                            <i class="fa-solid fa-save mr-1"></i> Update Payment
                        </button>
                    </div>
                </form>

            </div>

        </div>

    </div>

</body>
</html>
