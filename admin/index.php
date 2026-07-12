<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/subscription.php';
require_once __DIR__ . '/../includes/notifications.php';
requireAdmin();

$notifCounts = getNotificationCounts();
$totalNotifs = array_sum($notifCounts);

$displayName = trim($_SESSION['username'] ?? 'Admin');
$displayInitial = strtoupper(substr($displayName, 0, 1));
$displayRole = (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'Administrator' : 'Member';

$totalUsers = 0;
$totalPosts = 0;
$publishedPosts = 0;
$premiumRegistrationCount = 0;

$subscriptionStats = getSubscriptionStats();
$totalRevenue = $subscriptionStats['total_revenue'];

$db = getDB();

$userResult = $db->query('SELECT COUNT(*) AS total FROM users');
if ($userResult) {
    $userRow = $userResult->fetch_assoc();
    $totalUsers = isset($userRow['total']) ? (int) $userRow['total'] : 0;
}

$postResult = $db->query('SELECT COUNT(*) AS total, SUM(status = "published") AS published FROM posts');
if ($postResult) {
    $postRow = $postResult->fetch_assoc();
    $totalPosts = isset($postRow['total']) ? (int) $postRow['total'] : 0;
    $publishedPosts = isset($postRow['published']) ? (int) $postRow['published'] : 0;
}

$premiumResult = $db->query('SELECT COUNT(*) AS total FROM user_subscriptions WHERE status = "active" AND payment_status = "paid"');
if ($premiumResult) {
    $premiumRow = $premiumResult->fetch_assoc();
    $premiumRegistrationCount = isset($premiumRow['total']) ? (int) $premiumRow['total'] : 0;
}

$latestPosts = [];
$postsResult = $db->query('SELECT title, post_type, status FROM posts ORDER BY created_at DESC LIMIT 5');
if ($postsResult) {
    while ($row = $postsResult->fetch_assoc()) {
        $latestPosts[] = $row;
    }
}

$planStats = [];
$planResult = $db->query("
    SELECT sp.name, sp.duration_months, COUNT(us.id) AS subscriber_count
    FROM subscription_plans sp
    LEFT JOIN user_subscriptions us ON us.plan_id = sp.id AND us.status = 'active' AND us.payment_status = 'paid'
    GROUP BY sp.id, sp.name, sp.duration_months
    ORDER BY sp.duration_months ASC
");
$maxPlanCount = 0;
if ($planResult) {
    while ($row = $planResult->fetch_assoc()) {
        $planStats[] = $row;
        if ((int)$row['subscriber_count'] > $maxPlanCount) {
            $maxPlanCount = (int)$row['subscriber_count'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova News Admin Dashboard</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>

<body class="bg-gray-100">

<!-- Sidebar -->
    <aside class="fixed left-0 top-0 h-screen overflow-y-auto z-50 w-72 bg-slate-900 text-white flex flex-col">

        <div class="h-16 flex items-center px-6 border-b border-slate-700">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center text-2xl font-bold text-white">
                    <?= htmlspecialchars($displayInitial) ?>
                </div>

                <div>
                    <h3 class="font-semibold"><?= htmlspecialchars($displayName) ?></h3>
                    <p class="text-sm text-green-400">
                        ● <?= htmlspecialchars($displayRole) ?>
                    </p>
                </div>
            </div>
        </div>

        

        <nav class="mt-6 flex-1">

            <a href="#" class="flex items-center px-6 py-4 bg-blue-600">
                <i class="fa-solid fa-house mr-4"></i>
                Dashboard
            </a>

            <a href="posts.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-newspaper mr-4"></i>
                Posts
            </a>

            <a href="categories.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-folder mr-4"></i>
                Categories
            </a>

            <a href="users.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-users mr-4"></i>
                Users
            </a>

            <a href="plans.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-gem mr-4"></i>
                Subscription Plans
            </a>

            <a href="user-subscriptions.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-file-contract mr-4"></i> User Subscriptions</a>

            <a href="payments.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-credit-card mr-4"></i>
                Payments
            </a>

            <a href="payment-services.php" class="flex items-center px-6 py-4 hover:bg-slate-800">
                <i class="fa-solid fa-money-bill-transfer mr-4"></i>
                Payment Services
            </a>

        </nav>

        <a href="/Nova_News/public/signin.php" class="flex items-center px-6 py-4 hover:bg-red-600">
                <i class="fa-solid fa-right-from-bracket mr-4"></i>
                Logout
        </a>

    </aside>


<!-- Main -->
    <div class="ml-72 flex flex-col h-screen">

    <!-- Header -->
        <header class="bg-white shadow h-16 flex justify-between items-center px-8 sticky top-0 left-0 right-0 z-30">

            <h2 class="text-3xl font-bold">
                Dashboard
            </h2>

            <div class="flex items-center gap-6">

                <button class="relative">
                    <i class="fa-regular fa-bell text-xl"></i>
                    <?php if ($totalNotifs > 0): ?>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center"><?= $totalNotifs > 9 ? '9+' : $totalNotifs ?></span>
                    <?php endif; ?>
                </button>

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-sm font-bold text-white">
                        <?= htmlspecialchars($displayInitial) ?>
                    </div>

                    <span class="font-semibold">
                        <?= htmlspecialchars($displayName) ?>
                    </span>
                </div>

            </div>

        </header>

        <div class="flex-1 overflow-y-auto p-8 space-y-8">

        <!-- Cards -->
            <div class="grid lg:grid-cols-4 md:grid-cols-2 gap-6">
                
            <!-- total users -->
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-gray-500">
                                Total Users
                            </p>

                            <h2 class="text-4xl font-bold mt-3">
                                <?= number_format($totalUsers) ?>
                            </h2>
                        </div>

                        <div
                            class="w-16 h-16 rounded-xl bg-blue-100 flex items-center justify-center">
                            <i class="fa-solid fa-users text-blue-600 text-3xl"></i>
                        </div>
                    </div>
                </div>

            <!-- total posts -->
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-gray-500">
                                Total Posts
                            </p>

                            <h2 class="text-4xl font-bold mt-3">
                                <?= number_format($totalPosts) ?>
                            </h2>

                            <p class="text-green-500 mt-3">
                                <?= number_format($publishedPosts) ?> Published
                            </p>
                        </div>

                        <div
                            class="w-16 h-16 rounded-xl bg-green-100 flex items-center justify-center">
                            <i class="fa-solid fa-file-lines text-green-600 text-3xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-gray-500">
                                Premium Users
                            </p>

                            <h2 class="text-4xl font-bold mt-3">
                                <?= number_format($premiumRegistrationCount) ?>
                            </h2>

                            <p class="text-green-500 mt-3">
                                <?= number_format($premiumRegistrationCount) ?> Active Subscribers
                            </p>
                        </div>

                        <div
                            class="w-16 h-16 rounded-xl bg-purple-100 flex items-center justify-center">
                            <i class="fa-solid fa-gem text-purple-600 text-3xl"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Revenue -->
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-gray-500">
                                Revenue
                            </p>

                            <h2 class="text-4xl font-bold mt-3">
                                $<?= number_format($totalRevenue, 2) ?>
                            </h2>

                            <p class="text-green-500 mt-3">
                                +18%
                            </p>
                        </div>

                        <div
                            class="w-16 h-16 rounded-xl bg-yellow-100 flex items-center justify-center">
                            <i class="fa-solid fa-dollar-sign text-yellow-600 text-3xl"></i>
                        </div>
                    </div>
                </div>

            </div>

        <!-- Tables -->
            <div class="grid lg:grid-cols-2 gap-8">

                <div class="bg-white rounded-xl shadow">

                    <div class="border-b p-5">
                        <h3 class="text-xl font-bold">
                            Latest Posts
                        </h3>
                    </div>

                    <table class="w-full">

                        <tbody>

                            <?php foreach ($latestPosts as $post): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-5"><?= htmlspecialchars($post['title']) ?></td>
                                <td>
                                    <?php if ($post['post_type'] === 'premium'): ?>
                                    <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm">
                                        Premium
                                    </span>
                                    <?php else: ?>
                                    <span class="bg-gray-100 px-3 py-1 rounded-full text-sm">
                                        Free
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($post['status'] === 'published'): ?>
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm">
                                        Published
                                    </span>
                                    <?php else: ?>
                                    <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-sm">
                                        Draft
                                    </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

                <div class="bg-white rounded-xl shadow p-6 h-[350px] overflow-y-auto">

                    <h3 class="text-xl font-bold mb-6">
                        Subscription Overview
                    </h3>

                    <div class="space-y-5">
                        <?php $barColors = ['bg-blue-600', 'bg-green-500', 'bg-yellow-500', 'bg-purple-500', 'bg-red-500', 'bg-indigo-500']; $ci = 0; ?>
                        <?php foreach ($planStats as $plan):
                            $count = (int)$plan['subscriber_count'];
                            $pct = $maxPlanCount > 0 ? round(($count / $maxPlanCount) * 100) : 0;
                            $color = $barColors[$ci % count($barColors)]; $ci++;
                        ?>
                        <div class="flex justify-between">
                            <span><?= htmlspecialchars($plan['name']) ?></span>
                            <span class="font-bold"><?= $count ?></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div class="<?= $color ?> h-3 rounded-full" style="width: <?= $pct ?>%"></div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                </div>

            </div>

        </div>

    </div>

</body>
</html>