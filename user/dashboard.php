<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/subscription.php';
require_once __DIR__ . '/../includes/payments.php';
require_once __DIR__ . '/../includes/posts.php';

requireLogin();
if (isAdmin()) { header('Location: /Nova_News/admin/index.php'); exit; }

$user = getUserById(currentUserId());
$sub = getActiveSubscription(currentUserId());
$payments = getUserPayments(currentUserId());
$editorsPick = getEditorsPickPost();
$pageTitle = 'My Dashboard - Nova News';

include __DIR__ . '/../includes/header.php';
?>

<main class="flex-1">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-theme-adaptive"><?= __('dashboard.welcome_back') ?>, <?= htmlspecialchars($user['username'] ?? 'Member') ?>!</h1>
            <p class="text-slate-500 text-sm mt-1"><?= __('dashboard.overview') ?></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

            <div class="bg-white border border-slate-200 rounded-2xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-blue-500/20 rounded-xl flex items-center justify-center text-blue-500">
                        <i class="fa-solid fa-user text-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold"><?= __('dashboard.account') ?></p>
                        <p class="text-sm font-bold text-slate-900"><?= __('dashboard.profile_details') ?></p>
                    </div>
                </div>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-slate-500"><?= __('dashboard.username') ?></dt>
                        <dd class="text-slate-900 font-medium"><?= htmlspecialchars($user['username'] ?? '-') ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500"><?= __('dashboard.email') ?></dt>
                        <dd class="text-slate-900 font-medium truncate max-w-[200px]"><?= htmlspecialchars($user['email'] ?? '-') ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500"><?= __('dashboard.member_since') ?></dt>
                        <dd class="text-slate-900 font-medium"><?= isset($user['created_at']) ? date('M j, Y', strtotime($user['created_at'])) : '-' ?></dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-amber-500/20 rounded-xl flex items-center justify-center text-amber-500">
                        <i class="fa-solid fa-crown text-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold"><?= __('dashboard.subscription') ?></p>
                        <p class="text-sm font-bold text-slate-900"><?= __('dashboard.current_plan') ?></p>
                    </div>
                </div>
                <?php if ($sub): ?>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-slate-500"><?= __('dashboard.plan') ?></dt>
                            <dd class="text-amber-500 font-semibold"><?= htmlspecialchars($sub['plan_name']) ?></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500"><?= __('dashboard.status') ?></dt>
                            <dd><span class="bg-green-500/10 text-green-600 border border-green-500/30 px-2.5 py-0.5 rounded-full text-xs font-medium"><?= __('dashboard.active') ?></span></dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500"><?= __('dashboard.valid_until') ?></dt>
                            <dd class="text-slate-900 font-medium"><?= date('M j, Y', strtotime($sub['end_date'])) ?></dd>
                        </div>
                    </dl>
                    <div class="mt-4 pt-4 border-t border-slate-200">
                        <a href="subscribe.php" class="text-sm text-blue-500 hover:text-blue-600 font-medium"><?= __('dashboard.change_plan') ?> <i class="fa-solid fa-arrow-right text-xs ml-1"></i></a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p class="text-slate-500 text-sm mb-3"><?= __('dashboard.no_subscription') ?></p>
                        <a href="subscribe.php" class="inline-block px-5 py-2.5 bg-gradient-to-r from-amber-500 to-yellow-500 text-slate-900 font-bold rounded-xl hover:brightness-110 transition text-sm shadow-lg">
                            <i class="fa-solid fa-crown mr-1"></i> <?= __('dashboard.subscribe_now') ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-emerald-500/20 rounded-xl flex items-center justify-center text-emerald-500">
                        <i class="fa-solid fa-credit-card text-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500 uppercase tracking-wider font-semibold">Payments</p>
                        <p class="text-sm font-bold text-slate-900">Recent Activity</p>
                    </div>
                </div>
                <?php if (!empty($payments)): ?>
                    <div class="space-y-2">
                        <?php foreach (array_slice($payments, 0, 3) as $p): ?>
                            <div class="flex items-center justify-between text-sm py-1.5 border-b border-slate-200 last:border-0">
                                <div>
                                    <p class="text-slate-900 font-medium text-xs"><?= htmlspecialchars($p['plan_name']) ?></p>
                                    <p class="text-slate-500 text-[11px]"><?= date('M j, Y', strtotime($p['paid_at'])) ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-amber-500 font-semibold text-sm">$<?= number_format((float) $p['amount'], 2) ?></p>
                                    <?php if ($p['status'] === 'success'): ?>
                                        <span class="text-green-600 text-[10px] font-medium">Paid</span>
                                    <?php else: ?>
                                        <span class="text-red-500 text-[10px] font-medium"><?= htmlspecialchars(ucfirst($p['status'])) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-200">
                        <a href="payments.php" class="text-sm text-blue-500 hover:text-blue-600 font-medium">View All Payments <i class="fa-solid fa-arrow-right text-xs ml-1"></i></a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p class="text-slate-500 text-sm">No payments yet</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-6">
            <h2 class="text-lg font-bold text-slate-900 mb-4">Quick Links</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="subscribe.php" class="flex items-center gap-3 p-4 rounded-xl bg-slate-50 hover:bg-slate-100 transition-colors border border-slate-200">
                    <div class="w-9 h-9 bg-amber-500/20 rounded-lg flex items-center justify-center text-amber-500">
                        <i class="fa-solid fa-crown"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Subscription</p>
                        <p class="text-xs text-slate-500">Manage your plan</p>
                    </div>
                </a>
                <a href="payments.php" class="flex items-center gap-3 p-4 rounded-xl bg-slate-50 hover:bg-slate-100 transition-colors border border-slate-200">
                    <div class="w-9 h-9 bg-emerald-500/20 rounded-lg flex items-center justify-center text-emerald-500">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Payments</p>
                        <p class="text-xs text-slate-500">View payment history</p>
                    </div>
                </a>
                <a href="/Nova_News/user/index.php" class="flex items-center gap-3 p-4 rounded-xl bg-slate-50 hover:bg-slate-100 transition-colors border border-slate-200">
                    <div class="w-9 h-9 bg-blue-500/20 rounded-lg flex items-center justify-center text-blue-500">
                        <i class="fa-solid fa-newspaper"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-900">Browse News</p>
                        <p class="text-xs text-slate-500">Read latest articles</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Editor's Pick -->
        <?php if ($editorsPick): ?>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
            <div class="text-xs font-bold text-slate-900 uppercase tracking-wider">Editor's Pick</div>
            <a href="/Nova_News/user/article.php?slug=<?= htmlspecialchars($editorsPick['slug']) ?>" class="block rounded-xl overflow-hidden bg-slate-950 relative h-36 group cursor-pointer">
                <?php if (!empty($editorsPick['image_url'])): ?>
                    <img src="/Nova_News/<?= htmlspecialchars($editorsPick['image_url']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-300 opacity-70" alt="<?= htmlspecialchars($editorsPick['title']) ?>">
                <?php else: ?>
                    <img src="https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=300&q=80" class="w-full h-full object-cover group-hover:scale-105 transition duration-300 opacity-70" alt="Editor's Pick">
                <?php endif; ?>
                <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent"></div>
                <div class="absolute bottom-3 left-3 right-3 text-white">
                    <?php if (($editorsPick['post_type'] ?? 'free') === 'premium'): ?>
                        <span class="bg-[#5B41FF] text-[9px] font-bold uppercase px-1.5 py-0.5 rounded">Premium</span>
                    <?php else: ?>
                        <span class="bg-green-500 text-[9px] font-bold uppercase px-1.5 py-0.5 rounded">Free</span>
                    <?php endif; ?>
                    <h4 class="font-bold text-xs line-clamp-2 mt-1.5"><?= htmlspecialchars($editorsPick['title']) ?></h4>
                </div>
            </a>
            <div class="flex justify-between items-center text-[11px] text-slate-500">
                <span>By <?= htmlspecialchars($editorsPick['author_name'] ?? 'Nova News Team') ?></span>
                <span><?= date('M j, Y', strtotime($editorsPick['created_at'])) ?></span>
            </div>
        </div>
        <?php endif; ?>

    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
