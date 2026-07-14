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
    echo json_encode(['error' => 'You must be signed in.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$commentId = (int) ($input['comment_id'] ?? 0);
$postId    = (int) ($input['post_id'] ?? 0);

if ($commentId <= 0 || $postId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid parameters.']);
    exit;
}

$deleted = deleteComment($commentId, currentUserId());

if ($deleted) {
    $comments = getCommentsByPost($postId);
    ob_start();
    renderComments($comments, $postId);
    $html = ob_get_clean();

    echo json_encode([
        'success'       => true,
        'comment_count' => getCommentCount($postId),
        'html'          => $html,
    ]);
} else {
    http_response_code(403);
    echo json_encode(['error' => 'Could not delete comment.']);
}
