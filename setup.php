<?php
// Simple setup script to add missing database columns
// Access this via: http://localhost/Nova_News/setup.php

session_start();
require_once __DIR__ . '/config/db.php';

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'migrate') {
    $db = getDB();
    
    // Check and add is_featured column
    $result = $db->query("SHOW COLUMNS FROM posts LIKE 'is_featured'");
    if ($result && $result->num_rows === 0) {
        if ($db->query("ALTER TABLE posts ADD COLUMN is_featured TINYINT(1) DEFAULT 0 AFTER status")) {
            $message .= "✓ Added is_featured column\n";
        } else {
            $message .= "✗ Failed to add is_featured column: " . $db->error . "\n";
        }
    } else {
        $message .= "✓ is_featured column already exists\n";
    }
    
    // Check and add view_count column
    $result = $db->query("SHOW COLUMNS FROM posts LIKE 'view_count'");
    if ($result && $result->num_rows === 0) {
        if ($db->query("ALTER TABLE posts ADD COLUMN view_count INT DEFAULT 0 AFTER is_featured")) {
            $message .= "✓ Added view_count column\n";
        } else {
            $message .= "✗ Failed to add view_count column: " . $db->error . "\n";
        }
    } else {
        $message .= "✓ view_count column already exists\n";
    }
    
    $success = true;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Nova News Setup</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .container { border: 1px solid #ddd; padding: 20px; border-radius: 5px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .message { margin-top: 20px; padding: 10px; background: #f0f0f0; border-left: 4px solid #28a745; white-space: pre-wrap; font-family: monospace; }
        .success { border-left-color: #28a745; background: #f0fff4; }
        h1 { color: #333; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Nova News Database Setup</h1>
        <p>This script will add missing columns to the posts table.</p>
        
        <form method="POST">
            <input type="hidden" name="action" value="migrate">
            <button type="submit">Run Migration</button>
        </form>
        
        <?php if ($success && !empty($message)): ?>
        <div class="message success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
