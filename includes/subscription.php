<?php
require_once __DIR__ . '/../config/db.php';

function getAllPlans(): array
{
    $db = getDB();
    $result = $db->query('SELECT * FROM subscription_plans ORDER BY duration_months ASC');
    $plans = [];
    if ($result && method_exists($result, 'fetch_assoc')) {
        while ($row = $result->fetch_assoc()) {
            $plans[] = $row;
        }
    }
    return $plans;
}

function getPlanById(int $id): ?array
{
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM subscription_plans WHERE id = ?');
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

function createPlan(string $name, int $durationMonths, float $price, float $discountPercentage, float $finalPrice, int $isActive): int|false
{
    $db = getDB();
    $stmt = $db->prepare('INSERT INTO subscription_plans (name, duration_months, price, discount_percentage, final_price, is_active) VALUES (?, ?, ?, ?, ?, ?)');
    if ($stmt) {
        $stmt->bind_param('sidddi', $name, $durationMonths, $price, $discountPercentage, $finalPrice, $isActive);
        if ($stmt->execute()) {
            return $db->insert_id;
        }
    }
    return false;
}

function updatePlan(int $id, string $name, int $durationMonths, float $price, float $discountPercentage, float $finalPrice, int $isActive): bool
{
    $db = getDB();
    $stmt = $db->prepare('UPDATE subscription_plans SET name = ?, duration_months = ?, price = ?, discount_percentage = ?, final_price = ?, is_active = ? WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('sidddii', $name, $durationMonths, $price, $discountPercentage, $finalPrice, $isActive, $id);
        return $stmt->execute();
    }
    return false;
}

function deletePlan(int $id): bool
{
    $db = getDB();
    $stmt = $db->prepare('DELETE FROM subscription_plans WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
    return false;
}

function createUserSubscription(int $userId, int $planId, ?string $startDate = null, ?string $endDate = null, ?string $status = null, ?string $paymentStatus = null): int|false
{
    $db = getDB();
    $plan = getPlanById($planId);
    if (!$plan) return false;

    $startDate ??= date('Y-m-d');
    $endDate ??= date('Y-m-d', strtotime("+{$plan['duration_months']} months"));
    $status ??= 'active';
    $paymentStatus ??= 'pending';

    $stmt = $db->prepare('INSERT INTO user_subscriptions (user_id, plan_id, start_date, end_date, status, payment_status) VALUES (?, ?, ?, ?, ?, ?)');
    if ($stmt) {
        $stmt->bind_param('iissss', $userId, $planId, $startDate, $endDate, $status, $paymentStatus);
        if ($stmt->execute()) {
            return $db->insert_id;
        }
    }
    return false;
}

function getUserSubscription(int $userId): ?array
{
    $db = getDB();
    $stmt = $db->prepare('
        SELECT us.*, sp.name AS plan_name, sp.duration_months, sp.final_price
        FROM user_subscriptions us
        JOIN subscription_plans sp ON us.plan_id = sp.id
        WHERE us.user_id = ?
        ORDER BY us.created_at DESC
        LIMIT 1
    ');
    if ($stmt) {
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
    }
    return null;
}

function getActiveSubscription(?int $userId)
{
    $sub = $userId ? getUserSubscription($userId) : null;
    if ($sub && $sub['status'] === 'active' && ($sub['payment_status'] ?? '') === 'paid') {
        return $sub;
    }
    return null;
}

function getSubscriptionStats(): array
{
    $db = getDB();
    $stats = ['total' => 0, 'active_count' => 0, 'total_revenue' => 0.0];

    $result = $db->query("SELECT COUNT(*) AS total_subs, SUM(CASE WHEN status = 'active' AND payment_status = 'paid' THEN 1 ELSE 0 END) AS active_count FROM user_subscriptions");
    if ($result && method_exists($result, 'fetch_assoc')) {
        $row = $result->fetch_assoc();
        if (is_array($row)) {
            $stats['total'] = (int) ($row['total_subs'] ?? 0);
            $stats['active_count'] = (int) ($row['active_count'] ?? 0);
        }
    }

    $paymentResult = $db->query("SELECT COALESCE(SUM(amount), 0) AS total_revenue FROM payments WHERE status = 'success'");
    if ($paymentResult && method_exists($paymentResult, 'fetch_assoc')) {
        $row = $paymentResult->fetch_assoc();
        if (is_array($row)) {
            $stats['total_revenue'] = (float) ($row['total_revenue'] ?? 0);
        }
    }

    return $stats;
}
