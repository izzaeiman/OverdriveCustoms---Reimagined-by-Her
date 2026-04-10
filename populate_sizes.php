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
    // 1. Fetch all product IDs
    $stmt = $pdo->query("SELECT id FROM products");
    $productIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($productIds)) {
        die("No products found.");
    }

    // 2. Prepare insert statement for product_options
    $stmtInsert = $pdo->prepare("INSERT INTO product_options (product_id, option_group, option_value, price_modifier) VALUES (?, ?, ?, ?)");

    $sizes = ['S', 'M', 'L', 'XL', 'XXL'];

    echo "Adding sizes to " . count($productIds) . " products...\n";

    foreach ($productIds as $pid) {
        // Check if options already exist to avoid duplicates
        $check = $pdo->prepare("SELECT COUNT(*) FROM product_options WHERE product_id = ? AND option_group = 'Size'");
        $check->execute([$pid]);
        if ($check->fetchColumn() > 0) {
            echo "Skipping Product ID $pid (Sizes already exist)\n";
            continue;
        }

        foreach ($sizes as $size) {
            // Price modifier 0 for now
            $stmtInsert->execute([$pid, 'Size', $size, 0.00]);
        }
        echo "Added sizes for Product ID $pid\n";
    }

    echo "Done!\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
