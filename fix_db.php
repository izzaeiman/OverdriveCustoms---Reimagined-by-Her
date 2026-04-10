<?php
require_once 'inc/db.php';

try {
    echo "Checking 'orders' table...<br>";
    
    // Check if total_amount exists
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'total_amount'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN total_amount DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER user_id");
        echo "Added 'total_amount' column.<br>";
    }

    // Check if status exists
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'status'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN status ENUM('Pending', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Pending' AFTER total_amount");
        echo "Added 'status' column.<br>";
    }

    // Check if shipping_address exists
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'shipping_address'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE orders ADD COLUMN shipping_address TEXT NOT NULL AFTER status");
        echo "Added 'shipping_address' column.<br>";
    }

    // Check if user_id exists (it should, but good to be safe if it was an old table)
    $stmt = $pdo->query("SHOW COLUMNS FROM orders LIKE 'user_id'");
    if (!$stmt->fetch()) {
        // If user_id is missing, this is a very old table. Let's add it.
        // Note: This might fail if there are existing rows without user_ids, but we'll try.
        $pdo->exec("ALTER TABLE orders ADD COLUMN user_id INT NULL AFTER id"); 
        echo "Added 'user_id' column.<br>";
    }
    
    // Also check order_items just in case
    echo "Checking 'order_items' table...<br>";
    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )");
    echo "Order items table checked/created.<br>";

    echo "Database repair complete.";

} catch (PDOException $e) {
    die("DB Repair Error: " . $e->getMessage());
}
?>
