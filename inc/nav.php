<?php
// Fetch Logo Setting if not already fetched
if (!isset($settings)) {
    $settings = [];
    $stmt = $pdo->query("SELECT * FROM site_settings");
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}
$logoIcon = !empty($settings['logo_icon']) ? $settings['logo_icon'] : '';
?>
<nav class="navbar">
    <div class="container nav-container">
        <div class="nav-left">
            <?php if ($logoIcon): ?>
                <a href="<?php echo BASE_URL; ?>" class="logo-icon-link">
                    <img src="<?php echo BASE_URL . $logoIcon; ?>" alt="Overdrive Logo" class="logo-icon">
                </a>
            <?php endif; ?>
        </div>
        
        <div class="nav-center">
            <a href="<?php echo BASE_URL; ?>" class="logo">
                <span class="logo-text">OVERDRIVE</span>
            </a>
        </div>
        
        <div class="nav-links">
            <a href="<?php echo BASE_URL; ?>">Home</a>
            <a href="<?php echo BASE_URL; ?>shop.php">Shop</a>
            
            <a href="<?php echo BASE_URL; ?>cart.php" class="cart-link" style="position: relative;">
                Cart 
                <span id="cart-count" class="cart-badge"></span>
            </a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="<?php echo BASE_URL; ?>admin/" class="admin-link">Admin</a>
                <?php else: ?>
                    <a href="<?php echo BASE_URL; ?>customer/">My Account</a>
                <?php endif; ?>
                <a href="<?php echo BASE_URL; ?>auth/logout.php">Logout</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>auth/login.php">Login</a>
            <?php endif; ?>
        </div>

        <div class="mobile-menu-toggle">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
</nav>
