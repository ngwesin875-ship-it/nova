<?php
$currentPage = basename($_SERVER['SCRIPT_NAME']);
function getSidebarClass($page, $currentPage) {
    if ($page === $currentPage) {
        return 'flex items-center px-3 py-2 mx-3 mb-1 bg-gradient-to-r from-blue-600/30 to-blue-900/10 text-blue-400 rounded-lg border-l-4 border-blue-500 font-semibold shadow-md shadow-blue-900/20 transition-all text-sm';
    }
    return 'flex items-center px-3 py-2 mx-3 mb-1 text-slate-400 hover:bg-slate-800 hover:text-white rounded-lg border-l-4 border-transparent font-medium transition-all group text-sm';
}
?>
<aside class="fixed left-0 top-0 h-screen overflow-y-auto z-50 w-64 bg-slate-950 text-slate-300 flex flex-col shadow-2xl">
    
    <div class="h-14 flex items-center px-5 border-b border-slate-800/60 shrink-0">
        <a href="index.php" class="flex items-center gap-3 text-lg font-bold text-white tracking-wide">
            <div class="w-7 h-7 rounded-md bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-md">
                <i class="fa-solid fa-newspaper text-xs text-white"></i>
            </div>
            Nova<span class="text-blue-500">News</span>
        </a>
    </div>

    <nav class="mt-4 flex-1 flex flex-col gap-0.5">
        
        <p class="px-5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Main Menu</p>
        
        <a href="index.php" class="<?= getSidebarClass('index.php', $currentPage) ?>">
            <i class="fa-solid fa-house w-5 text-center mr-2 text-base"></i>
            Dashboard
        </a>
        <a href="posts.php" class="<?= getSidebarClass('posts.php', $currentPage) ?>">
            <i class="fa-solid fa-newspaper w-5 text-center mr-2 text-base"></i>
            Posts
        </a>
        <a href="categories.php" class="<?= getSidebarClass('categories.php', $currentPage) ?>">
            <i class="fa-solid fa-folder w-5 text-center mr-2 text-base"></i>
            Categories
        </a>
        <a href="users.php" class="<?= getSidebarClass('users.php', $currentPage) ?>">
            <i class="fa-solid fa-users w-5 text-center mr-2 text-base"></i>
            Users
        </a>

        <p class="px-5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider mt-4 mb-1">Engagement</p>
        
        <a href="comments.php" class="<?= getSidebarClass('comments.php', $currentPage) ?>">
            <i class="fa-solid fa-comments w-5 text-center mr-2 text-base"></i>
            Comments
        </a>
        <a href="likes.php" class="<?= getSidebarClass('likes.php', $currentPage) ?>">
            <i class="fa-solid fa-thumbs-up w-5 text-center mr-2 text-base"></i>
            Likes &amp; Dislikes
        </a>

        <p class="px-5 text-[11px] font-semibold text-slate-400 uppercase tracking-wider mt-4 mb-1">Finance</p>
        
        <a href="plans.php" class="<?= getSidebarClass('plans.php', $currentPage) ?>">
            <i class="fa-solid fa-gem w-5 text-center mr-2 text-base"></i>
            Subscription Plans
        </a>
        <a href="user-subscriptions.php" class="<?= getSidebarClass('user-subscriptions.php', $currentPage) ?>">
            <i class="fa-solid fa-file-contract w-5 text-center mr-2 text-base"></i>
            User Subscriptions
        </a>
        <a href="payments.php" class="<?= getSidebarClass('payments.php', $currentPage) ?>">
            <i class="fa-solid fa-credit-card w-5 text-center mr-2 text-base"></i>
            Payments
        </a>
        <a href="payment-services.php" class="<?= getSidebarClass('payment-services.php', $currentPage) ?>">
            <i class="fa-solid fa-money-bill-transfer w-5 text-center mr-2 text-base"></i>
            Payment Services
        </a>
    </nav>
    
</aside>
