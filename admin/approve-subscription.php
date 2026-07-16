<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/middleware.php';
require_once __DIR__ . '/../includes/subscription.php';
require_once __DIR__ . '/../includes/notifications.php';
require_once __DIR__ . '/../config/session.php';

requireAdmin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$action = $_POST['action'] ?? '';
$subId = (int) ($_POST['subscription_id'] ?? 0);

if ($subId <= 0 || !in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
    exit;
}

$db = getDB();

$stmt = $db->prepare('SELECT us.*, u.username, u.email, sp.name AS plan_name FROM user_subscriptions us JOIN users u ON us.user_id = u.id JOIN subscription_plans sp ON us.plan_id = sp.id WHERE us.id = ?');
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
    exit;
}
$stmt->bind_param('i', $subId);
$stmt->execute();
$sub = $stmt->get_result()->fetch_assoc();

if (!$sub) {
    echo json_encode(['success' => false, 'message' => 'Subscription not found.']);
    exit;
}

if ($action === 'approve') {
    $updateSub = $db->prepare('UPDATE user_subscriptions SET payment_status = "paid", status = "active" WHERE id = ?');
    if ($updateSub) {
        $updateSub->bind_param('i', $subId);
        $updateSub->execute();
    }

    $updatePay = $db->prepare('UPDATE payments SET status = "success" WHERE subscription_id = ?');
    if ($updatePay) {
        $updatePay->bind_param('i', $subId);
        $updatePay->execute();
    }

    createNotification(
        'subscription_approved',
        'Subscription Approved',
        htmlspecialchars($sub['username']) . '\'s ' . htmlspecialchars($sub['plan_name']) . ' subscription has been approved.',
        $subId,
        'user_subscriptions'
    );

    echo json_encode(['success' => true, 'message' => 'Subscription approved successfully.']);
} else {
    $updateSub = $db->prepare('UPDATE user_subscriptions SET payment_status = "failed", status = "cancelled" WHERE id = ?');
    if ($updateSub) {
        $updateSub->bind_param('i', $subId);
        $updateSub->execute();
    }

    $updatePay = $db->prepare('UPDATE payments SET status = "failed" WHERE subscription_id = ?');
    if ($updatePay) {
        $updatePay->bind_param('i', $subId);
        $updatePay->execute();
    }

    createNotification(
        'subscription_rejected',
        'Subscription Rejected',
        htmlspecialchars($sub['username']) . '\'s ' . htmlspecialchars($sub['plan_name']) . ' subscription has been rejected.',
        $subId,
        'user_subscriptions'
    );

    echo json_encode(['success' => true, 'message' => 'Subscription rejected.']);
}
