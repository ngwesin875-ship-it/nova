<?php
require_once __DIR__ . '/../includes/posts.php';
$latestPosts = getPostsPaginated(1, 4, 'all', 'published');
$editorsPick = getEditorsPickPost();
$catColors = [
    'Technology'    => 'text-cyan-600', 'Business' => 'text-amber-600',
    'World'         => 'text-emerald-600', 'Sports' => 'text-blue-600',
    'Health'        => 'text-red-600', 'Science' => 'text-purple-600',
    'Politics'      => 'text-slate-600', 'Entertainment' => 'text-purple-600',
];
include __DIR__ . '/../includes/header.php';
?>

    <!-- main -->
    <main class="max-w-[1440px] mx-auto p-4 md:p-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <div class="lg:col-span-9 space-y-8">
            
            <?php
            $heroPosts = getFeaturedPosts(1);
            $hero = $heroPosts[0] ?? null;
            ?>
            <div class="relative bg-[#090D1A] rounded-2xl overflow-hidden min-h-[480px] flex items-end p-6 md:p-12 text-white shadow-xl">
                <div class="absolute inset-0 opacity-40 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-blue-600 via-purple-900 to-slate-950"></div>
                <div class="absolute top-0 right-0 w-full md:w-1/2 h-full bg-cover bg-center mix-blend-lighten opacity-80" style="background-image: url('<?= $hero ? '/Nova_News/' . htmlspecialchars($hero['image_url']) : 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=800&q=80' ?>');"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-[#090D1A] via-[#090D1A]/50 to-transparent"></div>

                <div class="relative z-10 max-w-xl space-y-4">
                    <?php if ($hero && $hero['post_type'] === 'premium'): ?>
                    <span class="bg-[#5B41FF]/20 text-[#9E8FFF] text-[11px] font-bold tracking-wider uppercase px-2.5 py-1 rounded-md border border-[#5B41FF]/30">Premium Exclusive</span>
                    <?php endif; ?>
                    <div class="text-[#00D4FF] uppercase text-xs font-bold tracking-widest pt-2"><?= htmlspecialchars($hero['category_name'] ?? 'Featured') ?></div>
                    <h1 class="text-2xl md:text-4xl font-extrabold leading-tight"><?= htmlspecialchars($hero['title'] ?? 'Welcome to Nova News') ?></h1>
                    <p class="text-slate-300 text-sm md:text-base font-light leading-relaxed"><?= htmlspecialchars($hero['excerpt'] ?? 'Stay informed with the latest breaking news, exclusive stories, and in-depth analysis from around the world.') ?></p>

                    <div class="pt-4 flex flex-wrap gap-3">
                        <?php if ($hero): ?>
                        <a href="article.php?slug=<?= urlencode($hero['slug']) ?>" class="bg-[#5B41FF] hover:bg-[#4830DF] text-white text-sm font-semibold px-5 py-3 rounded-xl flex items-center gap-2 shadow-lg transition">
                            <?php if ($hero['post_type'] === 'premium'): ?>
                            <i class="fa-solid fa-lock"></i> Unlock This Article
                            <?php else: ?>
                            <i class="fa-solid fa-book-open"></i> Read Article
                            <?php endif; ?>
                        </a>
                        <?php endif; ?>
                    </div>

                   
                </div>
            </div>


            <!-- TRENDING TODAY -->
            <?php
            $trendingPosts = getPostsPaginated(1, 3, 'all', 'published');
            $trendingBadgeColors = [
                'Technology'    => 'bg-cyan-500/90',
                'Business'      => 'bg-amber-500/90',
                'World'         => 'bg-emerald-500/90',
                'Sports'        => 'bg-blue-500/90',
                'Health'        => 'bg-red-500/90',
                'Science'       => 'bg-purple-500/90',
                'Politics'      => 'bg-slate-500/90',
                'Entertainment' => 'bg-pink-500/90',
            ];
            ?>
            <section class="space-y-4">
                <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <span class="text-orange-500"><i class="fa-solid fa-fire"></i></span> TRENDING TODAY
                    </h2>
                    <a href="all-posts.php" class="text-sm font-medium text-[#5B41FF] hover:underline">View All <i class="fa-solid fa-arrow-right text-xs ml-1"></i></a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php if (empty($trendingPosts)): ?>
                        <div class="col-span-full text-center text-slate-400 py-12 text-sm">No trending articles yet.</div>
                    <?php else: ?>
                    <?php foreach ($trendingPosts as $post):
                        $catName = htmlspecialchars($post['category_name'] ?? 'Uncategorized');
                        $badgeColor = $trendingBadgeColors[$post['category_name'] ?? ''] ?? 'bg-slate-500/90';
                        $img = $post['image_url'] ? '/Nova_News/' . htmlspecialchars($post['image_url']) : 'https://images.unsplash.com/photo-1504711434969-e33886168d8c?auto=format&fit=crop&w=400&q=80';
                        $title = htmlspecialchars($post['title']);
                    ?>
                    <a href="article.php?from=index&slug=<?= urlencode($post['slug']) ?>" class="bg-white rounded-xl overflow-hidden shadow-sm border border-slate-100 flex flex-col group cursor-pointer">
                        <div class="h-40 bg-slate-100 relative overflow-hidden">
                            <img src="<?= $img ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" alt="<?= $title ?>">
                            <span class="absolute top-3 left-3 <?= $badgeColor ?> text-white text-[10px] font-bold uppercase px-2 py-0.5 rounded"><?= $catName ?></span>
                        </div>
                        <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
                            <h3 class="font-bold text-sm text-slate-800 line-clamp-2 group-hover:text-[#5B41FF] transition"><?= $title ?></h3>
                            <div class="flex items-center justify-between text-xs text-slate-400">
                                <span><?= date('M j, Y', strtotime($post['created_at'])) ?></span>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>


            <!-- categories pills -->
            <?php
            $allCategories = getAllCategories();
            ?>
            <section class="space-y-4">
                <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                    <h2 class="text-lg font-bold text-slate-900">BROWSE CATEGORIES</h2>
                </div>

                <?php if (empty($allCategories)): ?>
                    <div class="text-center text-slate-400 py-12 text-sm">No categories yet.</div>
                <?php else: ?>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($allCategories as $cat):
                        $name = htmlspecialchars($cat['name']);
                        $slug = htmlspecialchars($cat['slug']);
                        $count = (int) $cat['article_count'];
                    ?>
                    <button class="category-pill px-4 py-2 rounded-full border border-slate-200 bg-white text-sm font-medium text-slate-700 hover:bg-[#5B41FF] hover:text-white hover:border-[#5B41FF] transition" data-category="<?= $slug ?>">
                        <?= $name ?>
                        <span class="text-xs opacity-60 ml-1">(<?= $count ?>)</span>
                    </button>
                    <?php endforeach; ?>
                </div>

                <div id="category-posts-container">
                    <?php foreach ($allCategories as $i => $cat):
                        $catId = (int) $cat['id'];
                        $slug = htmlspecialchars($cat['slug']);
                        $catPosts = getPostsByCategory($catId, 1, 5);
                    ?>
                    <div class="category-posts hidden" data-category="<?= $slug ?>">
                        <?php if (empty($catPosts)): ?>
                            <p class="text-xs text-slate-400 py-4">No articles in this category yet.</p>
                        <?php else: ?>
                        <div class="grid grid-cols-1 gap-3">
                            <?php foreach ($catPosts as $post):
                                $postTitle = htmlspecialchars($post['title']);
                                $postSlug = htmlspecialchars($post['slug']);
                                $postType = $post['post_type'];
                                $postDate = date('M j, Y', strtotime($post['created_at']));
                                $postImg = $post['image_url'] ? '/Nova_News/' . htmlspecialchars($post['image_url']) : 'https://images.unsplash.com/photo-1504711434969-e33886168d8c?auto=format&fit=crop&w=200&q=80';
                            ?>
                            <a href="article.php?slug=<?= urlencode($postSlug) ?>" class="flex items-center gap-4 bg-white p-3 rounded-xl border border-slate-100 hover:border-[#5B41FF]/30 hover:shadow-sm transition group">
                                <div class="w-20 h-16 rounded-lg bg-slate-100 overflow-hidden shrink-0">
                                    <img src="<?= $postImg ?>" class="w-full h-full object-cover group-hover:scale-105 transition" alt="<?= $postTitle ?>">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <span class="text-sm font-medium text-slate-700 group-hover:text-[#5B41FF] truncate block"><?= $postTitle ?></span>
                                    <div class="flex items-center gap-2 mt-1">
                                        <?php if ($postType === 'premium'): ?>
                                            <span class="text-[10px] font-bold text-amber-600 bg-amber-100 px-1.5 py-0.5 rounded">PREMIUM</span>
                                        <?php endif; ?>
                                        <span class="text-xs text-slate-400"><?= $postDate ?></span>
                                    </div>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </section>
        </div>

        <!-- right side -->
        <aside class="lg:col-span-3 space-y-6">
            

            <!-- premium access -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                <div class="flex items-center space-x-2 text-amber-500 font-bold text-sm">
                    <i class="fa-solid fa-crown"></i>
                    <span>PREMIUM ACCESS</span>
                </div>
                <ul class="space-y-2.5 text-xs text-slate-600">
                    <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-emerald-500"></i> Unlimited permium articles</li>
                    <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-emerald-500"></i> Exclusive analysis & reports</li>
                    <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-emerald-500"></i> Early access to breaking news</li>
                    <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-emerald-500"></i> Ad-free to breaking news</li>
                </ul>
                <div class="pt-2 border-t border-slate-100">
                    <div class="text-[11px] text-slate-400">Starting from</div>
                    <div class="text-xl font-extrabold text-slate-950">3,000 MMK <span class="text-xs font-normal text-slate-500">/ Month</span></div>
                </div>
                <a href="subscribe.php" class="block w-full text-center bg-[#5B41FF] hover:bg-[#4830DF] text-white font-semibold text-sm py-3 rounded-xl shadow-md transition">Choose Plan</a>
                <div class="text-center"><a href="subscribe.php" class="text-xs font-semibold text-slate-500 hover:text-slate-800">Learn More <i class="fa-solid fa-arrow-right text-[10px] ml-0.5"></i></a></div>
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
                <div class="text-xs font-bold text-slate-900 uppercase tracking-wider">Top Categories</div>
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
                <div class="text-xs font-bold text-slate-900 uppercase tracking-wider">Editor's Pick</div>
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
                        <h2 class="text-lg font-bold text-slate-900">LATEST NEWS</h2>
                        <div class="flex bg-slate-200/60 p-0.5 rounded-lg text-xs font-semibold text-slate-600">
                            <button type="button" data-filter="all" class="filter-btn bg-[#5B41FF] text-white px-3 py-1 rounded-md">All</button>
                            <button type="button" data-filter="free" class="filter-btn px-3 py-1 rounded-md text-slate-600 hover:text-slate-900">Free</button>
                            <button type="button" data-filter="premium" class="filter-btn px-3 py-1 rounded-md text-slate-600 hover:text-slate-900">Premium</button>
                        </div>
                    </div>
                </div>

                <div id="latest-news-grid" class="grid grid-cols-1 gap-6">
                    <?php if (empty($latestPosts)): ?>
                        <div class="col-span-full text-center text-slate-400 py-12 text-sm">No articles yet.</div>
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
                                    <span class="bg-amber-50 text-amber-700 font-medium flex items-center gap-1 border border-amber-300 px-2 py-0.5 rounded"><i class="fa-solid fa-lock text-[10px]"></i> Premium</span>
                                <?php else: ?>
                                    <span class="bg-emerald-50 text-emerald-700 font-medium flex items-center gap-1 border border-emerald-300 px-2 py-0.5 rounded"><span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> Free </span>
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

        <!-- PREMIUM ARTICLES -->
        <section class="lg:col-span-12 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                    <span class="text-amber-500"><i class="fa-solid fa-crown"></i></span> PREMIUM ARTICLES
                </h2>
                <a href="all-posts.php?type=premium" class="text-sm font-medium text-[#5B41FF] hover:underline">View All <i class="fa-solid fa-arrow-right text-xs ml-1"></i></a>
            </div>

            <?php
            $premiumPosts = getPostsPaginated(1, 4, 'premium', 'published');
            ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php if (empty($premiumPosts)): ?>
                    <div class="col-span-full text-center text-slate-400 py-12 text-sm">No premium articles yet.</div>
                <?php else: ?>
                <?php foreach ($premiumPosts as $post):
                    $catName = htmlspecialchars($post['category_name'] ?? 'Uncategorized');
                    $catStyle = $catIcons[$post['category_name'] ?? ''] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-600'];
                    $img = $post['image_url'] ? '/Nova_News/' . htmlspecialchars($post['image_url']) : 'https://images.unsplash.com/photo-1504711434969-e33886168d8c?auto=format&fit=crop&w=200&q=80';
                    $title = htmlspecialchars($post['title']);
                ?>
                <a href="article.php?slug=<?= urlencode($post['slug']) ?>" class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm flex space-x-4 group cursor-pointer">
                    <div class="w-28 h-24 rounded-lg bg-slate-100 overflow-hidden shrink-0">
                        <img src="<?= $img ?>" class="w-full h-full object-cover group-hover:scale-105 transition" alt="<?= $title ?>">
                    </div>
                    <div class="flex flex-col justify-between flex-1 min-w-0">
                        <div>
                            <span class="text-[10px] font-bold uppercase <?= $catStyle['text'] ?> <?= $catStyle['bg'] ?> border-2 border-current/30 px-2 py-0.5 rounded-lg"><?= $catName ?></span>
                            <h4 class="font-bold text-xs text-slate-800 line-clamp-2 mt-0.5 group-hover:text-[#5B41FF]"><?= $title ?></h4>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-slate-400 mt-2">
                            <span class="bg-amber-50 text-amber-700 font-medium flex items-center gap-1 border border-amber-300 px-2 py-0.5 rounded"><i class="fa-solid fa-lock text-[10px]"></i> Premium</span>
                            <span><?= date('M j, Y', strtotime($post['created_at'])) ?></span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

    </main>

<style>
.category-pill.active { background-color: #5B41FF; color: white; border-color: #5B41FF; }
</style>
<script>
document.querySelectorAll('.category-pill').forEach(function(pill) {
    pill.addEventListener('click', function() {
        document.querySelectorAll('.category-pill').forEach(function(p) { p.classList.remove('active'); });
        this.classList.add('active');
        var slug = this.dataset.category;
        document.querySelectorAll('.category-posts').forEach(function(panel) {
            panel.classList.toggle('hidden', panel.dataset.category !== slug);
        });
    });
});
var firstPill = document.querySelector('.category-pill');
if (firstPill) firstPill.click();
</script>
<?php include __DIR__ . '/../includes/footer.php'; ?>