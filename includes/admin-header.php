<?php
require_once __DIR__ . '/notifications.php';

$unreadNotifs = getUnreadNotifications(10);
$unreadCount = count($unreadNotifs);
$pendingSubCount = getPendingSubscriptionCount();
$adminTotalNotifs = $unreadCount + $pendingSubCount;
?>

<div class="relative" id="notif-dropdown-wrapper">
    <button id="notif-bell-btn" class="relative p-2 hover:bg-gray-100 rounded-lg transition" onclick="toggleNotifDropdown()">
        <i class="fa-regular fa-bell text-xl text-gray-600"></i>
        <?php if ($adminTotalNotifs > 0): ?>
            <span class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center">
                <?= $adminTotalNotifs > 9 ? '9+' : $adminTotalNotifs ?>
            </span>
        <?php endif; ?>
    </button>

    <div id="notif-dropdown" class="hidden absolute right-0 top-full mt-2 w-80 bg-white rounded-xl shadow-2xl border border-gray-200 z-50 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <h4 class="text-sm font-bold text-gray-800">Notifications</h4>
            <?php if ($adminTotalNotifs > 0): ?>
                <button onclick="markAllRead()" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Mark all read</button>
            <?php endif; ?>
        </div>
        <div class="max-h-80 overflow-y-auto" id="notif-list">
            <?php if ($pendingSubCount > 0): ?>
                <a href="user-subscriptions.php" class="flex items-start gap-3 px-4 py-3 hover:bg-amber-50 border-b border-gray-50 transition">
                    <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fa-solid fa-clock text-amber-600 text-xs"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-800"><?= $pendingSubCount ?> pending subscription<?= $pendingSubCount > 1 ? 's' : '' ?></p>
                        <p class="text-xs text-gray-500 mt-0.5">Waiting for your approval</p>
                    </div>
                </a>
            <?php endif; ?>

            <?php if (empty($unreadNotifs) && $pendingSubCount === 0): ?>
                <div class="px-4 py-8 text-center">
                    <i class="fa-regular fa-bell-slash text-3xl text-gray-300 mb-2 block"></i>
                    <p class="text-sm text-gray-400">No new notifications</p>
                </div>
            <?php else: ?>
                <?php foreach ($unreadNotifs as $notif): ?>
                    <?php
                    $iconMap = [
                        'new_subscription' => ['fa-solid fa-user-plus', 'bg-blue-100', 'text-blue-600'],
                        'payment_received' => ['fa-solid fa-money-bill', 'bg-green-100', 'text-green-600'],
                        'subscription_approved' => ['fa-solid fa-check-circle', 'bg-green-100', 'text-green-600'],
                        'subscription_rejected' => ['fa-solid fa-times-circle', 'bg-red-100', 'text-red-600'],
                    ];
                    $iconData = $iconMap[$notif['type']] ?? ['fa-solid fa-bell', 'bg-gray-100', 'text-gray-600'];
                    $link = $notif['reference_type'] === 'user_subscriptions' ? 'user-subscriptions.php' : 'payments.php';
                    ?>
                    <a href="<?= $link ?>" onclick="markRead(<?= $notif['id'] ?>)" class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 border-b border-gray-50 transition">
                        <div class="w-8 h-8 rounded-full <?= $iconData[1] ?> flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="<?= $iconData[0] ?> <?= $iconData[2] ?> text-xs"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-gray-800 truncate"><?= htmlspecialchars($notif['title']) ?></p>
                            <p class="text-xs text-gray-500 mt-0.5 line-clamp-2"><?= htmlspecialchars($notif['message']) ?></p>
                            <p class="text-[10px] text-gray-400 mt-1"><?= date('M j, g:i A', strtotime($notif['created_at'])) ?></p>
                        </div>
                        <div class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0 mt-2"></div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="px-4 py-2.5 border-t border-gray-100 text-center">
            <a href="user-subscriptions.php" class="text-xs font-semibold text-blue-600 hover:text-blue-800">View All Subscriptions</a>
        </div>
    </div>
</div>

<script>
function toggleNotifDropdown() {
    const dropdown = document.getElementById('notif-dropdown');
    dropdown.classList.toggle('hidden');
}

document.addEventListener('click', function(e) {
    const wrapper = document.getElementById('notif-dropdown-wrapper');
    if (!wrapper.contains(e.target)) {
        document.getElementById('notif-dropdown').classList.add('hidden');
    }
});

function markRead(id) {
    fetch('/Nova_News/admin/notification-action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=mark_read&id=' + id
    });
}

function markAllRead() {
    fetch('/Nova_News/admin/notification-action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=mark_all_read'
    }).then(() => {
        document.querySelectorAll('#notif-list .bg-blue-500').forEach(el => el.classList.remove('bg-blue-500'));
        const badge = document.querySelector('#notif-bell-btn span');
        if (badge) badge.remove();
    });
}
</script>
