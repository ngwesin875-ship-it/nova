<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/posts.php';
require_once __DIR__ . '/lang.php';
$breakingNews = getBreakingPosts(5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova News Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="/Nova_News/includes/translations.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        body.theme-dark {
            background-color: #020617;
            color: #E2E8F0;
        }

        body.theme-dark header,
        body.theme-dark footer,
        body.theme-dark .bg-white,
        body.theme-dark .bg-slate-100,
        body.theme-dark .bg-slate-50 {
            background-color: #111827 !important;
        }

        body.theme-dark .border-slate-200,
        body.theme-dark .border-slate-100 {
            border-color: #334155 !important;
        }

        body.theme-dark .text-slate-900,
        body.theme-dark .text-slate-800,
        body.theme-dark .text-slate-700,
        body.theme-dark .text-slate-600,
        body.theme-dark .text-slate-500,
        body.theme-dark .text-slate-400,
        body.theme-dark .text-slate-300,
        body.theme-dark .text-slate-950 {
            color: #E2E8F0 !important;
        }

        body.theme-dark .text-slate-400 {
            color: #94A3B8 !important;
        }

        .text-theme-adaptive {
            color: #0F172A;
        }

        body.theme-dark .text-theme-adaptive {
            color: #FFFFFF;
        }

        body.theme-dark .bg-slate-900,
        body.theme-dark .bg-slate-950 {
            background-color: #0F172A !important;
        }

        body.theme-dark .hover\:bg-slate-50:hover {
            background-color: #1E293B !important;
        }

        body.theme-dark .hover\:bg-slate-100:hover {
            background-color: #1E293B !important;
        }

        body.theme-dark .divide-slate-200 > :not([hidden]) ~ :not([hidden]) {
            border-color: #334155 !important;
        }

        body.theme-dark .from-amber-50 {
            --tw-gradient-from: #422006 !important;
            --tw-gradient-to: #0F172A !important;
        }

        body.theme-dark .hover\:border-slate-300:hover {
            border-color: #475569 !important;
        }

        body.theme-dark .text-blue-500 {
            color: #60A5FA !important;
        }

        body.theme-dark .hover\:text-red-600:hover {
            color: #FCA5A5 !important;
        }

        body.theme-dark .text-amber-600 {
            color: #FCD34D !important;
        }

        body.theme-dark .hover\:text-amber-700:hover {
            color: #FCD34D !important;
        }

        body.theme-dark .hover\:text-slate-900:hover {
            color: #F1F5F9 !important;
        }

        body.theme-dark .text-green-600 {
            color: #4ADE80 !important;
        }

        body.theme-dark .text-yellow-600 {
            color: #FACC15 !important;
        }

        body.theme-dark .text-red-600 {
            color: #F87171 !important;
        }

        body.theme-dark .text-gray-600 {
            color: #9CA3AF !important;
        }

        body.theme-dark .shadow-sm {
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.4) !important;
        }

        body.theme-dark .shadow-xl {
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.35) !important;
        }

        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0; }
        }
        .blink-text {
            animation: blink 1s step-end infinite;
        }

        @keyframes ticker {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .ticker-track {
            display: inline-flex;
            white-space: nowrap;
            animation: ticker 30s linear infinite;
        }
        .ticker-track:hover {
            animation-play-state: paused;
        }
    </style>
</head>
<body class="bg-[#F8FAFC] text-slate-800 antialiased theme-light">

    <!-- breaking news -->
    <div class="bg-red-600 text-white text-xs py-2 px-4 md:px-8 flex items-center justify-between overflow-hidden">
        <div class="flex items-center space-x-4 overflow-hidden min-w-0">
            <span class="bg-white text-red-600 text-[10px] uppercase font-bold px-2 py-0.5 rounded flex items-center gap-1 shrink-0">
                <i class="fa-solid fa-bolt"></i> <span class="blink-text">Breaking</span>
            </span>
            <div class="overflow-hidden min-w-0 text-white/80">
                <?php if (!empty($breakingNews)): ?>
                    <div class="ticker-track">
                        <span class="inline-flex space-x-6">
                            <?php foreach ($breakingNews as $i => $bn): ?>
                                <a href="/Nova_News/user/article.php?slug=<?= urlencode($bn['slug']) ?>" class="text-white font-medium hover:underline whitespace-nowrap"><?= htmlspecialchars($bn['title']) ?></a>
                                <?php if ($i < count($breakingNews) - 1): ?><span class="mx-3">•</span><?php endif; ?>
                            <?php endforeach; ?>
                        </span>
                        <span class="inline-flex space-x-6">
                            <?php foreach ($breakingNews as $i => $bn): ?>
                                <a href="/Nova_News/user/article.php?slug=<?= urlencode($bn['slug']) ?>" class="text-white font-medium hover:underline whitespace-nowrap"><?= htmlspecialchars($bn['title']) ?></a>
                                <?php if ($i < count($breakingNews) - 1): ?><span class="mx-3">•</span><?php endif; ?>
                            <?php endforeach; ?>
                        </span>
                    </div>
                <?php else: ?>
                    <span class="text-white font-medium">Stay informed with Nova News</span>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <!-- header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50 px-4 md:px-8 py-4 flex items-center justify-between gap-8">
        <div class="flex items-center space-x-2">
            <div class="text-2xl font-black tracking-wider text-[#1E224F]">NOVA <span class="text-[#5B41FF]">NEWS</span></div>
            <div class="text-[10px] text-slate-400 hidden lg:block border-l border-slate-300 pl-2 self-end mb-1">Read Smart. Stay Ahead.</div>
        </div>
        
        <nav class="hidden xl:flex items-center space-x-6 text-sm font-semibold text-slate-600 mr-6">
            <a href="index.php" class="text-[#5B41FF] border-b-2 border-[#5B41FF] pb-1"><?= __('header.home') ?></a>
        </nav>

        <!-- Categories Dropdown -->
        <div class="relative group">
            <button class="px-3 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors flex items-center gap-1">
                <?= __('header.categories') ?>
                <svg class="w-4 h-4 transition-transform group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div class="absolute top-full left-0 mt-1 w-52 bg-white rounded-xl shadow-2xl border border-slate-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                <?php
                require_once __DIR__ . '/posts.php';
                $navCategories = getAllCategories();
                foreach ($navCategories as $cat): ?>
                    <a href="/Nova_News/user/category.php?slug=<?= urlencode($cat['slug']) ?>"
                        class="block px-4 py-2.5 text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-50 first:rounded-t-xl last:rounded-b-xl transition-colors">
                        <?= htmlspecialchars($cat['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- search bar -->
        <div class="flex items-center space-x-3">
            <form action="search.php" method="GET" class="hidden lg:flex items-center bg-slate-100 border border-slate-200 rounded-full overflow-hidden focus-within:ring-2 focus-within:ring-[#5B41FF]/30">
                <input name="q" type="search" placeholder="<?= __('header.search') ?>" class="min-w-[180px] bg-transparent px-3 py-1.5 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none" />
                <button type="submit" class="px-3 py-1.5 bg-[#5B41FF] text-white text-sm font-semibold hover:bg-[#4830DF] transition">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>
            
            <button id="theme-toggle" class="p-2 text-slate-500 hover:text-slate-800" aria-label="Toggle theme" title="Toggle theme">
                <i class="fa-solid fa-moon text-lg"></i>
            </button>
            <select id="lang-select" onchange="setLang(this.value)" class="p-1.5 text-sm font-medium text-slate-600 bg-slate-100 border border-slate-200 rounded-lg cursor-pointer focus:outline-none focus:ring-2 focus:ring-[#5B41FF]/30" aria-label="Select language">
                <option value="en" <?= getCurrentLang() === 'en' ? 'selected' : '' ?>>English</option>
                <option value="my" <?= getCurrentLang() === 'my' ? 'selected' : '' ?>>Myanmar</option>
            </select>
        </div>

         <div class="hidden md:flex items-center gap-3">
                    <?php include __DIR__ . '/theme-toggle.php'; ?>
                    <?php if (!isLoggedIn()): ?>
                        <a href="/Nova_News/public/Signin.php"
                            class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors rounded-lg border border-slate-300 hover:border-slate-400">
                            <?= __('header.signin') ?>
                        </a>
                        <a href="/Nova_News/public/register.php"
                            class="px-4 py-2 text-sm font-medium bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white rounded-lg transition-all shadow-lg hover:shadow-blue-500/25">
                            <?= __('header.get_started') ?>
                        </a>
                    <?php elseif (isAdmin()): ?>
                        <a href="/Nova_News/admin/index.php"
                            class="px-3 py-2 text-sm font-medium text-amber-600 hover:text-amber-700 bg-amber-500/10 hover:bg-amber-500/20 rounded-lg transition-colors flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Admin Panel
                        </a>
                        <!-- Avatar Dropdown -->
                        <div class="relative group">
                            <button class="flex items-center gap-2 p-1 rounded-full hover:bg-slate-100 transition-colors">
                                <div class="w-8 h-8 bg-gradient-to-br from-amber-400 to-orange-500 rounded-full flex items-center justify-center text-sm font-bold text-white">
                                    <?= strtoupper(substr(currentUserName() ?? 'A', 0, 1)) ?>
                                </div>
                            </button>
                            <div class="absolute top-full right-0 mt-1 w-44 bg-white rounded-xl shadow-2xl border border-slate-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                                <div class="px-4 py-3 border-b border-slate-200">
                                    <p class="text-sm font-semibold text-slate-900 truncate"><?= htmlspecialchars(currentUserName() ?? '') ?></p>
                                    <p class="text-xs text-amber-600">Administrator</p>
                                </div>
                                <a href="/Nova_News/public/logout.php" class="block px-4 py-2.5 text-sm text-red-500 hover:text-red-600 hover:bg-slate-50 rounded-b-xl transition-colors">Sign Out</a>
                            </div>
                        </div>
                    <?php else: ?>

                        <!-- Premium Badge -->
                        <?php
                        require_once __DIR__ . '/../includes/subscription.php';
                        $navSub = getActiveSubscription(currentUserId());
                        if ($navSub): ?>
                            <span class="px-2.5 py-1 text-xs font-semibold bg-gradient-to-r from-amber-500 to-yellow-500 text-slate-900 rounded-full">
                                PREMIUM
                            </span>
                        <?php else: ?>
                            <a href="/Nova_News/user/subscribe.php"
                                class="px-3 py-1.5 text-xs font-semibold bg-gradient-to-r from-amber-500 to-yellow-500 text-slate-900 rounded-full hover:brightness-110 transition-all">
                                Go Premium
                            </a>
                        <?php endif; ?>

                        <!-- Avatar Dropdown -->
                        <div class="relative group">
                            <button class="flex items-center gap-2 p-1 rounded-full hover:bg-slate-100 transition-colors">
                                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-sm font-bold text-white">
                                    <?= strtoupper(substr(currentUserName() ?? 'U', 0, 1)) ?>
                                </div>
                            </button>
                            <div class="absolute top-full right-0 mt-1 w-44 bg-white rounded-xl shadow-2xl border border-slate-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all z-50">
                                <div class="px-4 py-3 border-b border-slate-200">
                                    <p class="text-sm font-semibold text-slate-900 truncate"><?= htmlspecialchars(currentUserName() ?? '') ?></p>
                                    <p class="text-xs text-blue-500">Member</p>
                                </div>
                                <a href="/Nova_News/user/dashboard.php" class="block px-4 py-2.5 text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-colors">My Dashboard</a>
                                <a href="/Nova_News/user/subscribe.php" class="block px-4 py-2.5 text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-colors">Subscription</a>
                                <a href="/Nova_News/user/payments.php" class="block px-4 py-2.5 text-sm text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-colors"><i class="fa-solid fa-credit-card mr-1.5"></i> My Payments</a>
                                <a href="/Nova_News/public/logout.php" class="block px-4 py-2.5 text-sm text-red-500 hover:text-red-600 hover:bg-slate-50 rounded-b-xl transition-colors">Sign Out</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
    </header>
