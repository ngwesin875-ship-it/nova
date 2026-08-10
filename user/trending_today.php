<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/posts.php';

$db = getDB();
$trendingTodayPosts = [];

$stmt = $db->prepare('
    SELECT p.*, u.username AS author_name, c.name AS category_name
    FROM posts p
    LEFT JOIN users u ON p.created_by = u.id
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE DATE(p.created_at) = CURDATE() AND p.status = "published"
    ORDER BY p.created_at DESC
');
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && method_exists($result, 'fetch_assoc')) {
        while ($row = $result->fetch_assoc()) {
            $trendingTodayPosts[] = $row;
        }
    }
}

$editorsPick = getEditorsPickPost();
$latestPosts = getPostsPaginated(1, 4, 'all', 'published');

$catColors = [
    'Technology'    => 'bg-cyan-500/90',
    'Business'      => 'bg-amber-500/90',
    'World'         => 'bg-emerald-500/90',
    'Sports'        => 'bg-blue-500/90',
    'Health'        => 'bg-red-500/90',
    'Science'       => 'bg-purple-500/90',
    'Politics'      => 'bg-slate-500/90',
    'Entertainment' => 'bg-pink-500/90',
];

$catTextColors = [
    'Technology'    => 'text-cyan-600',
    'Business'      => 'text-amber-600',
    'World'         => 'text-emerald-600',
    'Sports'        => 'text-blue-600',
    'Health'        => 'text-red-600',
    'Science'       => 'text-purple-600',
    'Politics'      => 'text-slate-600',
    'Entertainment' => 'text-purple-600',
];

include __DIR__ . '/../includes/header.php';
?>

