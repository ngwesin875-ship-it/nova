<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/posts.php';
require_once __DIR__ . '/../config/session.php';

requireAdmin();

$db = getDB();

$type = $_GET['type'] ?? 'all';
$status = $_GET['status'] ?? 'all';
$search = trim($_GET['search'] ?? '');

$conditions = [];
$params = [];
$types = '';

if ($type !== 'all') {
    $conditions[] = 'p.post_type = ?';
    $params[] = $type;
    $types .= 's';
}

if ($status !== 'all') {
    $conditions[] = 'p.status = ?';
    $params[] = $status;
    $types .= 's';
}

if ($search !== '') {
    $conditions[] = 'p.title LIKE ?';
    $params[] = '%' . $search . '%';
    $types .= 's';
}

$sql = 'SELECT p.id, p.title, p.slug, p.content, c.name AS category_name, u.username AS author_name, p.post_type, p.status, p.created_at
        FROM posts p
        LEFT JOIN users u ON p.created_by = u.id
        LEFT JOIN categories c ON p.category_id = c.id';

if (!empty($conditions)) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}

$sql .= ' ORDER BY p.created_at DESC';

$stmt = $db->prepare($sql);
if ($stmt && !empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$posts = [];
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && method_exists($result, 'fetch_assoc')) {
        while ($row = $result->fetch_assoc()) {
            $posts[] = $row;
        }
    }
}

$filename = 'nova_news_posts_' . date('Y-m-d_H-i-s') . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

fputcsv($output, ['ID', 'Title', 'Slug', 'Content', 'Category', 'Author', 'Type', 'Status', 'Created At']);

foreach ($posts as $post) {
    $content = strip_tags(html_entity_decode($post['content'] ?? '', ENT_QUOTES, 'UTF-8'));
    $content = preg_replace('/[\r\n]+/', ' ', $content);
    $content = preg_replace('/\s+/', ' ', $content);
    $content = trim($content);

    fputcsv($output, [
        (int) $post['id'],
        $post['title'],
        $post['slug'] ?? '',
        $content,
        $post['category_name'] ?? '-',
        $post['author_name'] ?? '-',
        ucfirst($post['post_type'] ?? 'free'),
        ucfirst($post['status'] ?? 'draft'),
        date('M j, Y', strtotime($post['created_at'])),
    ]);
}

fclose($output);
exit;
