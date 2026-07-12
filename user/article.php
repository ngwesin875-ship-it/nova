<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/posts.php';
require_once __DIR__ . '/../includes/subscription.php';

$slug = $_GET['slug'] ?? '';
$post = getPostBySlug($slug);

if (!$post) {
    http_response_code(404);
    include __DIR__ . '/../includes/header.php';
    echo '<main class="max-w-[1440px] mx-auto p-8"><div class="text-center py-20"><h1 class="text-4xl font-bold text-slate-900">404</h1><p class="text-slate-500 mt-2">' . __('article.404') . '</p><a href="index.php" class="mt-4 inline-block text-[#5B41FF] hover:underline">' . __('article.back_to_home') . '</a></div></main>';
    include __DIR__ . '/../includes/footer.php';
    exit;
}

$hasAccess = $post['post_type'] !== 'premium' || isAdmin() || (isLoggedIn() && getActiveSubscription(currentUserId()));

include __DIR__ . '/../includes/header.php';

$catColors = [
    'Technology'    => 'text-cyan-600',
    'Business'      => 'text-amber-600',
    'World'         => 'text-emerald-600',
    'Sports'        => 'text-blue-600',
    'Health'        => 'text-red-600',
    'Science'       => 'text-purple-600',
    'Politics'      => 'text-slate-600',
    'Entertainment' => 'text-purple-600',
];
$catName = htmlspecialchars($post['category_name'] ?? 'Uncategorized');
$color = $catColors[$post['category_name'] ?? ''] ?? 'text-slate-600';
$img = $post['image_url'] ? '/Nova_News/' . htmlspecialchars($post['image_url']) : null;
$title = htmlspecialchars($post['title']);
$content = $post['content'];
$author = htmlspecialchars($post['author_name'] ?? 'Nova News Team');
$date = date('F j, Y', strtotime($post['created_at']));
$type = $post['post_type'];

// Related posts from same category
$relatedPosts = [];
if ($post['category_id']) {
    $relatedPosts = getPostsByCategory((int) $post['category_id'], 1, 4);
    $relatedPosts = array_filter($relatedPosts, function ($rp) use ($slug) {
        return $rp['slug'] !== $slug;
    });
    $relatedPosts = array_slice(array_values($relatedPosts), 0, 3);
}

// Sidebar data
$categories = getAllCategories();
$catIcons = [
    'Technology'    => ['bg' => 'bg-cyan-100', 'text' => 'text-cyan-600', 'icon' => 'fa-laptop-code'],
    'Business'      => ['bg' => 'bg-amber-100', 'text' => 'text-amber-600', 'icon' => 'fa-chart-pie'],
    'World'         => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-600', 'icon' => 'fa-earth-americas'],
    'Sports'        => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'icon' => 'fa-ranking-star'],
    'Health'        => ['bg' => 'bg-red-100', 'text' => 'text-red-600', 'icon' => 'fa-heart'],
    'Science'       => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600', 'icon' => 'fa-flask'],
    'Politics'      => ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'icon' => 'fa-landmark'],
    'Entertainment' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600', 'icon' => 'fa-clapperboard'],
];
$latestNews = getPostsPaginated(1, 4, 'all', 'published');
$editorsPick = getEditorsPickPost();
$latestPosts = $latestNews;
?>

