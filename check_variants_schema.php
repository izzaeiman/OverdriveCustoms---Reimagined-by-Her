<?php
require_once 'config.php';
// We need to check products and cart tables
try {
    echo "--- PRODUCTS TABLE ---\n";
    $stmt = $pdo->query("DESCRIBE products");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    
    echo "\n--- CART TABLE ---\n";
    $stmt = $pdo->query("DESCRIBE cart");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    
    echo "\n--- ORDER_ITEMS TABLE ---\n";
    $stmt = $pdo->query("DESCRIBE order_items");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
