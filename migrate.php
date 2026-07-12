<?php
require_once 'config/db.php';

$db = getDB();

// Add missing columns if they don't exist
$result = $db->query("SHOW COLUMNS FROM posts LIKE 'is_featured'");
if ($result && $result->num_rows === 0) {
    $db->query("ALTER TABLE posts ADD COLUMN is_featured TINYINT(1) DEFAULT 0 AFTER status");
    echo "Added is_featured column\n";
}

$result = $db->query("SHOW COLUMNS FROM posts LIKE 'view_count'");
if ($result && $result->num_rows === 0) {
    $db->query("ALTER TABLE posts ADD COLUMN view_count INT DEFAULT 0 AFTER is_featured");
    echo "Added view_count column\n";
}

echo "Database update complete!\n";
?>
