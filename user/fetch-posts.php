<?php
require_once __DIR__ . '/../includes/posts.php';

header('Content-Type: application/json');

$type = $_GET['type'] ?? 'all';
if (!in_array($type, ['all', 'free', 'premium'])) {
    $type = 'all';
}

$posts = getPostsPaginated(1, 4, $type, 'published');

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

ob_start();
foreach ($posts as $post):
    $typeLabel = $post['post_type'];
    $catName = htmlspecialchars($post['category_name'] ?? 'Uncategorized');
    $color = $catColors[$post['category_name'] ?? ''] ?? 'text-slate-600';
    $img = $post['image_url'] ? '/Nova_News/' . htmlspecialchars($post['image_url']) : 'https://images.unsplash.com/photo-1504711434969-e33886168d8c?auto=format&fit=crop&w=200&q=80';
    $title = htmlspecialchars($post['title']);
?>
<a href="article.php?slug=<?= urlencode($post['slug']) ?>" data-type="<?= $typeLabel ?>" class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm flex space-x-4 group cursor-pointer">
    <div class="w-28 h-24 rounded-lg bg-slate-100 overflow-hidden shrink-0">
        <img src="<?= $img ?>" class="w-full h-full object-cover group-hover:scale-105 transition" alt="<?= $title ?>">
    </div>
    <div class="flex flex-col justify-between flex-1">
        <div>
            <span class="text-[10px] font-bold uppercase <?= $color ?>"><?= $catName ?></span>
            <h4 class="font-bold text-xs text-slate-800 line-clamp-2 mt-0.5 group-hover:text-[#5B41FF]"><?= $title ?></h4>
        </div>
        <div class="flex items-center justify-between text-[11px] text-slate-400">
            <?php if ($typeLabel === 'premium'): ?>
                <span class="bg-amber-50 text-amber-700 font-medium flex items-center gap-1 border border-amber-300 px-2 py-0.5 rounded"><i class="fa-solid fa-lock text-[10px]"></i> Premium</span>
            <?php else: ?>
                <span class="bg-emerald-50 text-emerald-700 font-medium flex items-center gap-1 border border-emerald-300 px-2 py-0.5 rounded"><span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> Free</span>
            <?php endif; ?>
            <span><?= date('M j, Y', strtotime($post['created_at'])) ?></span>
        </div>
    </div>
</a>
<?php endforeach; ?>
<?php
$html = ob_get_clean();

echo json_encode([
    'html'  => $html,
    'count' => count($posts),
]);
