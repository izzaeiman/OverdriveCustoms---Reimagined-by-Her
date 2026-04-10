<?php
require_once '../config.php';
require_once '../inc/auth.php';

requireAdmin();

// --- Analytics Queries ---

// 1. Total Revenue
$stmt = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status != 'Cancelled'");
$totalRevenue = $stmt->fetchColumn() ?: 0;

// 2. Orders Count
$stmt = $pdo->query("SELECT COUNT(*) FROM orders");
$orderCount = $stmt->fetchColumn();

// 3. Products Count
$stmt = $pdo->query("SELECT COUNT(*) FROM products");
$productCount = $stmt->fetchColumn();

// 4. User Count
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'customer'");
$userCount = $stmt->fetchColumn();

// 5. Recent Orders
$stmt = $pdo->query("
    SELECT o.*, u.email 
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$recentOrders = $stmt->fetchAll();

// 6. Traffic (Page Views Today)
$stmt = $pdo->query("SELECT COUNT(*) FROM analytics WHERE DATE(created_at) = CURDATE()");
$viewsToday = $stmt->fetchColumn();

// 7. Best Selling Products
$stmt = $pdo->query("
    SELECT p.title, SUM(oi.quantity) as sold
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY p.id
    ORDER BY sold DESC
    LIMIT 5
");
$bestSellers = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Overdrive</title>
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
        
        .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 2rem; margin-top: 2rem; }
        .stat-card { background: var(--card-bg); padding: 2rem; border-radius: var(--radius); text-align: center; border: none; box-shadow: var(--shadow); transition: transform 0.3s ease; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-card h3 { font-size: 2.5rem; color: var(--accent-color); margin: 0; font-family: var(--font-heading); }
        .stat-card p { color: var(--text-mute); margin-top: 0.5rem; text-transform: uppercase; letter-spacing: 1px; font-size: 0.85rem; font-weight: 600; }
        
        .section-header { margin-top: 4rem; margin-bottom: 1.5rem; border-bottom: 2px solid var(--accent-color); padding-bottom: 0.5rem; color: var(--text-main); display: inline-block; font-family: var(--font-heading); font-size: 1.5rem; }
        
        table { width: 100%; border-collapse: separate; border-spacing: 0; margin-top: 1rem; background: var(--card-bg); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; }
        th, td { padding: 1.2rem; text-align: left; border-bottom: 1px solid var(--border-color); }
        th { background: #fdfbf7; color: var(--text-mute); text-transform: uppercase; font-size: 0.8rem; font-weight: 600; letter-spacing: 0.5px; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #fcfcfc; }
        
        .header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem; }
        .header-bar h1 { font-family: var(--font-heading); color: var(--text-main); font-size: 2.5rem; margin: 0; }
        .header-bar span { color: var(--text-mute); font-size: 1.1rem; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <h2 style="color: var(--accent-color); font-family: var(--font-heading); font-size: 2rem; margin-bottom: 3rem; text-align: center;">Overdrive</h2>
            <nav class="admin-nav">
                <a href="index.php" class="active">Dashboard</a>
                
                <?php if (hasRole(['manager', 'media_manager'])): ?>
                    <a href="products.php">Products</a>
                <?php endif; ?>
                
                <?php if (hasRole('order_manager')): ?>
                    <a href="orders.php">Orders</a>
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
            <div class="header-bar">
                <h1>Dashboard</h1>
                <span>Welcome Admin</span>
            </div>

            <!-- Stats Grid -->
            <div class="dashboard-grid">
                <div class="stat-card">
                    <h3>$<?php echo number_format($totalRevenue, 2); ?></h3>
                    <p>Total Revenue</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $orderCount; ?></h3>
                    <p>Total Orders</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $viewsToday; ?></h3>
                    <p>Page Views Today</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $userCount; ?></h3>
                    <p>Customers</p>
                </div>
            </div>

            <!-- Recent Orders -->
            <h3 class="section-header">Recent Orders</h3>
            <?php if (empty($recentOrders)): ?>
                <p>No orders yet.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['email'] ?? 'N/A'); ?></td>
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span style="
                                        padding: 4px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: 500;
                                        background: <?php echo $order['status'] === 'Pending' ? '#fff3cd' : ($order['status'] === 'Cancelled' ? '#f8d7da' : ($order['status'] === 'Returned' ? '#e2e3e5' : '#d4edda')); ?>;
                                        color: <?php echo $order['status'] === 'Pending' ? '#856404' : ($order['status'] === 'Cancelled' ? '#721c24' : ($order['status'] === 'Returned' ? '#383d41' : '#155724')); ?>;
                                    ">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j', strtotime($order['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="margin-top: 1.5rem; text-align: right;">
                    <a href="orders.php" style="color: var(--accent-color); font-weight: 600; text-decoration: none;">View All Orders &rarr;</a>
                </div>
            <?php endif; ?>

            <!-- Best Sellers -->
            <h3 class="section-header">Best Selling Products</h3>
            <?php if (empty($bestSellers)): ?>
                <p style="color: var(--text-mute);">No sales data yet.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Units Sold</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bestSellers as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['title']); ?></td>
                                <td><?php echo $item['sold']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

        </main>
    </div>
</body>
</html>
