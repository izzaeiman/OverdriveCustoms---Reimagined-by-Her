<?php
$pageTitle = "My Orders";
require_once __DIR__ . '/../inc/header.php';
require_once __DIR__ . '/../includes/session_check.php';

$user_id = $_SESSION['user_id'];

// Fetch orders by user_id
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>

<div class="container" style="padding: 4rem 0; min-height: 60vh;">
    <a href="index.php" style="color: #888; text-decoration: none; margin-bottom: 2rem; display: inline-block;">&larr; Back to Dashboard</a>
    
    <h1 style="color: #D4A5A5; margin-bottom: 2rem; font-family: var(--font-heading); font-size: 3rem;">My Orders</h1>

    <?php if (empty($orders)): ?>
        <p style="color: #444; font-size: 1.2rem; font-weight: 500;">You haven't placed any orders yet.</p>
    <?php else: ?>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
                <thead>
                    <tr style="border-bottom: 1px solid #333; text-align: left;">
                        <th style="padding: 1rem; color: #888;">Order ID</th>
                        <th style="padding: 1rem; color: #888;">Date</th>
                        <th style="padding: 1rem; color: #888;">Status</th>
                        <th style="padding: 1rem; color: #888;">Total</th>
                        <th style="padding: 1rem; color: #888;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr style="border-bottom: 1px solid #222;">
                            <td style="padding: 1rem;">#<?php echo $order['id']; ?></td>
                            <td style="padding: 1rem;"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                            <td style="padding: 1rem;">
                                <span style="
                                    padding: 4px 8px; 
                                    border-radius: 4px; 
                                    font-size: 0.8rem;
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
                            </td>
                            <td style="padding: 1rem;">$<?php echo number_format($order['total_amount'], 2); ?></td>
                            <td style="padding: 1rem;">
                                <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-outline" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
