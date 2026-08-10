<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
if ($post_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid post ID']);
    exit;
}

$user_id = currentUserId();
$db = getDB();

// Check if already saved
$stmt = $db->prepare('SELECT id FROM saved_articles WHERE user_id = ? AND post_id = ?');
$stmt->bind_param('ii', $user_id, $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    // Unsave
    $delStmt = $db->prepare('DELETE FROM saved_articles WHERE user_id = ? AND post_id = ?');
    $delStmt->bind_param('ii', $user_id, $post_id);
    if ($delStmt->execute()) {
        echo json_encode(['status' => 'success', 'action' => 'unsaved']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
} else {
    // Save
    $insStmt = $db->prepare('INSERT INTO saved_articles (user_id, post_id) VALUES (?, ?)');
    $insStmt->bind_param('ii', $user_id, $post_id);
    if ($insStmt->execute()) {
        echo json_encode(['status' => 'success', 'action' => 'saved']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
}
