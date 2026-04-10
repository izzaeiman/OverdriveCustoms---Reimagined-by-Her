<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';

// Load Cart from DB if logged in
if (isset($_SESSION['user_id']) && !isset($_SESSION['cart_loaded'])) {
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
    
    $stmt = $pdo->prepare("SELECT product_id, quantity FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    while ($row = $stmt->fetch()) {
        $_SESSION['cart'][$row['product_id']] = $row['quantity'];
    }
    $_SESSION['cart_loaded'] = true;
    $_SESSION['cart_loaded'] = true;
}

// Analytics Tracking
if (!isset($_SESSION['admin_viewing'])) { // Don't track admins
    // Create analytics table if it doesn't exist
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS analytics (
            id INT AUTO_INCREMENT PRIMARY KEY,
            page_url VARCHAR(255) NOT NULL,
            user_id INT NULL,
            ip_address VARCHAR(45) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    } catch (PDOException $e) {
        // Table might already exist, continue anyway
    }
    
    $page_url = $_SERVER['REQUEST_URI'];
    $u_id = $_SESSION['user_id'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // Simple debounce: only track if page changed or 5 mins passed (optional, keeping it simple for now: track all hits)
    try {
        $stmt = $pdo->prepare("INSERT INTO analytics (page_url, user_id, ip_address) VALUES (?, ?, ?)");
        $stmt->execute([$page_url, $u_id, $ip]);
    } catch (PDOException $e) {
        // Silently fail analytics tracking to prevent breaking the page
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | ' : ''; ?><?php echo SITE_NAME; ?></title>
    <meta name="description" content="Premium automotive apparel for the driven.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css?v=<?php echo time() + 5; ?>">
</head>
<body>
    <?php include __DIR__ . '/nav.php'; ?>
    <div class="main-wrapper">
