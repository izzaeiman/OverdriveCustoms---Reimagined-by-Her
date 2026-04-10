<?php
require 'config.php';
require 'inc/db.php';

try {
    // Expand the ENUM to include new roles
    $sql = "ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'customer', 'manager', 'order_manager', 'media_manager') DEFAULT 'customer'";
    $pdo->exec($sql);
    echo "Successfully updated 'role' column ENUM.";
} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage();
}
?>
