<?php
require_once 'inc/db.php';

function describeTable($pdo, $table) {
    echo "Table: $table\n";
    $stmt = $pdo->query("DESCRIBE $table");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    echo "\n";
}

describeTable($pdo, 'products');
describeTable($pdo, 'order_items');
?>
