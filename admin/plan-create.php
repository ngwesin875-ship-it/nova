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

$errorMessage = '';

$name = '';
$durationMonths = 1;
$price = '';
$discountPercentage = '0';
$isActive = 1;

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
            $result = createPlan($name, $durationMonths, $price, $discountPercentage, $finalPrice, $isActive);
            if ($result !== false) {
                flashMessage('success', 'Plan "' . htmlspecialchars($name) . '" created successfully.');
                header('Location: plans.php');
                exit;
            } else {
                $errorMessage = 'Failed to create plan.';
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
    <title>Nova News - Create Plan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="bg-gray-100">

    <aside class="fixed left-0 top-0 h-screen overflow-y-auto z-50 w-72 bg-slate-900 text-white">

        <div class="h-16 flex items-center px-6 border-b border-slate-700">
            <i class="fa-solid fa-newspaper text-2xl mr-3 text-blue-500"></i>
            <h1 class="text-2xl font-bold">NOVA NEWS</h1>
        </div>

        <div class="p-6 border-b border-slate-700">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-blue-600 flex items-center justify-center text-2xl font-bold text-white"><?= htmlspecialchars($displayInitial) ?></div>
                <div>
                    <h3 class="font-semibold"><?= htmlspecialchars($displayName) ?></h3>
                    <p class="text-sm text-green-400">● <?= htmlspecialchars($displayRole) ?></p>
                </div>
            </div>
        </div>

        <nav class="mt-6">
            <a href="index.php" class="flex items-center px-6 py-4 hover:bg-slate-800"><i class="fa-solid fa-house mr-4"></i> Dashboard</a>
            <a href="posts.php" class="flex items-center px-6 py-4 hover:bg-slate-800"><i class="fa-solid fa-newspaper mr-4"></i> Posts</a>
            <a href="categories.php" class="flex items-center px-6 py-4 hover:bg-slate-800"><i class="fa-solid fa-folder mr-4"></i> Categories</a>
            <a href="users.php" class="flex items-center px-6 py-4 hover:bg-slate-800"><i class="fa-solid fa-users mr-4"></i> Users</a>
            <a href="plans.php" class="flex items-center px-6 py-4 bg-blue-600"><i class="fa-solid fa-gem mr-4"></i> Subscription Plans</a>
            <a href="payments.php" class="flex items-center px-6 py-4 hover:bg-slate-800"><i class="fa-solid fa-credit-card mr-4"></i> Payments</a>

            <a href="#" class="flex items-center px-6 py-4 hover:bg-slate-800"><i class="fa-solid fa-gear mr-4"></i> Settings</a>
            <a href="/Nova_News/public/signin.php" class="flex items-center px-6 py-4 hover:bg-red-600"><i class="fa-solid fa-right-from-bracket mr-4"></i> Logout</a>
        </nav>

    </aside>

    <div class="ml-72 flex flex-col h-screen">

        <header class="bg-white shadow h-16 flex justify-between items-center px-8 shrink-0">
            <h2 class="text-3xl font-bold">Create Plan</h2>
            <div class="flex items-center gap-6">
                <?php include __DIR__ . '/../includes/admin-header.php'; ?>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-sm font-bold text-white"><?= htmlspecialchars($displayInitial) ?></div>
                    <span class="font-semibold"><?= htmlspecialchars($displayName) ?></span>
                </div>
            </div>
        </header>

        <div class="flex-1 overflow-y-auto p-8 space-y-8">

            <?php if ($errorMessage): ?>
                <div class="mb-6 px-5 py-4 rounded-xl shadow-sm text-sm font-medium bg-red-100 text-red-800 border border-red-200">
                    <i class="fa-solid fa-exclamation-circle mr-2"></i> <?= htmlspecialchars($errorMessage) ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow max-w-2xl">

                <div class="border-b p-5">
                    <h3 class="text-xl font-bold">Plan Details</h3>
                </div>

                <form method="post" action="" class="p-5 space-y-5">
                    <?= csrfField() ?>

                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Name <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>

                    <div class="grid md:grid-cols-2 gap-5">
                        <div>
                            <label for="duration_months" class="block text-sm font-semibold text-gray-700 mb-1">Duration (months) <span class="text-red-500">*</span></label>
                            <input type="number" id="duration_months" name="duration_months" value="<?= (int) $durationMonths ?>" min="1" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                        <div>
                            <label for="price" class="block text-sm font-semibold text-gray-700 mb-1">Price (MMK) <span class="text-red-500">*</span></label>
                            <input type="number" id="price" name="price" value="<?= htmlspecialchars($price) ?>" step="0.01" min="0.01" required oninput="calcFinal()" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-5">
                        <div>
                            <label for="discount_percentage" class="block text-sm font-semibold text-gray-700 mb-1">Discount (%)</label>
                            <input type="number" id="discount_percentage" name="discount_percentage" value="<?= htmlspecialchars($discountPercentage) ?>" step="0.01" min="0" max="100" oninput="calcFinal()" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                            <p class="text-xs text-gray-500 mt-1">Set to 0 for no discount.</p>
                        </div>
                        <div>
                            <label for="final_price_display" class="block text-sm font-semibold text-gray-700 mb-1">Final Price (MMK)</label>
                            <input type="text" id="final_price_display" readonly value="0 MMK" class="w-full px-4 py-2.5 border border-gray-200 bg-gray-50 rounded-lg text-gray-500 outline-none cursor-default">
                            <input type="hidden" id="final_price" name="final_price" value="0">
                            <p class="text-xs text-gray-500 mt-1">Auto-calculated from price and discount.</p>
                        </div>
                    </div>

                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" <?= $isActive ? 'checked' : '' ?> class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="text-sm font-semibold text-gray-700">Active</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1">Only active plans are shown to users.</p>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                            <i class="fa-solid fa-save mr-1"></i> Create Plan
                        </button>
                        <a href="plans.php" class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition">Cancel</a>
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
