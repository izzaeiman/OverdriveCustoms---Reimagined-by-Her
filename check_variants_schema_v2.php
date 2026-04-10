<?php
// Fix connection include
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

try {
    echo "--- PRODUCTS TABLE ---\n";
    $stmt = $pdo->query("DESCRIBE products");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    
    echo "\n--- PRODUCT_OPTIONS TABLE ---\n";
    $stmt = $pdo->query("DESCRIBE product_options"); // Check if this table exists
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
