<?php
require_once __DIR__ . '/../config/db.php';

function getCategories(): array
{
    $db = getDB();
    $result = $db->query('SELECT * FROM categories ORDER BY name ASC');
    $categories = [];
    if ($result && method_exists($result, 'fetch_assoc')) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }
    return $categories;
}

function getCategoryById(int $id): ?array
{
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM categories WHERE id = ?');
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

function createCategory(string $name, string $slug): bool
{
    $db = getDB();
    $stmt = $db->prepare('INSERT INTO categories (name, slug) VALUES (?, ?)');
    if ($stmt) {
        $stmt->bind_param('ss', $name, $slug);
        return $stmt->execute();
    }
    return false;
}

function updateCategory(int $id, string $name, string $slug): bool
{
    $db = getDB();
    $stmt = $db->prepare('UPDATE categories SET name = ?, slug = ? WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('ssi', $name, $slug, $id);
        return $stmt->execute();
    }
    return false;
}

function deleteCategory(int $id): bool
{
    $db = getDB();
    $stmt = $db->prepare('DELETE FROM categories WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
    return false;
}

function getCategoryBySlug(string $slug): ?array
{
    $db = getDB();
    $stmt = $db->prepare('SELECT * FROM categories WHERE slug = ?');
    if ($stmt) {
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            return $result->fetch_assoc();
        }
    }
    return null;
}

function slugify(string $text): string
{
    $text = mb_strtolower(trim($text), 'UTF-8');
    $text = str_replace(['ä', 'ö', 'ü', 'ß'], ['ae', 'oe', 'ue', 'ss'], $text);
    $text = preg_replace('/[^a-z0-9-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}
