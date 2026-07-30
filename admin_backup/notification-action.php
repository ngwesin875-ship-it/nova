<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/notifications.php';
require_once __DIR__ . '/../config/session.php';

requireAdmin();

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'mark_read') {
    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        markNotificationRead($id);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
} elseif ($action === 'mark_all_read') {
    markAllNotificationsRead();
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
