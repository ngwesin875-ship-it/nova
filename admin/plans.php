<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/subscription.php';
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
        header('Location: plans.php');
        exit;
    }
    $deleteId = (int) $_POST['delete_id'];
    if (deletePlan($deleteId)) {
        flashMessage('success', 'Subscription plan deleted successfully.');
    } else {
        flashMessage('error', 'Failed to delete plan.');
    }
    header('Location: plans.php');
    exit;
}

$plans = getAllPlans();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova News - Subscription Plans</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-slate-100">

    <?php include __DIR__ . '/../includes/admin-sidebar.php'; ?>

    <div class="ml-64 flex flex-col h-screen">

        <?php $pageTitle = 'Subscription Plans'; include __DIR__ . '/../includes/admin-topbar.php'; ?>

        <div class="flex-1 overflow-y-auto p-6 space-y-6">

            <?php if ($flash): ?>
                <div class="mb-6 px-5 py-4 rounded-xl shadow-sm text-xs font-medium <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' ?>">
                    <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-2"></i>
                    <?= htmlspecialchars($flash['message']) ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60">

                <div class="border-b p-5 flex justify-between items-center">
                    <h3 class="text-xl font-bold">All Plans</h3>
                    <a href="plan-create.php" class="px-3 py-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl text-xs shadow-sm transition-all text-sm font-semibold hover:bg-blue-700 transition">
                        <i class="fa-solid fa-plus mr-1"></i> Add New Plan
                    </a>
                </div>

                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-slate-50/50 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            <th class="px-3 py-2 text-xs">No</th>
                            <th class="px-3 py-2 text-xs">Name</th>
                            <th class="px-3 py-2 text-xs">Duration</th>
                            <th class="px-3 py-2 text-xs">Price</th>
                            <th class="px-3 py-2 text-xs">Discount</th>
                            <th class="px-3 py-2 text-xs">Final Price</th>
                            <th class="px-3 py-2 text-xs">Status</th>
                            <th class="px-3 py-2 text-xs">Created</th>
                            <th class="px-3 py-2 text-xs text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($plans)): ?>
                            <tr>
                                <td colspan="9" class="p-10 text-center text-slate-500">
                                    <i class="fa-solid fa-gem text-4xl mb-3 block"></i>
                                    No plans found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $seq = isset($page, $limit) ? ($page - 1) * $limit + 1 : 1; ?>
                            <?php foreach ($plans as $plan): ?>
                                <tr class="border-b hover:bg-slate-50/50 transition-colors">
                                    <td class="px-3 py-2 text-xs text-slate-500"><?= $seq++ ?></td>
                                    <td class="px-3 py-2 text-xs font-medium"><?= htmlspecialchars($plan['name']) ?></td>
                                    <td class="px-3 py-2 text-xs text-slate-500"><?= (int) $plan['duration_months'] ?> month<?= (int) $plan['duration_months'] > 1 ? 's' : '' ?></td>
                                    <td class="px-3 py-2 text-xs"><?= number_format((float) $plan['price'], 0) ?> MMK</td>
                                    <td class="px-3 py-2 text-xs"><?= (float) $plan['discount_percentage'] > 0 ? number_format((float) $plan['discount_percentage'], 1) . '%' : '-' ?></td>
                                    <td class="px-3 py-2 text-xs font-semibold"><?= number_format((float) $plan['final_price'], 0) ?> MMK</td>
                                    <td class="px-3 py-2 text-xs">
                                        <?php if ((int) $plan['is_active']): ?>
                                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-medium">Active</span>
                                        <?php else: ?>
                                            <span class="bg-red-100 text-red-600 px-3 py-1 rounded-full text-xs font-medium">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-3 py-2 text-xs text-slate-500 text-sm"><?= htmlspecialchars(date('M j, Y', strtotime($plan['created_at']))) ?></td>
                                    <td class="px-3 py-2 text-xs text-right whitespace-nowrap">
                                        <a href="plan-edit.php?id=<?= (int) $plan['id'] ?>" class="text-blue-600 hover:text-blue-800 mr-4"><i class="fa-solid fa-edit"></i> Edit</a>
                                        <form method="post" action="" class="inline" onsubmit="return confirm('Delete plan &quot;<?= htmlspecialchars($plan['name'], ENT_QUOTES) ?>&quot;?');">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="delete_id" value="<?= (int) $plan['id'] ?>">
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
