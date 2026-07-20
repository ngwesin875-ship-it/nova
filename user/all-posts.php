<?php
require_once __DIR__ . '/../includes/posts.php';
include __DIR__ . '/../includes/header.php';

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 12;
$type = $_GET['type'] ?? 'all';
$typeFilter = in_array($type, ['all', 'free', 'premium']) ? $type : 'all';
$posts = getPostsPaginated($page, $limit, $typeFilter, 'published');
$total = getPostsCount($typeFilter, 'published');
$totalPages = $limit > 0 ? (int)ceil($total / $limit) : 0;

$params = $_GET;
unset($params['page']);
$baseUrl = http_build_query($params);

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
?>
<main class="max-w-[1440px] mx-auto p-4 md:p-8 grid grid-cols-1 lg:grid-cols-12 gap-8">
    <div class="lg:col-span-9 space-y-8">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                    <i class="fa-solid fa-newspaper"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900"><?= $typeFilter === 'premium' ? 'Premium News' : ($typeFilter === 'free' ? 'Free News' : 'All News') ?></h1>
                    <p class="text-sm text-slate-500"><?= $total ?> article<?= $total !== 1 ? 's' : '' ?> total</p>
                </div>
            </div>
        </div>

        <?php if (empty($posts)): ?>
        <div class="bg-white rounded-2xl border border-slate-200 p-10 shadow-sm text-center">
            <p class="text-slate-400 text-sm">No articles found.</p>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($posts as $post):
                $type = $post['post_type'];
                $catName = htmlspecialchars($post['category_name'] ?? 'Uncategorized');
                $color = $catColors[$post['category_name'] ?? ''] ?? 'text-slate-600';
                $img = $post['image_url'] ? '/Nova_News/' . htmlspecialchars($post['image_url']) : 'https://images.unsplash.com/photo-1504711434969-e33886168d8c?auto=format&fit=crop&w=200&q=80';
                $title = htmlspecialchars($post['title']);
            ?>
            <a href="article.php?from=all-posts&slug=<?= urlencode($post['slug']) ?>" data-type="<?= $type ?>" class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm flex space-x-4 group cursor-pointer">
                <div class="w-28 h-24 rounded-lg bg-slate-100 overflow-hidden shrink-0">
                    <img src="<?= $img ?>" class="w-full h-full object-cover group-hover:scale-105 transition" alt="<?= $title ?>">
                </div>
                <div class="flex flex-col justify-between flex-1">
                    <div>
                        <span class="text-[10px] font-bold uppercase <?= $color ?>"><?= $catName ?></span>
                        <h4 class="font-bold text-xs text-slate-800 line-clamp-2 mt-0.5 group-hover:text-[#5B41FF]"><?= $title ?></h4>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-400">
                        <?php if ($type === 'premium'): ?>
                            <span class="bg-amber-50 text-amber-700 font-medium flex items-center gap-1 border border-amber-300 px-2 py-0.5 rounded"><i class="fa-solid fa-lock text-[10px]"></i> Premium</span>
                        <?php else: ?>
                            <span class="bg-emerald-50 text-emerald-700 font-medium flex items-center gap-1 border border-emerald-300 px-2 py-0.5 rounded"><span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> Free</span>
                        <?php endif; ?>
                        <span><?= date('M j, Y', strtotime($post['created_at'])) ?></span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="flex justify-center items-center gap-2 pt-4">
            <?php if ($page > 1): ?>
                <a href="?<?= $baseUrl ? $baseUrl . '&' : '' ?>page=<?= $page - 1 ?>" class="px-3 py-2 rounded-lg bg-white border border-slate-200 text-sm text-slate-600 hover:bg-slate-50 transition">&laquo; Previous</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?<?= $baseUrl ? $baseUrl . '&' : '' ?>page=<?= $i ?>" class="px-3 py-2 rounded-lg text-sm <?= $i === $page ? 'bg-[#5B41FF] text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' ?> transition"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a href="?<?= $baseUrl ? $baseUrl . '&' : '' ?>page=<?= $page + 1 ?>" class="px-3 py-2 rounded-lg bg-white border border-slate-200 text-sm text-slate-600 hover:bg-slate-50 transition">Next &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>

    <aside class="lg:col-span-3 space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
            <div class="flex items-center space-x-2 text-amber-500 font-bold text-sm">
                <i class="fa-solid fa-crown"></i>
                <span>PREMIUM ACCESS</span>
            </div>
            <ul class="space-y-2.5 text-xs text-slate-600">
                <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-emerald-500"></i> Unlimited premium articles</li>
                <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-emerald-500"></i> Exclusive analysis & reports</li>
                <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-emerald-500"></i> Early access to breaking news</li>
                <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-emerald-500"></i> Ad-free experience</li>
            </ul>
            <div class="pt-2 border-t border-slate-100">
                <div class="text-[11px] text-slate-400">Starting from</div>
                <div class="text-xl font-extrabold text-slate-950">3,000 MMK <span class="text-xs font-normal text-slate-500">/ Month</span></div>
            </div>
            <a href="subscribe.php" class="block w-full text-center bg-[#5B41FF] hover:bg-[#4830DF] text-white font-semibold text-sm py-3 rounded-xl shadow-md transition">Choose Plan</a>
        </div>

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
                    $s = htmlspecialchars($cat['slug']);
                    $n = htmlspecialchars($cat['name']);
                    $c = (int) $cat['article_count'];
                    $st = $catIcons[$cat['name']] ?? ['bg' => 'bg-slate-100', 'text' => 'text-slate-600', 'icon' => 'fa-folder'];
                ?>
                <a href="/Nova_News/user/category.php?slug=<?= urlencode($cat['slug']) ?>" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition text-xs">
                    <div class="flex items-center gap-2 font-medium text-slate-700"><span class="w-6 h-6 rounded <?= $st['bg'] . ' ' . $st['text'] ?> flex items-center justify-center"><i class="fa-solid <?= $st['icon'] ?> text-[11px]"></i></span> <?= $n ?></div>
                    <span class="text-slate-400"><?= $c ?> Article<?= $c !== 1 ? 's' : '' ?> <i class="fa-solid fa-chevron-right text-[9px] ml-1"></i></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </aside>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
