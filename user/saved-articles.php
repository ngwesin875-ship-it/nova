<?php
require_once __DIR__ . '/../includes/posts.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    header('Location: ../public/Signin.php');
    exit;
}

$userId = currentUserId();
include __DIR__ . '/../includes/header.php';

// Fetch saved articles
$db = getDB();
$sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug, u.username AS author_name 
        FROM posts p
        JOIN saved_articles sa ON p.id = sa.post_id
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN users u ON p.created_by = u.id
        WHERE sa.user_id = ?
        ORDER BY sa.created_at DESC";
$stmt = $db->prepare($sql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$posts = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
}

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
                    <i class="fa-solid fa-bookmark"></i>
                </span>
                <div class="flex-1">
                    <h1 class="text-2xl font-extrabold text-slate-900">Saved Articles</h1>
                    <p class="text-sm text-slate-500">You have <?= count($posts) ?> saved article<?= count($posts) !== 1 ? 's' : '' ?></p>
                </div>
            </div>
        </div>

        <?php if (empty($posts)): ?>
        <div class="bg-white rounded-2xl border border-slate-200 p-10 shadow-sm text-center">
            <p class="text-slate-400 text-sm">You haven't saved any articles yet.</p>
        </div>
        <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($posts as $post):
                $type = $post['post_type'] ?? 'free';
                $catName = htmlspecialchars($post['category_name'] ?? 'Uncategorized');
                $color = $catColors[$post['category_name'] ?? ''] ?? 'text-slate-600';
                $img = !empty($post['image_url']) ? '/Nova_News/' . htmlspecialchars($post['image_url']) : 'https://images.unsplash.com/photo-1504711434969-e33886168d8c?auto=format&fit=crop&w=200&q=80';
                $title = htmlspecialchars($post['title']);
            ?>
            <a href="article.php?from=saved-articles&slug=<?= urlencode($post['slug']) ?>" data-type="<?= $type ?>" class="bg-white p-3 rounded-xl border border-slate-100 shadow-sm flex space-x-4 group cursor-pointer relative">
                <div class="w-28 h-24 rounded-lg bg-slate-100 overflow-hidden shrink-0 relative">
                    <img src="<?= $img ?>" class="w-full h-full object-cover group-hover:scale-105 transition" alt="<?= $title ?>">
                    <?php $isSaved = in_array($post['id'], $savedPostIds ?? []); ?>
                    <button type="button" onclick="event.preventDefault(); event.stopPropagation(); toggleBookmark(<?= $post['id'] ?>, this)" class="absolute top-1 right-1 w-7 h-7 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center shadow-sm text-slate-700 hover:text-[#5B41FF] transition z-10">
                        <i class="<?= $isSaved ? 'fa-solid text-[#5B41FF]' : 'fa-regular' ?> fa-bookmark text-xs"></i>
                    </button>
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
        <?php endif; ?>
    </div>
    <aside class="lg:col-span-3 space-y-6">
        <!-- premium access ad block -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
            <div class="flex items-center space-x-2 text-amber-500 font-bold text-sm">
                <i class="fa-solid fa-crown"></i>
                <span>PREMIUM ACCESS</span>
            </div>
            <ul class="space-y-2.5 text-xs text-slate-600">
                <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-emerald-500"></i> Unlimited premium articles</li>
                <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-emerald-500"></i> Exclusive analysis & reports</li>
            </ul>
            <a href="subscribe.php" class="block w-full text-center bg-[#5B41FF] hover:bg-[#4830DF] text-white font-semibold text-sm py-3 rounded-xl shadow-md transition">Choose Plan</a>
        </div>
    </aside>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
