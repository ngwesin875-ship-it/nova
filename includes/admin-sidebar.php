<?php
$currentPage = basename($_SERVER['SCRIPT_NAME']);
function getSidebarClass($page, $currentPage) {
    if ($page === $currentPage) {
        return 'flex items-center px-4 py-3 mx-4 mb-1.5 bg-gradient-to-r from-blue-600/30 to-blue-900/10 text-blue-400 rounded-xl border-l-4 border-blue-500 font-semibold shadow-lg shadow-blue-900/20 transition-all';
    }
    return 'flex items-center px-4 py-3 mx-4 mb-1.5 text-slate-400 hover:bg-slate-800 hover:text-white rounded-xl border-l-4 border-transparent font-medium transition-all group';
}
?>
<aside class="fixed left-0 top-0 h-screen overflow-y-auto z-50 w-72 bg-slate-900 text-slate-300 flex flex-col shadow-2xl">
    
    <div class="h-16 flex items-center px-8 border-b border-slate-800/60 shrink-0">
        <a href="index.php" class="flex items-center gap-3 text-xl font-bold text-white tracking-wide">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg">
                <i class="fa-solid fa-newspaper text-sm text-white"></i>
            </div>
            Nova<span class="text-blue-500">News</span>
        </a>
    </div>

    <nav class="mt-8 flex-1 flex flex-col gap-0.5">
        
        <p class="px-8 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Main Menu</p>
        
        <a href="index.php" class="<?= getSidebarClass('index.php', $currentPage) ?>">
            <i class="fa-solid fa-house w-6 text-center mr-3 text-lg"></i>
            Dashboard
        </a>
        <a href="posts.php" class="<?= getSidebarClass('posts.php', $currentPage) ?>">
            <i class="fa-solid fa-newspaper w-6 text-center mr-3 text-lg"></i>
            Posts
        </a>
        <a href="categories.php" class="<?= getSidebarClass('categories.php', $currentPage) ?>">
            <i class="fa-solid fa-folder w-6 text-center mr-3 text-lg"></i>
            Categories
        </a>
        <a href="users.php" class="<?= getSidebarClass('users.php', $currentPage) ?>">
            <i class="fa-solid fa-users w-6 text-center mr-3 text-lg"></i>
            Users
        </a>

        <p class="px-8 text-xs font-semibold text-slate-500 uppercase tracking-wider mt-6 mb-2">Engagement</p>
        
        <a href="comments.php" class="<?= getSidebarClass('comments.php', $currentPage) ?>">
            <i class="fa-solid fa-comments w-6 text-center mr-3 text-lg"></i>
            Comments
        </a>
        <a href="likes.php" class="<?= getSidebarClass('likes.php', $currentPage) ?>">
            <i class="fa-solid fa-thumbs-up w-6 text-center mr-3 text-lg"></i>
            Likes &amp; Dislikes
        </a>

        <p class="px-8 text-xs font-semibold text-slate-500 uppercase tracking-wider mt-6 mb-2">Finance</p>
        
        <a href="plans.php" class="<?= getSidebarClass('plans.php', $currentPage) ?>">
            <i class="fa-solid fa-gem w-6 text-center mr-3 text-lg"></i>
            Subscription Plans
        </a>
        <a href="user-subscriptions.php" class="<?= getSidebarClass('user-subscriptions.php', $currentPage) ?>">
            <i class="fa-solid fa-file-contract w-6 text-center mr-3 text-lg"></i>
            User Subscriptions
        </a>
        <a href="payments.php" class="<?= getSidebarClass('payments.php', $currentPage) ?>">
            <i class="fa-solid fa-credit-card w-6 text-center mr-3 text-lg"></i>
            Payments
        </a>
        <a href="payment-services.php" class="<?= getSidebarClass('payment-services.php', $currentPage) ?>">
            <i class="fa-solid fa-money-bill-transfer w-6 text-center mr-3 text-lg"></i>
            Payment Services
        </a>
    </nav>
    
</aside>