<main class="max-w-[1440px] mx-auto p-4 md:p-8 grid grid-cols-1 lg:grid-cols-12 gap-8">

    <div class="lg:col-span-9 space-y-8">

        <!-- Back to Home -->
        <div>
            <a href="index.php" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-[#5B41FF] transition">
                <i class="fa-solid fa-arrow-left text-xs"></i> Back to Home
            </a>
        </div>

        <!-- Page Header -->
        <section class="space-y-2">
            <div class="flex items-center gap-3">
                <span class="text-orange-500"><i class="fa-solid fa-fire text-xl"></i></span>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900">TRENDING TODAY</h1>
            </div>
            <p class="text-slate-500 text-sm">
                <?= date('l, F j, Y') ?> — All articles published today
            </p>
            <div class="border-b border-slate-200 mt-2"></div>
        </section>

        <?php if (empty($trendingTodayPosts)): ?>
            <!-- Empty State -->
            <div class="text-center py-20">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-slate-100 mb-6">
                    <i class="fa-regular fa-newspaper text-3xl text-slate-300"></i>
                </div>
                <p class="text-slate-400 text-base font-medium">ဒီနေ့အတွက် တင်ထားသော သတင်းများ မရှိသေးပါ။</p>
                <p class="text-slate-400/70 text-sm mt-2">Check back later for today's trending stories.</p>
                <a href="index.php" class="inline-flex items-center gap-2 mt-6 bg-[#5B41FF] hover:bg-[#4830DF] text-white text-sm font-semibold px-6 py-3 rounded-xl shadow-md transition">
                    <i class="fa-solid fa-house text-xs"></i> Back to Home
                </a>
            </div>
        <?php else: ?>
            <!-- Posts Count -->
            <div>
                <span class="text-sm font-semibold text-slate-600 bg-slate-100 px-3 py-1 rounded-full">
                    <?= count($trendingTodayPosts) ?> Article<?= count($trendingTodayPosts) !== 1 ? 's' : '' ?> Published Today
                </span>
            </div>

            <!-- Posts Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($trendingTodayPosts as $post):
                    $catName = htmlspecialchars($post['category_name'] ?? 'Uncategorized');
                    $badgeColor = $catColors[$post['category_name'] ?? ''] ?? 'bg-slate-500/90';
                    $img = $post['image_url'] ? '/Nova_News/' . htmlspecialchars($post['image_url']) : 'https://images.unsplash.com/photo-1504711434969-e33886168d8c?auto=format&fit=crop&w=600&q=80';
                    $title = htmlspecialchars($post['title']);
                    $excerpt = htmlspecialchars($post['excerpt'] ?? '');
                    $author = htmlspecialchars($post['author_name'] ?? 'Nova News Team');
                    $postType = $post['post_type'];
                ?>
                <a href="article.php?slug=<?= urlencode($post['slug']) ?>" class="bg-white rounded-xl overflow-hidden shadow-sm border border-slate-100 flex flex-col group cursor-pointer hover:shadow-md transition">
                    <div class="h-48 bg-slate-100 relative overflow-hidden">
                        <img src="<?= $img ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-300" alt="<?= $title ?>">
                        
                            <?php $isSaved = in_array($post['id'], $savedPostIds ?? []); ?>
                            <button type="button" onclick="event.preventDefault(); event.stopPropagation(); toggleBookmark(<?= $post['id'] ?>, this)" class="absolute top-3 right-3 w-7 h-7 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center shadow-sm text-slate-700 hover:text-[#5B41FF] transition z-10">
                                <i class="<?= $isSaved ? 'fa-solid text-[#5B41FF]' : 'fa-regular' ?> fa-bookmark text-xs"></i>
                            </button><span class="absolute top-3 left-3 <?= $badgeColor ?> text-white text-[10px] font-bold uppercase px-2 py-0.5 rounded"><?= $catName ?></span>
                        <?php if ($postType === 'premium'): ?>
                            <span class="absolute top-3 right-3 bg-amber-500/90 text-white text-[10px] font-bold uppercase px-2 py-0.5 rounded flex items-center gap-1">
                                <i class="fa-solid fa-lock text-[8px]"></i> Premium
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                        <h3 class="font-bold text-base text-slate-800 line-clamp-2 group-hover:text-[#5B41FF] transition"><?= $title ?></h3>
                        <?php if ($excerpt): ?>
                            <p class="text-slate-500 text-xs line-clamp-2"><?= $excerpt ?></p>
                        <?php endif; ?>
                        <div class="flex items-center justify-between text-xs text-slate-400 pt-2 border-t border-slate-100">
                            <span class="flex items-center gap-1.5">
                                <i class="fa-regular fa-user"></i> <?= $author ?>
                            </span>
                            <span class="flex items-center gap-1.5">
                                <i class="fa-regular fa-clock"></i> <?= date('g:i A', strtotime($post['created_at'])) ?>
                            </span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

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
                <h2 class="text-lg font-bold text-slate-900">LATEST NEWS</h2>
            </div>

            <div class="grid grid-cols-1 gap-6">
                <?php if (empty($latestPosts)): ?>
                    <div class="col-span-full text-center text-slate-400 py-12 text-sm">No articles yet.</div>
                <?php else: ?>
                <?php foreach ($latestPosts as $post):
                    $type = $post['post_type'];
                    $catName = htmlspecialchars($post['category_name'] ?? 'Uncategorized');
                    $color = $catTextColors[$post['category_name'] ?? ''] ?? 'text-slate-600';
                    $img = $post['image_url'] ? '/Nova_News/' . htmlspecialchars($post['image_url']) : 'https://images.unsplash.com/photo-1504711434969-e33886168d8c?auto=format&fit=crop&w=200&q=80';
                    $title = htmlspecialchars($post['title']);
                ?>
                <a href="article.php?slug=<?= urlencode($post['slug']) ?>" class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm flex space-x-4 group cursor-pointer">
                    <div class="w-28 h-24 rounded-lg bg-slate-100 overflow-hidden shrink-0">
                        <img src="<?= $img ?>" class="w-full h-full object-cover group-hover:scale-105 transition" alt="<?= $title ?>">
                    
                            <?php $isSaved = in_array($post['id'], $savedPostIds ?? []); ?>
                            <button type="button" onclick="event.preventDefault(); event.stopPropagation(); toggleBookmark(<?= $post['id'] ?>, this)" class="absolute top-3 right-3 w-7 h-7 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center shadow-sm text-slate-700 hover:text-[#5B41FF] transition z-10">
                                <i class="<?= $isSaved ? 'fa-solid text-[#5B41FF]' : 'fa-regular' ?> fa-bookmark text-xs"></i>
                            </button></div>
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

</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
