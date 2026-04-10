<?php
require_once 'inc/db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Login required']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'] ?? null;

if (!$product_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

// Check if exists
$stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
$stmt->execute([$user_id, $product_id]);
$exists = $stmt->fetch();

if ($exists) {
    // Remove
    $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    echo json_encode(['success' => true, 'action' => 'removed']);
} else {
    // Add
    $stmt = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $product_id]);
    echo json_encode(['success' => true, 'action' => 'added']);
}
?>
