<?php
require_once '../config.php';
require_once '../inc/auth.php';

requireAdmin();
requireRole('order_manager');

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], $_POST['order_id']]);
    $success = "Order status updated.";
}

$stmt = $pdo->query("
    SELECT o.*, u.email 
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Orders - Admin</title>
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
        
        table { width: 100%; border-collapse: separate; border-spacing: 0; margin-top: 2rem; background: var(--card-bg); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; }
        th, td { padding: 1.2rem; text-align: left; border-bottom: 1px solid var(--border-color); }
        th { background: #fdfbf7; color: var(--text-mute); text-transform: uppercase; font-size: 0.8rem; font-weight: 600; letter-spacing: 0.5px; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #fcfcfc; }
        
        select { padding: 8px 12px; background: #fff; color: #333; border: 1px solid #ddd; border-radius: 6px; cursor: pointer; outline: none; transition: border-color 0.2s; }
        select:focus { border-color: var(--accent-color); }
        
        h1 { font-family: var(--font-heading); font-size: 2.5rem; margin-bottom: 1.5rem; color: var(--text-main); }
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
            <h1>Manage Orders</h1>
            <?php if (isset($success)) echo "<p style='color: #28a745;'>$success</p>"; ?>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo htmlspecialchars($order['email'] ?? 'Guest'); ?></td>
                            <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="status" onchange="this.form.submit()">
                                        <option value="Pending" <?php echo $order['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Shipped" <?php echo $order['status'] == 'Shipped' ? 'selected' : ''; ?>>Shipped</option>
                                        <option value="Delivered" <?php echo $order['status'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                        <option value="Returned" <?php echo $order['status'] == 'Returned' ? 'selected' : ''; ?>>Returned</option>
                                        <option value="Cancelled" <?php echo $order['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </form>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                            <td>
                                <a href="order_details.php?id=<?php echo $order['id']; ?>" style="color: #e10600;">View Details</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>
</html>
