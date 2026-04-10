<?php
// Since config.php might not be creating $pdo in the global scope correctly or I missed it.
// I will copy the connection logic directly or fix the include.
// Let's first look at config.php from the view_file result, but if I can't wait, I'll try to just dump everything.

require 'config.php';
global $pdo; // explicit global declaration might help if it's inside a function? unlikely for simple config.

if (!isset($pdo)) {
    // Fallback: try to create it if config didn't
    $host = 'localhost';
    $db   = 'overdrive';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

try {
    $stmt = $pdo->query("DESCRIBE orders");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($columns);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