<main class="max-w-[1440px] mx-auto p-4 md:p-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
    <div class="lg:col-span-9 space-y-8">
        <?php
        $from = $_GET['from'] ?? '';
        if ($from === 'all-posts') {
            $backLink = 'all-posts.php';
        } elseif ($from === 'category') {
            $backLink = 'category.php?slug=' . urlencode($_GET['cat_slug'] ?? '');
        } elseif ($from === 'index') {
            $backLink = 'index.php';
        } else {
            $backLink = 'index.php';
        }
        ?>
        <a href="<?= $backLink ?>" class="inline-flex items-center text-sm text-slate-500 hover:text-[#5B41FF]">
            <i class="fa-solid fa-arrow-left mr-2"></i> <?= __('article.back_to_news') ?>
        </a>

        <article>
            <div class="flex flex-col md:flex-row gap-6">
                <?php if ($img): ?>
                <div class="md:w-56 shrink-0">
                    <div class="rounded-xl overflow-hidden sticky top-24">
                        <img src="<?= $img ?>" alt="<?= $title ?>" class="w-full h-auto object-cover">
                    </div>
                </div>
                <?php endif; ?>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="text-xs font-bold uppercase <?= $color ?>"><?= $catName ?></span>
                        <?php if ($type === 'premium'): ?>
                            <span class="text-[10px] font-semibold text-amber-500 flex items-center gap-1 bg-amber-50 px-2 py-0.5 rounded"><i class="fa-solid fa-lock"></i> Premium</span>
                        <?php else: ?>
                            <span class="text-[10px] font-semibold text-emerald-600 flex items-center gap-1 bg-emerald-50 px-2 py-0.5 rounded"><span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> Free</span>
                        <?php endif; ?>
                    </div>

                    <h1 class="text-3xl md:text-4xl font-bold text-slate-900 leading-tight mb-4"><?= $title ?></h1>

                    <div class="flex items-center gap-4 text-sm text-slate-500 mb-6">
                        <span><i class="fa-regular fa-user mr-1"></i> <?= $author ?></span>
                        <span><i class="fa-regular fa-calendar mr-1"></i> <?= $date ?></span>
                    </div>

            <?php if ($hasAccess): ?>
                <div class="prose prose-slate max-w-none text-slate-700 leading-relaxed text-base">
                    <?= $content ?>
                </div>
            <?php else: ?>
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-8 text-center">
                    <div class="text-5xl mb-4">🔒</div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2"><?= __('article.premium_locked_title') ?></h3>
                    <p class="text-slate-600 mb-6 max-w-md mx-auto"><?= __('article.premium_locked_desc') ?></p>
                    <?php if (isLoggedIn()): ?>
                        <a href="subscribe.php" class="inline-block px-6 py-3 bg-gradient-to-r from-amber-500 to-yellow-500 text-slate-900 font-bold rounded-xl hover:brightness-110 transition-all"><?= __('article.go_premium') ?></a>
                    <?php else: ?>
                        <a href="/Nova_News/public/Signin.php" class="inline-block px-6 py-3 bg-[#5B41FF] text-white font-bold rounded-xl hover:bg-[#4830DF] transition-all"><?= __('article.sign_in_to_subscribe') ?></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
                </div>
            </div>
        </article>

        <!-- Related Posts -->
        <?php if (!empty($relatedPosts)): ?>
        <section class="pt-4 border-t border-slate-200">
            <h2 class="text-lg font-bold text-slate-900 mb-4"><?= __('article.related_articles') ?></h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <?php foreach ($relatedPosts as $rp):
                    $rpTitle = htmlspecialchars($rp['title']);
                    $rpSlug = htmlspecialchars($rp['slug']);
                    $rpType = $rp['post_type'];
                    $rpDate = date('M j, Y', strtotime($rp['created_at']));
                    $rpImg = $rp['image_url'] ? '/Nova_News/' . htmlspecialchars($rp['image_url']) : 'https://images.unsplash.com/photo-1504711434969-e33886168d8c?auto=format&fit=crop&w=400&q=80';
                ?>
                <a href="article.php?slug=<?= urlencode($rpSlug) ?>" class="bg-white rounded-xl overflow-hidden border border-slate-100 shadow-sm group hover:shadow-md transition">
                    <div class="h-40 bg-slate-100 overflow-hidden">
                        <img src="<?= $rpImg ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" alt="<?= $rpTitle ?>">
                    </div>
                    <div class="p-4">
                        <h3 class="font-bold text-sm text-slate-800 line-clamp-2 group-hover:text-[#5B41FF] transition"><?= $rpTitle ?></h3>
                        <div class="flex items-center justify-between mt-2">
                            <?php if ($rpType === 'premium'): ?>
                                <span class="text-[10px] font-bold text-amber-600 bg-amber-100 px-1.5 py-0.5 rounded">PREMIUM</span>
                            <?php endif; ?>
                            <span class="text-xs text-slate-400"><?= $rpDate ?></span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </div>

    <!-- right side -->
        <aside class="lg:col-span-3 space-y-6">
            

            <!-- premium access -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                <div class="flex items-center space-x-2 text-amber-500 font-bold text-sm">
                    <i class="fa-solid fa-crown"></i>
                    <span><?= __('sidebar.premium_access') ?></span>
                </div>
                <ul class="space-y-2.5 text-xs text-slate-600">
                    <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-emerald-500"></i> <?= __('sidebar.unlimited') ?></li>
                    <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-emerald-500"></i> <?= __('sidebar.exclusive') ?></li>
                    <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-emerald-500"></i> <?= __('sidebar.early_access') ?></li>
                    <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-emerald-500"></i> <?= __('sidebar.ad_free') ?></li>
                </ul>
                <div class="pt-2 border-t border-slate-100">
                    <div class="text-[11px] text-slate-400"><?= __('sidebar.starting_from') ?></div>
                    <div class="text-xl font-extrabold text-slate-950">3,000 MMK <span class="text-xs font-normal text-slate-500"><?= __('sidebar.per_month') ?></span></div>
                </div>
                <a href="subscribe.php" class="block w-full text-center bg-[#5B41FF] hover:bg-[#4830DF] text-white font-semibold text-sm py-3 rounded-xl shadow-md transition"><?= __('sidebar.choose_plan') ?></a>
            </div>


            <!-- top categories -->
            <?php
            $categories = getAllCategories();
            $catIcons = [
                'Technology'    => ['bg' => 'bg-cyan-100', 'text' => 'text-cyan-600', 'icon' => 'fa-laptop-code'],
                'Business'      => ['bg' => 'bg-amber-100', 'text' => 'text-amber-600', 'icon' => 'fa-chart-pie'],
                'World'         => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-600', 'icon' => 'fa-earth-americas'],
                'Sports'        => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'icon' => 'fa-ranking-star'],
                'Health'        => ['bg' => 'bg-red-100', 'text' => 'text-red-600', 'icon' => 'fa-heart'],
                'Science'       => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600', 'icon' => 'fa-flask'],
                'Politics'      => ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'icon' => 'fa-landmark'],
                'Entertainment' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600', 'icon' => 'fa-clapperboard'],
            ];
            ?>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                <div class="text-xs font-bold text-slate-900 uppercase tracking-wider"><?= __('categories.title') ?></div>
                <div class="space-y-2">
                    <?php foreach ($categories as $cat):
                        $slug = htmlspecialchars($cat['slug']);
                        $name = htmlspecialchars($cat['name']);
                        $count = (int) $cat['article_count'];
                        $style = $catIcons[$cat['name']] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'icon' => 'fa-folder'];
                    ?>
                    <a href="/Nova_News/user/category.php?slug=<?= urlencode($cat['slug']) ?>" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition text-xs">
                        <div class="flex items-center gap-2 font-medium text-slate-700"><span class="w-6 h-6 rounded <?= $style['bg'] . ' ' . $style['text'] ?> flex items-center justify-center"><i class="fa-solid <?= $style['icon'] ?> text-[11px]"></i></span> <?= $name ?></div>
                        <span class="text-slate-400"><?= $count ?> Article<?= $count !== 1 ? 's' : '' ?> <i class="fa-solid fa-chevron-right text-[9px] ml-1"></i></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            
            <?php if ($editorsPick):
                $epImg = $editorsPick['image_url'] ? '/Nova_News/' . htmlspecialchars($editorsPick['image_url']) : 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=300&q=80';
            ?>
            <a href="article.php?slug=<?= urlencode($editorsPick['slug']) ?>" class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4 block group">
                <div class="text-xs font-bold text-slate-900 uppercase tracking-wider"><?= __('editor_pick.title') ?></div>
                <div class="rounded-xl overflow-hidden bg-slate-950 relative h-36 group cursor-pointer">
                    <img src="<?= $epImg ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-300 opacity-70" alt="<?= htmlspecialchars($editorsPick['title']) ?>">
                    <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent"></div>
                    <div class="absolute bottom-3 left-3 right-3 text-white">
                        <?php if (($editorsPick['post_type'] ?? 'free') === 'premium'): ?>
                        <span class="bg-[#5B41FF] text-[9px] font-bold uppercase px-1.5 py-0.5 rounded">Premium</span>
                        <?php endif; ?>
                        <h4 class="font-bold text-xs line-clamp-2 mt-1.5"><?= htmlspecialchars($editorsPick['title']) ?></h4>
                    </div>
                </div>
                <div class="flex justify-between items-center text-[11px] text-slate-400">
                    <span><?= htmlspecialchars($editorsPick['author_name'] ?? 'Nova News Team') ?></span>
                    <span><?= htmlspecialchars(date('M j, Y', strtotime($editorsPick['created_at']))) ?></span>
                </div>
            </a>
            <?php endif; ?>

            <!-- LATEST NEWS -->
            <section class="space-y-4">
                <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                    <div class="flex items-center space-x-4">
                        <h2 class="text-lg font-bold text-slate-900"><?= __('latest_news.title') ?></h2>
                        <div class="flex bg-slate-200/60 p-0.5 rounded-lg text-xs font-semibold text-slate-600">
                            <button type="button" data-filter="all" class="filter-btn bg-[#5B41FF] text-white px-3 py-1 rounded-md"><?= __('filter.all') ?></button>
                            <button type="button" data-filter="free" class="filter-btn px-3 py-1 rounded-md text-slate-600 hover:text-slate-900"><?= __('filter.free') ?></button>
                            <button type="button" data-filter="premium" class="filter-btn px-3 py-1 rounded-md text-slate-600 hover:text-slate-900"><?= __('filter.premium') ?></button>
                        </div>
                    </div>
                </div>

                <div id="latest-news-grid" class="grid grid-cols-1 gap-6">
                    <?php if (empty($latestPosts)): ?>
                        <div class="col-span-full text-center text-slate-400 py-12 text-sm"><?= __('empty.no_articles') ?></div>
                    <?php else: ?>
                    <?php foreach ($latestPosts as $post):
                        $type = $post['post_type'];
                        $catName = htmlspecialchars($post['category_name'] ?? 'Uncategorized');
                        $color = $catColors[$post['category_name'] ?? ''] ?? 'text-slate-600';
                        $img = $post['image_url'] ? '/Nova_News/' . htmlspecialchars($post['image_url']) : 'https://images.unsplash.com/photo-1504711434969-e33886168d8c?auto=format&fit=crop&w=200&q=80';
                        $title = htmlspecialchars($post['title']);
                    ?>
                    <a href="../user/article.php?slug=<?= urlencode($post['slug']) ?>" data-type="<?= $type ?>" class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm flex space-x-4 group cursor-pointer">
                        <div class="w-28 h-24 rounded-lg bg-slate-100 overflow-hidden shrink-0">
                            <img src="<?= $img ?>" class="w-full h-full object-cover group-hover:scale-105 transition" alt="<?= $title ?>">
                        </div>
                        <div class="flex flex-col justify-between flex-1 min-w-0">
                            <div>
                                <span class="text-[10px] font-bold uppercase <?= $color ?>"><?= $catName ?></span>
                                <h4 class="font-bold text-xs text-slate-800 line-clamp-2 mt-0.5 group-hover:text-[#5B41FF]"><?= $title ?></h4>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-slate-400 mt-2">
                                <?php if ($type === 'premium'): ?>
                                    <span class="bg-amber-50 text-amber-700 font-medium flex items-center gap-1 border border-amber-300 px-2 py-0.5 rounded"><i class="fa-solid fa-lock text-[10px]"></i> <?= __('badge.premium') ?></span>
                                <?php else: ?>
                                    <span class="bg-emerald-50 text-emerald-700 font-medium flex items-center gap-1 border border-emerald-300 px-2 py-0.5 rounded"><span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> <?= __('badge.free_article') ?></span>
                                <?php endif; ?>
                                <span><?= date('M j, Y', strtotime($post['created_at'])) ?></span>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

        </aside>
</main>

<script>
(function() {
    var slug = '<?= $slug ?>';
    var key = 'nova_scroll_' + slug;
    var saved = sessionStorage.getItem(key);
    if (saved) {
        var pos = parseInt(saved, 10);
        if (!isNaN(pos)) {
            window.addEventListener('load', function() {
                setTimeout(function() { window.scrollTo(0, pos); }, 150);
            });
        }
    }
    window.addEventListener('beforeunload', function() {
        sessionStorage.setItem(key, window.scrollY);
    });
})();
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>
