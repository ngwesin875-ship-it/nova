<?php
$pageTitle = $pageTitle ?? 'Dashboard';
?>
<header class="bg-white/80 backdrop-blur-md shadow-sm border-b border-slate-200/80 h-16 flex justify-between items-center px-8 shrink-0 sticky top-0 left-0 right-0 z-30">
    <h2 class="text-2xl font-bold text-slate-900 tracking-tight">
        <?= htmlspecialchars($pageTitle) ?>
    </h2>
    <div class="flex items-center gap-5">
        <?php include __DIR__ . '/admin-header.php'; ?>
        
        <div class="h-8 w-px bg-slate-200 mx-2"></div>
        
        <div class="relative group">
            <button class="flex items-center gap-3 hover:bg-slate-50/50 py-1.5 pl-1.5 pr-3 rounded-full transition-colors focus:outline-none border border-transparent hover:border-slate-200">
                <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center text-sm font-bold text-white shadow-sm ring-2 ring-white">
                    <?= htmlspecialchars($displayInitial ?? 'A') ?>
                </div>
                <span class="font-semibold text-slate-900 hidden sm:block text-sm">
                    <?= htmlspecialchars($displayName ?? 'Admin') ?>
                </span>
                <i class="fa-solid fa-chevron-down text-[10px] text-slate-500 hidden sm:block transition-transform group-hover:rotate-180"></i>
            </button>
            
            <div class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 transform origin-top-right scale-95 group-hover:scale-100">
                <div class="p-4 border-b border-slate-200 bg-slate-50/50 rounded-t-2xl">
                    <p class="text-sm font-bold text-slate-900 truncate"><?= htmlspecialchars($displayName ?? 'Admin') ?></p>
                    <p class="text-xs font-medium text-emerald-600 truncate mt-0.5 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <?= htmlspecialchars($displayRole ?? 'Administrator') ?>
                    </p>
                </div>
                <div class="p-2">
                    <a href="/Nova_News/public/logout.php" class="flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-red-600 hover:text-red-600 hover:bg-red-50 rounded-xl transition-colors">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i> Sign out
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>
