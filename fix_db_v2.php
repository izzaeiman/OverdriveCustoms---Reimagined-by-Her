<?php
require_once 'inc/db.php';

try {
    echo "Checking 'orders' table...<br>";
    
    // Check if total_amount exists
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'total_amount'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN total_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00");
        echo "Added 'total_amount' column.<br>";
    } else {
        echo "'total_amount' already exists.<br>";
    }

    // Check if status exists
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'status'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN status ENUM('Pending', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Pending'");
        echo "Added 'status' column.<br>";
    } else {
        echo "'status' already exists.<br>";
    }

    // Check if shipping_address exists
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'shipping_address'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN shipping_address TEXT NOT NULL");
        echo "Added 'shipping_address' column.<br>";
    } else {
        echo "'shipping_address' already exists.<br>";
    }

    // Check if user_id exists
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'user_id'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN user_id INT NULL"); 
        echo "Added 'user_id' column.<br>";
    } else {
        echo "'user_id' already exists.<br>";
    }
    
    echo "Database repair complete.";

} catch (PDOException $e) {
    die("DB Repair Error: " . $e->getMessage());
}
?>
