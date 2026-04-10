<?php
$pageTitle = "My Account";
require_once __DIR__ . '/../inc/header.php';
require_once __DIR__ . '/../includes/session_check.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<div class="container" style="padding: 4rem 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem;">
        <h1 style="margin: 0; color: #333; font-family: var(--font-heading); font-size: 3rem;">My Account</h1>
        <a href="../auth/logout.php" class="btn btn-outline" style="padding: 0.5rem 1.5rem; font-size: 0.9rem;">Logout</a>
    </div>

    <div class="dashboard-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem;">
        
        <a href="orders.php" style="display: block; background: #fff; padding: 2.5rem; border-radius: var(--radius); text-decoration: none; color: inherit; box-shadow: var(--shadow); transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <h3 style="color: #2C2C2C; margin-bottom: 0.5rem; font-family: var(--font-heading); font-size: 1.5rem;">My Orders</h3>
            <p style="color: #666; font-size: 1rem;">Track and view your order history.</p>
        </a>

        <a href="addresses.php" style="display: block; background: #fff; padding: 2.5rem; border-radius: var(--radius); text-decoration: none; color: inherit; box-shadow: var(--shadow); transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <h3 style="color: #2C2C2C; margin-bottom: 0.5rem; font-family: var(--font-heading); font-size: 1.5rem;">Addresses</h3>
            <p style="color: #666; font-size: 1rem;">Manage your shipping addresses.</p>
        </a>

        <a href="wishlist.php" style="display: block; background: #fff; padding: 2.5rem; border-radius: var(--radius); text-decoration: none; color: inherit; box-shadow: var(--shadow); transition: transform 0.3s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <h3 style="color: #2C2C2C; margin-bottom: 0.5rem; font-family: var(--font-heading); font-size: 1.5rem;">Wishlist</h3>
            <p style="color: #666; font-size: 1rem;">View your saved items.</p>
        </a>

    </div>
</div>

        background: #222 !important;
    }
</style>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
