<?php
require_once 'inc/db.php';

try {
    echo "Creating site_settings table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(50) NOT NULL UNIQUE,
        setting_value TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Insert default values if needed, e.g. logo
    echo "Seeding default settings...\n";
    $stmt = $pdo->prepare("INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
    $stmt->execute(['logo_icon', 'assets/img/logo-small.png']);
    
    echo "site_settings table created and seeded successfully!\n";
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
?>
