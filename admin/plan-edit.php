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

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$plan = getPlanById($id);

if (!$plan) {
    flashMessage('error', 'Plan not found.');
    header('Location: plans.php');
    exit;
}

$errorMessage = '';

$name = $plan['name'];
$durationMonths = (int) $plan['duration_months'];
$price = (float) $plan['price'];
$discountPercentage = (float) $plan['discount_percentage'];
$finalPrice = (float) $plan['final_price'];
$isActive = (int) $plan['is_active'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
        $errorMessage = 'Invalid CSRF token.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $durationMonths = max(1, (int) ($_POST['duration_months'] ?? 1));
        $price = (float) ($_POST['price'] ?? 0);
        $discountPercentage = (float) ($_POST['discount_percentage'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '') {
            $errorMessage = 'Plan name is required.';
        } elseif ($price <= 0) {
            $errorMessage = 'Price must be greater than 0.';
        } elseif ($discountPercentage < 0 || $discountPercentage > 100) {
            $errorMessage = 'Discount must be between 0 and 100.';
        } else {
            $finalPrice = round($price - ($price * $discountPercentage / 100), 2);
            if (updatePlan($id, $name, $durationMonths, $price, $discountPercentage, $finalPrice, $isActive)) {
                flashMessage('success', 'Plan "' . htmlspecialchars($name) . '" updated successfully.');
                header('Location: plans.php');
                exit;
            } else {
                $errorMessage = 'Failed to update plan.';
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
    <title>Nova News - Edit Plan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-slate-100">

    <?php include __DIR__ . '/../includes/admin-sidebar.php'; ?>

    <div class="ml-64 flex flex-col h-screen">

        <?php $pageTitle = 'Edit Plan'; include __DIR__ . '/../includes/admin-topbar.php'; ?>

        <div class="flex-1 overflow-y-auto p-6 space-y-6">

            <?php if ($errorMessage): ?>
                <div class="mb-6 px-5 py-4 rounded-xl shadow-sm text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                    <i class="fa-solid fa-exclamation-circle mr-2"></i> <?= htmlspecialchars($errorMessage) ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/60 max-w-2xl">

                <div class="border-b p-5 flex items-center gap-3">
                    <i class="fa-solid fa-gem text-blue-600 text-xl"></i>
                    <h3 class="text-xl font-bold"><?= htmlspecialchars($plan['name']) ?></h3>
                </div>

                <form method="post" action="" class="px-3 py-2 text-xs space-y-5">
                    <?= csrfField() ?>

                    <div>
                        <label for="name" class="block text-xs font-semibold text-slate-900 mb-1">Name <span class="text-red-600">*</span></label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required class="w-full px-3 py-1.5 text-xs.5 border border-slate-200 rounded-xl border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                    </div>

                    <div class="grid md:grid-cols-2 gap-5">
                        <div>
                            <label for="duration_months" class="block text-xs font-semibold text-slate-900 mb-1">Duration (months) <span class="text-red-600">*</span></label>
                            <input type="number" id="duration_months" name="duration_months" value="<?= $durationMonths ?>" min="1" required class="w-full px-3 py-1.5 text-xs.5 border border-slate-200 rounded-xl border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                        </div>
                        <div>
                            <label for="price" class="block text-xs font-semibold text-slate-900 mb-1">Price (MMK) <span class="text-red-600">*</span></label>
                            <input type="number" id="price" name="price" value="<?= number_format($price, 2) ?>" step="0.01" min="0.01" required oninput="calcFinal()" class="w-full px-3 py-1.5 text-xs.5 border border-slate-200 rounded-xl border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-5">
                        <div>
                            <label for="discount_percentage" class="block text-xs font-semibold text-slate-900 mb-1">Discount (%)</label>
                            <input type="number" id="discount_percentage" name="discount_percentage" value="<?= number_format($discountPercentage, 2) ?>" step="0.01" min="0" max="100" oninput="calcFinal()" class="w-full px-3 py-1.5 text-xs.5 border border-slate-200 rounded-xl border-slate-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all outline-none">
                            <p class="text-xs text-slate-500 mt-1">Set to 0 for no discount.</p>
                        </div>
                        <div>
                            <label for="final_price_display" class="block text-xs font-semibold text-slate-900 mb-1">Final Price (MMK)</label>
                            <input type="text" id="final_price_display" readonly class="w-full px-3 py-1.5 text-xs.5 border border-slate-200 bg-slate-50/50 rounded-lg text-slate-500 outline-none cursor-default">
                            <input type="hidden" id="final_price" name="final_price" value="<?= number_format($finalPrice, 2) ?>">
                            <p class="text-xs text-slate-500 mt-1">Auto-calculated from price and discount.</p>
                        </div>
                    </div>

                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" <?= $isActive ? 'checked' : '' ?> class="w-4 h-4 text-blue-600 border-slate-200 rounded focus:ring-blue-500">
                            <span class="text-xs font-semibold text-slate-900">Active</span>
                        </label>
                        <p class="text-xs text-slate-500 mt-1">Only active plans are shown to users.</p>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <a href="plans.php" class="px-6 py-2.5 border border-slate-200 text-slate-900 rounded-lg font-semibold hover:bg-slate-50/50 transition-colors transition"><i class="fa-solid fa-arrow-left mr-1"></i> Back to Plans</a>
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                            <i class="fa-solid fa-save mr-1"></i> Update Plan
                        </button>
                    </div>
                </form>

            </div>

        </div>

    </div>

<script>
function calcFinal() {
    const price = parseFloat(document.getElementById('price').value) || 0;
    const discount = parseFloat(document.getElementById('discount_percentage').value) || 0;
    const final = price - (price * discount / 100);
    document.getElementById('final_price_display').value = final.toLocaleString('en-US', {maximumFractionDigits: 0}) + ' MMK';
    document.getElementById('final_price').value = final.toFixed(0);
}
calcFinal();
</script>

</body>
</html>
