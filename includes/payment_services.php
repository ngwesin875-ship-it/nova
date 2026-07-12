<?php
require_once __DIR__ . '/../config/db.php';

function getAllPaymentServices(): array
{
    $db = getDB();
    $result = $db->query('SELECT * FROM payment_services ORDER BY id ASC');
    $services = [];
    if ($result && method_exists($result, 'fetch_assoc')) {
        while ($row = $result->fetch_assoc()) {
            $services[] = $row;
        }
    }
    return $services;
}

function getPaymentServiceById(int $id): ?array
{
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM payment_services WHERE id = ?');
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

function getPaymentServiceByName(string $name): ?array
{
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM payment_services WHERE name = ?');
    if ($stmt) {
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
    }
    return null;
}

function createPaymentService(string $name, string $displayName, string $phoneNumber, string $logoImage, string $accountName, ?string $qrImage, int $isActive): int|false
{
    $db = getDB();
    $stmt = $db->prepare('INSERT INTO payment_services (name, display_name, phone_number, logo_image, account_name, qr_image, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)');
    if ($stmt) {
        $stmt->bind_param('ssssssi', $name, $displayName, $phoneNumber, $logoImage, $accountName, $qrImage, $isActive);
        if ($stmt->execute()) {
            return $db->insert_id;
        }
    }
    return false;
}

function updatePaymentService(int $id, string $displayName, string $phoneNumber, string $logoImage, string $accountName, ?string $qrImage, int $isActive): bool
{
    $db = getDB();
    if ($qrImage !== null) {
        $stmt = $db->prepare('UPDATE payment_services SET display_name = ?, phone_number = ?, logo_image = ?, account_name = ?, qr_image = ?, is_active = ? WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('sssssii', $displayName, $phoneNumber, $logoImage, $accountName, $qrImage, $isActive, $id);
            return $stmt->execute();
        }
    } else {
        $stmt = $db->prepare('UPDATE payment_services SET display_name = ?, phone_number = ?, logo_image = ?, account_name = ?, is_active = ? WHERE id = ?');
        if ($stmt) {
            $stmt->bind_param('ssssii', $displayName, $phoneNumber, $logoImage, $accountName, $isActive, $id);
            return $stmt->execute();
        }
    }
    return false;
}

function togglePaymentServiceActive(int $id): bool
{
    $db = getDB();
    $svc = getPaymentServiceById($id);
    if (!$svc) return false;
    $newStatus = (int) $svc['is_active'] ? 0 : 1;
    $stmt = $db->prepare('UPDATE payment_services SET is_active = ? WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('ii', $newStatus, $id);
        return $stmt->execute();
    }
    return false;
}

function deletePaymentService(int $id): bool
{
    $db = getDB();
    $stmt = $db->prepare('DELETE FROM payment_services WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
    return false;
}
