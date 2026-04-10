<?php
require_once 'inc/db.php';

try {
    // Analytics Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS analytics (
        id INT AUTO_INCREMENT PRIMARY KEY,
        page_url VARCHAR(255) NOT NULL,
        user_id INT NULL,
        ip_address VARCHAR(45) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Analytics table created.<br>";
    
} catch (PDOException $e) {
    die("DB Setup Error: " . $e->getMessage());
}
?>
