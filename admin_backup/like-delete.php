<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/interactions.php';
require_once __DIR__ . '/../config/session.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: likes.php');
    exit;
}

if (!verifyCsrf($_POST['csrf_token'] ?? '')) {
    flashMessage('error', 'Invalid CSRF token.');
    header('Location: likes.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    flashMessage('error', 'Invalid vote ID.');
    header('Location: likes.php');
    exit;
}

if (deleteLikeDislike($id)) {
    flashMessage('success', 'Vote deleted successfully.');
} else {
    flashMessage('error', 'Failed to delete vote.');
}

header('Location: likes.php');
exit;
