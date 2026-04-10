<?php
require_once 'inc/db.php';

function checkTable($table) {
    global $pdo;
    try {
        $result = $pdo->query("SELECT 1 FROM $table LIMIT 1");
        echo "$table: Exists\n";
    } catch (PDOException $e) {
        echo "$table: Missing (" . $e->getMessage() . ")\n";
    }
}

checkTable('product_images');
checkTable('product_options');
checkTable('order_items');
?>
