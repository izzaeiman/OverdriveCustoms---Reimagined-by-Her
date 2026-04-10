<?php
require_once 'inc/db.php';

try {
    // Product Images Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        image_url VARCHAR(255) NOT NULL,
        is_primary TINYINT(1) DEFAULT 0,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )");
    echo "Created product_images table.<br>";

    // Product Options Table (Size, Color, etc.)
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_options (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        option_group VARCHAR(50) NOT NULL, -- e.g. 'Size', 'Color'
        option_value VARCHAR(50) NOT NULL, -- e.g. 'M', 'Red'
        price_modifier DECIMAL(10, 2) DEFAULT 0.00,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )");
    echo "Created product_options table.<br>";

    // Update order_items to store selected options
    $stmt = $pdo->query("SHOW COLUMNS FROM order_items LIKE 'options'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE order_items ADD COLUMN options TEXT NULL AFTER price");
        echo "Added 'options' column to order_items.<br>";
    }
    
    // Update cart table to store options (if we are using DB cart)
    $stmt = $pdo->query("SHOW COLUMNS FROM cart LIKE 'options'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE cart ADD COLUMN options TEXT NULL AFTER quantity");
        echo "Added 'options' column to cart.<br>";
    }

    echo "Database updated for product features.";

} catch (PDOException $e) {
    die("DB Update Error: " . $e->getMessage());
}
?>
