<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/posts.php';
require_once __DIR__ . '/../includes/interactions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'You must be signed in to comment.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$postId   = (int) ($input['post_id'] ?? 0);
$content  = trim($input['content'] ?? '');
$parentId = !empty($input['parent_id']) ? (int) $input['parent_id'] : null;

if ($postId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid post ID.']);
    exit;
}

if ($content === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Comment cannot be empty.']);
    exit;
}

if (mb_strlen($content) > 2000) {
    http_response_code(400);
    echo json_encode(['error' => 'Comment must be 2000 characters or fewer.']);
    exit;
}

$post = getPostById($postId);
if (!$post) {
    http_response_code(404);
    echo json_encode(['error' => 'Post not found.']);
    exit;
}

$commentId = addComment($postId, currentUserId(), $content, $parentId);

if ($commentId) {
    $comments = getCommentsByPost($postId);
    ob_start();
    renderComments($comments, $postId);
    $html = ob_get_clean();

    echo json_encode([
        'success' => true,
        'comment_count' => getCommentCount($postId),
        'html' => $html,
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to post comment.']);
}
