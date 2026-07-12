<?php
require_once __DIR__ . '/../config/db.php';

function getAllPayments(): array
{
    $db = getDB();
    $result = $db->query('
        SELECT p.*, us.user_id, us.plan_id, u.username, sp.name AS plan_name
        FROM payments p
        JOIN user_subscriptions us ON p.subscription_id = us.id
        JOIN users u ON us.user_id = u.id
        JOIN subscription_plans sp ON us.plan_id = sp.id
        ORDER BY p.paid_at DESC
    ');
    $payments = [];
    if ($result && method_exists($result, 'fetch_assoc')) {
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }
    }
    return $payments;
}

function getPaymentById(int $id): ?array
{
    $db = getDB();
    $stmt = $db->prepare('
        SELECT p.*, us.user_id, us.plan_id, u.username, sp.name AS plan_name
        FROM payments p
        JOIN user_subscriptions us ON p.subscription_id = us.id
        JOIN users u ON us.user_id = u.id
        JOIN subscription_plans sp ON us.plan_id = sp.id
        WHERE p.id = ?
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

function createPayment(int $subscriptionId, float $amount, string $paymentMethod, string $accountName, string $accountPhone, string $receiptImage, string $status): int|false
{
    $db = getDB();
    $stmt = $db->prepare('INSERT INTO payments (subscription_id, amount, payment_method, account_name, account_phone, receipt_image, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
    if ($stmt) {
        $stmt->bind_param('idsssss', $subscriptionId, $amount, $paymentMethod, $accountName, $accountPhone, $receiptImage, $status);
        if ($stmt->execute()) {
            return $db->insert_id;
        }
    }
    return false;
}

function updatePayment(int $id, int $subscriptionId, float $amount, string $paymentMethod, string $accountName, string $accountPhone, ?string $receiptImage, string $status): bool
{
    $db = getDB();
    if ($receiptImage !== null) {
        $stmt = $db->prepare('UPDATE payments SET subscription_id = ?, amount = ?, payment_method = ?, account_name = ?, account_phone = ?, receipt_image = ?, status = ? WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('idsssssi', $subscriptionId, $amount, $paymentMethod, $accountName, $accountPhone, $receiptImage, $status, $id);
            return $stmt->execute();
        }
    } else {
        $stmt = $db->prepare('UPDATE payments SET subscription_id = ?, amount = ?, payment_method = ?, account_name = ?, account_phone = ?, status = ? WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('idssssi', $subscriptionId, $amount, $paymentMethod, $accountName, $accountPhone, $status, $id);
            return $stmt->execute();
        }
    }
    return false;
}

function deletePayment(int $id): bool
{
    $db = getDB();
    $stmt = $db->prepare('DELETE FROM payments WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
    return false;
}

function getUserPayments(int $userId): array
{
    $db = getDB();
    $stmt = $db->prepare('
        SELECT p.*, sp.name AS plan_name
        FROM payments p
        JOIN user_subscriptions us ON p.subscription_id = us.id
        JOIN subscription_plans sp ON us.plan_id = sp.id
        WHERE us.user_id = ?
        ORDER BY p.paid_at DESC
    ');
    $payments = [];
    if ($stmt) {
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && method_exists($result, 'fetch_assoc')) {
            while ($row = $result->fetch_assoc()) {
                $payments[] = $row;
            }
        }
    }
    return $payments;
}

function getUserSubscriptions(): array
{
    $db = getDB();
    $result = $db->query('
        SELECT us.id, us.user_id, us.plan_id, u.username, sp.name AS plan_name
        FROM user_subscriptions us
        JOIN users u ON us.user_id = u.id
        JOIN subscription_plans sp ON us.plan_id = sp.id
        ORDER BY us.id DESC
    ');
    $subscriptions = [];
    if ($result && method_exists($result, 'fetch_assoc')) {
        while ($row = $result->fetch_assoc()) {
            $subscriptions[] = $row;
        }
    }
    return $subscriptions;
}
