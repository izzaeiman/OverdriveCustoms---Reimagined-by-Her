<?php
$pageTitle = "Order Details";
require_once __DIR__ . '/../inc/header.php';
require_once __DIR__ . '/../includes/session_check.php';

$order_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    echo "<div class='container' style='padding: 4rem 0;'><p>Order not found.</p></div>";
    require_once __DIR__ . '/../inc/footer.php';
    exit;
}

$stmt = $pdo->prepare("
    SELECT oi.*, p.title, p.image_url 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();
?>

<div class="container" style="padding: 4rem 0;">
    <div style="margin-bottom: 2rem;">
        <a href="orders.php" style="color: #888; text-decoration: none;">&larr; Back to Orders</a>
    </div>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="color: #e10600;">Order #<?php echo $order['id']; ?></h1>
        <span style="
            padding: 4px 8px; 
            border-radius: 4px; 
            background: <?php 
                echo match($order['status']) {
                    'Delivered' => '#28a745',
                    'Shipped' => '#007bff',
                    'Cancelled' => '#dc3545',
                    default => '#ffc107'
                };
            ?>;
            color: <?php echo $order['status'] === 'Pending' ? '#000' : '#fff'; ?>;
        ">
            <?php echo htmlspecialchars($order['status']); ?>
        </span>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 4rem;">
        <!-- Items -->
        <div>
            <h3 style="margin-bottom: 1rem;">Items</h3>
            <div style="background: #1a1a1a; border-radius: 8px; overflow: hidden;">
                <?php foreach ($items as $item): ?>
                    <div style="display: flex; align-items: center; padding: 1rem; border-bottom: 1px solid #333;">
                        <img src="<?php echo BASE_URL . htmlspecialchars($item['image_url'] ?: 'assets/img/placeholder.jpg'); ?>" 
                             style="width: 60px; height: 60px; object-fit: cover; margin-right: 1rem;">
                        <div style="flex: 1;">
                            <h4 style="margin: 0; font-size: 1rem;"><?php echo htmlspecialchars($item['title']); ?></h4>
                            <p style="color: #888; margin: 0;">Qty: <?php echo $item['quantity']; ?></p>
                        </div>
                        <span style="font-weight: bold;">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Summary -->
        <div>
            <h3 style="margin-bottom: 1rem;">Summary</h3>
            <div style="background: #1a1a1a; padding: 1.5rem; border-radius: 8px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                    <span style="color: #888;">Date</span>
                    <span><?php echo date('M j, Y', strtotime($order['created_at'])); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid #333;">
                    <span style="color: #888;">Total Items</span>
                    <span><?php echo count($items); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: bold;">
                    <span>Total</span>
                    <span style="color: #e10600;">$<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>
            
            <h3 style="margin: 2rem 0 1rem;">Shipping Address</h3>
            <div style="background: #1a1a1a; padding: 1.5rem; border-radius: 8px;">
                <p style="color: #ccc; white-space: pre-line;"><?php echo htmlspecialchars($order['shipping_address']); ?></p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
