<?php
require_once '../config.php';
require_once '../inc/auth.php';

requireAdmin();
requireRole('order_manager');

$order_id = $_GET['id'] ?? null;
if (!$order_id) {
    header("Location: orders.php");
    exit;
}

// Fetch Order with user email
$stmt = $pdo->prepare("
    SELECT o.*, u.email 
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    echo "Order not found.";
    exit;
}

// Fetch Items
$stmt = $pdo->prepare("
    SELECT oi.*, p.title, p.image_url 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order #<?php echo $order['id']; ?> - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root {
            --admin-bg: #f8f9fa;
            --sidebar-bg: #ffffff;
            --card-bg: #ffffff;
            --text-main: #333333;
            --text-mute: #777777;
            --border-color: #eeeeee;
        }
        body { background: var(--admin-bg); color: var(--text-main); font-family: var(--font-body); }
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 260px; background: var(--sidebar-bg); padding: 2rem; border-right: 1px solid var(--border-color); box-shadow: 2px 0 10px rgba(0,0,0,0.02); }
        .admin-content { flex: 1; padding: 2rem 3rem; background: var(--admin-bg); }
        
        .admin-nav a { display: block; padding: 1rem 1.5rem; color: var(--text-mute); text-decoration: none; border-radius: 8px; margin-bottom: 0.5rem; transition: all 0.3s ease; font-weight: 500; }
        .admin-nav a:hover, .admin-nav a.active { color: var(--accent-color); background: #fff0f3; }
        
        .order-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .order-info { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem; }
        .info-card { background: var(--card-bg); padding: 1.5rem; border-radius: var(--radius); box-shadow: var(--shadow); }
        .info-card h3 { color: var(--accent-color); margin-bottom: 1rem; font-family: var(--font-heading); font-size: 1.2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem; }
        .info-card p { margin-bottom: 0.8rem; color: #555; }
        .info-card strong { color: #333; }
        
        table { width: 100%; border-collapse: separate; border-spacing: 0; background: var(--card-bg); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; margin-top: 2rem; }
        th, td { padding: 1.2rem; text-align: left; border-bottom: 1px solid var(--border-color); }
        th { background: #fdfbf7; color: var(--text-mute); text-transform: uppercase; font-size: 0.8rem; font-weight: 600; letter-spacing: 0.5px; }
        tr:last-child td { border-bottom: none; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <h2 style="color: var(--accent-color); font-family: var(--font-heading); font-size: 2rem; margin-bottom: 3rem; text-align: center;">Overdrive</h2>
            <nav class="admin-nav">
                <a href="index.php">Dashboard</a>
                
                <?php if (hasRole(['manager', 'media_manager'])): ?>
                    <a href="products.php">Products</a>
                <?php endif; ?>
                
                <?php if (hasRole('order_manager')): ?>
                    <a href="orders.php" class="active">Orders</a>
                <?php endif; ?>
                
                <?php if (hasRole('manager')): ?>
                    <a href="users.php">Users</a>
                <?php endif; ?>
                
                <?php if (hasRole(['manager', 'media_manager'])): ?>
                    <a href="media.php">Media Library</a>
                <?php endif; ?>
                
                <?php if (hasRole('manager')): ?>
                    <a href="settings.php">Settings</a>
                <?php endif; ?>
                
                <a href="../index.php" target="_blank">View Storefront</a>
                <a href="../auth/logout.php">Logout</a>
            </nav>
        </aside>

        <main class="admin-content">
            <div class="order-header">
                <h1 style="font-family: var(--font-heading); font-size: 2.5rem; color: #333;">Order #<?php echo $order['id']; ?></h1>
                <a href="orders.php" class="btn" style="padding: 10px 20px; text-decoration: none;">Back to Orders</a>
            </div>

            <div class="order-info">
                <div class="info-card">
                    <h3>Customer Details</h3>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email'] ?? 'Guest'); ?></p>
                    <p><strong>Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
                    <p><strong>Status:</strong> 
                        <span style="padding: 4px 10px; border-radius: 20px; font-size: 0.85rem; background: #eee; color: #333;">
                            <?php echo htmlspecialchars($order['status']); ?>
                        </span>
                    </p>
                </div>
                <div class="info-card">
                    <h3>Shipping Info</h3>
                    <p style="white-space: pre-line; line-height: 1.6;"><?php echo htmlspecialchars($order['shipping_address'] ?? 'N/A'); ?></p>
                </div>
            </div>

            <div class="info-card">
                <h3>Order Items</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Options</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 1rem;">
                                        <img src="../<?php echo htmlspecialchars($item['image_url']); ?>" width="50" height="50" style="object-fit: cover; border-radius: 4px;">
                                        <span style="font-weight: 500; color: #333;"><?php echo htmlspecialchars($item['title']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    if ($item['options']) {
                                        $opts = json_decode($item['options'], true);
                                        if ($opts) {
                                            foreach ($opts as $k => $v) {
                                                echo "<span style='display: inline-block; background: #f8f9fa; padding: 2px 8px; border-radius: 4px; font-size: 0.85rem; color: #666; margin-right: 5px;'>" . htmlspecialchars($k) . ": " . htmlspecialchars($v) . "</span>";
                                            }
                                        }
                                    } else {
                                        echo "-";
                                    }
                                    ?>
                                </td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr style="background: #fffafa;">
                            <td colspan="4" style="text-align: right; font-weight: bold; padding-right: 2rem;">Total</td>
                            <td style="font-weight: bold; color: var(--accent-color); font-size: 1.2rem;">$<?php echo number_format($order['total_amount'], 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>
