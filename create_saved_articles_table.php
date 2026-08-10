<?php
require_once 'config/db.php';
$db = getDB();

$sql = "CREATE TABLE IF NOT EXISTS saved_articles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    post_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    UNIQUE KEY user_post_unique (user_id, post_id)
)";

if ($db->query($sql) === TRUE) {
    echo "Table saved_articles created successfully";
} else {
    echo "Error creating table: " . $db->error;
}
?>
