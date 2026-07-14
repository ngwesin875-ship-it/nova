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
    echo json_encode(['error' => 'You must be signed in to vote.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$postId = (int) ($input['post_id'] ?? 0);
$action = $input['action'] ?? '';

if ($postId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid post ID.']);
    exit;
}

$post = getPostById($postId);
if (!$post) {
    http_response_code(404);
    echo json_encode(['error' => 'Post not found.']);
    exit;
}

$userId = currentUserId();

if ($action === 'like') {
    $result = toggleLike($postId, $userId);
} elseif ($action === 'dislike') {
    $result = toggleDislike($postId, $userId);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action. Use "like" or "dislike".']);
    exit;
}

echo json_encode([
    'success'  => true,
    'user_vote' => $result['user_vote'],
    'likes'     => $result['likes'],
    'dislikes'  => $result['dislikes'],
]);
