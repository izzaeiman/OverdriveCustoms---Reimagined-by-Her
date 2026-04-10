<?php
require_once 'inc/db.php';

try {
    echo "Dropping old analytics table...\n";
    $pdo->exec("DROP TABLE IF EXISTS analytics");
    
    echo "Creating new analytics table...\n";
    $pdo->exec("CREATE TABLE analytics (
        id INT AUTO_INCREMENT PRIMARY KEY,
        page_url VARCHAR(255) NOT NULL,
        user_id INT NULL,
        ip_address VARCHAR(45) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    echo "Analytics table fixed successfully!\n";
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
?>
