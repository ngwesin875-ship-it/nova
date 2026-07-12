<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/subscription.php'; // For getPlanById

function getAllUserSubscriptions(): array
{
    $db = getDB();
    $result = $db->query('
        SELECT us.*, u.username, u.email, sp.name AS plan_name
        FROM user_subscriptions us
        JOIN users u ON us.user_id = u.id
        JOIN subscription_plans sp ON us.plan_id = sp.id
        ORDER BY us.created_at DESC
    ');
    $subs = [];
    if ($result && method_exists($result, 'fetch_assoc')) {
        while ($row = $result->fetch_assoc()) {
            $subs[] = $row;
        }
    }
    return $subs;
}

function getUserSubscriptionById(int $id): ?array
{
    $db = getDB();
    $stmt = $db->prepare('
        SELECT us.*, u.username, u.email
        FROM user_subscriptions us
        JOIN users u ON us.user_id = u.id
        WHERE us.id = ?
    ');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
    }
    return null;
}

function updateUserSubscription(int $id, int $userId, int $planId, string $startDate, string $endDate, string $status, string $paymentStatus): bool
{
    $db = getDB();
    $stmt = $db->prepare('UPDATE user_subscriptions SET user_id = ?, plan_id = ?, start_date = ?, end_date = ?, status = ?, payment_status = ? WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('iissssi', $userId, $planId, $startDate, $endDate, $status, $paymentStatus, $id);
        return $stmt->execute();
    }
    return false;
}

function deleteUserSubscription(int $id): bool
{
    $db = getDB();
    $stmt = $db->prepare('DELETE FROM user_subscriptions WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
    return false;
}

function getAllUsersForSelect(): array
{
    $db = getDB();
    $result = $db->query('SELECT id, username, email FROM users ORDER BY username ASC');
    $users = [];
    if ($result && method_exists($result, 'fetch_assoc')) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    return $users;
}

function getAllPlansForSelect(): array
{
    return getAllPlans();
}
