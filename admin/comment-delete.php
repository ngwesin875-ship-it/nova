<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/interactions.php';
require_once __DIR__ . '/../config/session.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: comments.php');
    exit;
}

if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    header('Location: comments.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    flashMessage('error', 'Invalid comment ID.');
    header('Location: comments.php');
    exit;
}

if (deleteCommentAdmin($id)) {
    flashMessage('success', 'Comment deleted successfully.');
} else {
    flashMessage('error', 'Failed to delete comment.');
}

header('Location: comments.php');
exit;
