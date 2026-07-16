<?php
require_once __DIR__ . '/../config/db.php';

function getNotificationCounts(): array
{
    $db = getDB();
    $counts = ['pending_payments' => 0, 'draft_posts' => 0, 'new_users' => 0];

    $r = $db->query("SELECT COUNT(*) AS c FROM payments WHERE status = 'pending'");
    if ($r) $counts['pending_payments'] = (int) ($r->fetch_assoc()['c'] ?? 0);

    $r = $db->query("SELECT COUNT(*) AS c FROM posts WHERE status = 'draft'");
    if ($r) $counts['draft_posts'] = (int) ($r->fetch_assoc()['c'] ?? 0);

    $r = $db->query("SELECT COUNT(*) AS c FROM users WHERE created_at >= NOW() - INTERVAL 7 DAY");
    if ($r) $counts['new_users'] = (int) ($r->fetch_assoc()['c'] ?? 0);

    return $counts;
}

function createNotification(string $type, string $title, string $message, ?int $referenceId = null, ?string $referenceType = null): int|false
{
    $db = getDB();
    $stmt = $db->prepare('INSERT INTO notifications (type, title, message, reference_id, reference_type) VALUES (?, ?, ?, ?, ?)');
    if ($stmt) {
        $stmt->bind_param('ssssi', $type, $title, $message, $referenceId, $referenceType);
        if ($stmt->execute()) {
            return $db->insert_id;
        }
    }
    return false;
}

function getUnreadNotifications(int $limit = 20): array
{
    $db = getDB();
    $limitVal = (int) $limit;
    $stmt = $db->prepare('SELECT * FROM notifications WHERE is_read = 0 ORDER BY created_at DESC LIMIT ?');
    if ($stmt) {
        $stmt->bind_param('i', $limitVal);
        $stmt->execute();
        $result = $stmt->get_result();
        $notifs = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $notifs[] = $row;
            }
        }
        return $notifs;
    }
    return [];
}

function getUnreadNotificationCount(): int
{
    $db = getDB();
    $r = $db->query("SELECT COUNT(*) AS c FROM notifications WHERE is_read = 0");
    if ($r) return (int) ($r->fetch_assoc()['c'] ?? 0);
    return 0;
}

function markNotificationRead(int $id): bool
{
    $db = getDB();
    $stmt = $db->prepare('UPDATE notifications SET is_read = 1 WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
    return false;
}

function markAllNotificationsRead(): bool
{
    $db = getDB();
    $result = $db->query("UPDATE notifications SET is_read = 1 WHERE is_read = 0");
    return $result !== false;
}

function getPendingSubscriptionCount(): int
{
    $db = getDB();
    $r = $db->query("SELECT COUNT(*) AS c FROM user_subscriptions WHERE payment_status = 'pending'");
    if ($r) return (int) ($r->fetch_assoc()['c'] ?? 0);
    return 0;
}
