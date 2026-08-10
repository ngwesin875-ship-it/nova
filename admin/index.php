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

<body class="bg-slate-100">

<?php include __DIR__ . '/../includes/admin-sidebar.php'; ?>


<!-- Main -->
    <div class="ml-64 flex flex-col h-screen">

    <?php $pageTitle = 'Dashboard'; include __DIR__ . '/../includes/admin-topbar.php'; ?>

        <div class="flex-1 overflow-y-auto p-6 space-y-6">

        <!-- Cards -->
            <div class="grid lg:grid-cols-4 md:grid-cols-2 gap-4">
                
            <!-- total users -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                Total Users
                            </p>

                            <h2 class="text-2xl font-bold mt-2 text-slate-900 tracking-tight">
                                <?= number_format($totalUsers) ?>
                            </h2>
                        </div>

                        <div
                            class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-100 to-blue-50 flex items-center justify-center shadow-inner">
                            <i class="fa-solid fa-users text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>

            <!-- total posts -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                Total Posts
                            </p>

                            <h2 class="text-2xl font-bold mt-2 text-slate-900 tracking-tight">
                                <?= number_format($totalPosts) ?>
                            </h2>

                            <p class="text-emerald-600 text-xs font-medium mt-2 bg-emerald-50 inline-block px-2.5 py-0.5 rounded-full">
                                <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1 animate-pulse"></span>
                                <?= number_format($publishedPosts) ?> Published
                            </p>
                        </div>

                        <div
                            class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-100 to-emerald-50 flex items-center justify-center shadow-inner">
                            <i class="fa-solid fa-file-lines text-emerald-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                Premium Users
                            </p>

                            <h2 class="text-2xl font-bold mt-2 text-slate-900 tracking-tight">
                                <?= number_format($premiumRegistrationCount) ?>
                            </h2>

                            <p class="text-emerald-600 text-xs font-medium mt-2 bg-emerald-50 inline-block px-2.5 py-0.5 rounded-full">
                                <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1 animate-pulse"></span>
                                <?= number_format($premiumRegistrationCount) ?> Active Subs
                            </p>
                        </div>

                        <div
                            class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-100 to-purple-50 flex items-center justify-center shadow-inner">
                            <i class="fa-solid fa-gem text-purple-600 text-xl"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Revenue -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-4 hover:-translate-y-1 hover:shadow-md transition-all duration-300">
                    <div class="flex justify-between">
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                Revenue
                            </p>

                            <h2 class="text-2xl font-bold mt-2 text-slate-900 tracking-tight">
                                <?= number_format($totalRevenue, 0) ?> <span class="text-base font-medium text-slate-500">MMK</span>
                            </h2>

                            <p class="text-emerald-600 text-xs font-medium mt-2 bg-emerald-50 inline-block px-2.5 py-0.5 rounded-full">
                                <i class="fa-solid fa-arrow-trend-up mr-1"></i>
                                +18%
                            </p>
                        </div>

                        <div
                            class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-100 to-amber-50 flex items-center justify-center shadow-inner">
                            <i class="fa-solid fa-coins text-amber-600 text-xl"></i>
                        </div>
                    </div>
                </div>

            </div>

        <!-- Tables -->
            <div class="grid lg:grid-cols-2 gap-6">

                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
                    <div class="border-b border-slate-200 p-4 bg-slate-50/50">
                        <h3 class="text-base font-bold text-slate-900">
                            Latest Posts
                        </h3>
                    </div>

                    <table class="w-full">

                        <tbody>

                            <?php foreach ($latestPosts as $post): ?>
                            <tr class="border-b border-slate-200 hover:bg-slate-50/50 transition-colors">
                                <td class="p-4 font-medium text-slate-900 text-sm"><?= htmlspecialchars($post['title']) ?></td>
                                <td>
                                    <?php if ($post['post_type'] === 'premium'): ?>
                                    <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-full text-xs font-semibold border border-blue-100">
                                        Premium
                                    </span>
                                    <?php else: ?>
                                    <span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-xs font-semibold border border-slate-200">
                                        Free
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($post['status'] === 'published'): ?>
                                    <span class="bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full text-xs font-semibold border border-emerald-100 flex items-center w-fit">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                                        Published
                                    </span>
                                    <?php else: ?>
                                    <span class="bg-amber-50 text-amber-600 px-3 py-1 rounded-full text-xs font-semibold border border-amber-100 flex items-center w-fit">
                                        Draft
                                    </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 p-5 h-[300px] overflow-y-auto">

                    <h3 class="text-base font-bold text-slate-900 mb-4">
                        Subscription Overview
                    </h3>

                    <div class="space-y-5">
                        <?php $barColors = ['bg-blue-600', 'bg-green-500', 'bg-yellow-500', 'bg-purple-500', 'bg-red-500', 'bg-indigo-500']; $ci = 0; ?>
                        <?php foreach ($planStats as $plan):
                            $count = (int)$plan['subscriber_count'];
                            $pct = $maxPlanCount > 0 ? round(($count / $maxPlanCount) * 100) : 0;
                            $color = $barColors[$ci % count($barColors)]; $ci++;
                        ?>
                        <div class="flex justify-between items-center mb-1.5">
                            <span class="text-xs font-medium text-slate-600"><?= htmlspecialchars($plan['name']) ?></span>
                            <span class="text-sm font-bold text-slate-900"><?= $count ?></span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                            <div class="<?= $color ?> h-full rounded-full transition-all duration-1000 ease-out" style="width: <?= $pct ?>%"></div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                </div>

            </div>

        </div>

    </div>

</body>
</html>
