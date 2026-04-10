<?php
require 'config.php';
require 'inc/db.php';

try {
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($columns);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
