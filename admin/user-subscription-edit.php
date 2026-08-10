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
$errors = [];

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    flashMessage('error', 'Invalid subscription ID.');
    header('Location: user-subscriptions.php');
    exit;
}

$subscription = getUserSubscriptionById($id);
if (!$subscription) {
    flashMessage('error', 'Subscription not found.');
    header('Location: user-subscriptions.php');
    exit;
}

$old = [
    'user_id' => $subscription['user_id'],
    'plan_id' => $subscription['plan_id'],
    'start_date' => $subscription['start_date'],
    'end_date' => $subscription['end_date'],
    'status' => $subscription['status'],
    'payment_status' => $subscription['payment_status']
];

$users = getAllUsersForSelect();
$plans = getAllPlansForSelect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        flashMessage('error', 'Invalid CSRF token.');
        header('Location: user-subscription-edit.php?id=' . $id);
        exit;
    }

    $old['user_id'] = (int) ($_POST['user_id'] ?? 0);
    $old['plan_id'] = (int) ($_POST['plan_id'] ?? 0);
    $old['start_date'] = $_POST['start_date'] ?? '';
    $old['end_date'] = $_POST['end_date'] ?? '';
    $old['status'] = $_POST['status'] ?? 'active';
    $old['payment_status'] = $_POST['payment_status'] ?? 'pending';

    if (empty($old['user_id'])) $errors[] = 'User is required.';
    if (empty($old['plan_id'])) $errors[] = 'Plan is required.';
    if (empty($old['start_date'])) $errors[] = 'Start date is required.';
    if (empty($old['end_date'])) $errors[] = 'End date is required.';
    if (!in_array($old['status'], ['active', 'expired', 'cancelled'])) $errors[] = 'Invalid status.';
    if (!in_array($old['payment_status'], ['pending', 'paid', 'failed'])) $errors[] = 'Invalid payment status.';

    if (empty($errors)) {
        if (updateUserSubscription($id, $old['user_id'], $old['plan_id'], $old['start_date'], $old['end_date'], $old['status'], $old['payment_status'])) {
            flashMessage('success', 'Subscription updated successfully.');
            header('Location: user-subscriptions.php');
            exit;
        } else {
            $errors[] = 'Failed to update subscription.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova News - Edit User Subscription</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-slate-100">

    <?php include __DIR__ . '/../includes/admin-sidebar.php'; ?>

    <div class="ml-64 flex flex-col h-screen">

        <?php $pageTitle = 'Edit Subscription'; include __DIR__ . '/../includes/admin-topbar.php'; ?>

        <div class="flex-1 overflow-y-auto p-6 space-y-6">

            <?php if ($flash): ?>
                <div class="mb-6 px-5 py-4 rounded-xl shadow-sm text-xs font-medium <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' ?>">
                    <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-2"></i>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="mb-6 px-5 py-4 rounded-xl shadow-sm text-xs font-medium bg-red-100 text-red-800 border border-red-200">
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
                            <label class="block text-xs font-medium text-slate-900 mb-1">User</label>
                            <select name="user_id" required class="w-full border border-slate-200 rounded-lg px-3 py-1.5 text-xs focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select User</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= (int) $user['id'] ?>" <?= $old['user_id'] == $user['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars($user['email']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-900 mb-1">Plan</label>
                            <select name="plan_id" required class="w-full border border-slate-200 rounded-lg px-3 py-1.5 text-xs focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Plan</option>
                                <?php foreach ($plans as $plan): ?>
                                    <option value="<?= (int) $plan['id'] ?>" <?= $old['plan_id'] == $plan['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($plan['name']) ?> (<?= number_format((float) $plan['final_price'], 0) ?> MMK)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-900 mb-1">Start Date</label>
                            <input type="date" name="start_date" value="<?= htmlspecialchars($old['start_date']) ?>" required class="w-full border border-slate-200 rounded-lg px-3 py-1.5 text-xs focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-900 mb-1">End Date</label>
                            <input type="date" name="end_date" value="<?= htmlspecialchars($old['end_date']) ?>" required class="w-full border border-slate-200 rounded-lg px-3 py-1.5 text-xs focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-900 mb-1">Status</label>
                            <select name="status" required class="w-full border border-slate-200 rounded-lg px-3 py-1.5 text-xs focus:ring-blue-500 focus:border-blue-500">
                                <option value="active" <?= $old['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="expired" <?= $old['status'] === 'expired' ? 'selected' : '' ?>>Expired</option>
                                <option value="cancelled" <?= $old['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-900 mb-1">Payment Status</label>
                            <select name="payment_status" required class="w-full border border-slate-200 rounded-lg px-3 py-1.5 text-xs focus:ring-blue-500 focus:border-blue-500">
                                <option value="pending" <?= $old['payment_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="paid" <?= $old['payment_status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                                <option value="failed" <?= $old['payment_status'] === 'failed' ? 'selected' : '' ?>>Failed</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-8 flex gap-4">
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                            Update Subscription
                        </button>
                        <a href="user-subscriptions.php" class="px-6 py-2 bg-gray-200 text-slate-900 rounded-lg font-semibold hover:bg-gray-300 transition">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>

        </div>

    </div>

</body>
</html>
