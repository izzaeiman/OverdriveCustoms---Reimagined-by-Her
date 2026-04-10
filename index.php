<?php
$pageTitle = "Home";
require_once 'inc/header.php';

// Fetch featured products (e.g., first 4)
$stmt = $pdo->query("SELECT * FROM products WHERE visible = 1 ORDER BY created_at DESC LIMIT 4");
$featuredProducts = $stmt->fetchAll();

// Fetch Site Settings
$settings = [];
$stmt = $pdo->query("SELECT * FROM site_settings");
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$heroHeading = !empty($settings['hero_heading']) ? $settings['hero_heading'] : 'OVERDRIVE';
$heroSubheading = !empty($settings['hero_subheading']) ? $settings['hero_subheading'] : 'FUEL YOUR PASSION';
$heroVideo = !empty($settings['hero_video']) ? $settings['hero_video'] : '';
?>

<!-- Hero Section -->
<section class="hero">
    <?php if ($heroVideo): ?>
        <video autoplay muted loop playsinline class="hero-video">
            <source src="<?php echo $heroVideo; ?>" type="video/mp4">
        </video>
    <?php endif; ?>
    <div class="hero-content">
        <h1><?php echo htmlspecialchars($heroHeading); ?></h1>
        <p class="hero-subheading"><?php echo htmlspecialchars($heroSubheading); ?></p>
        <div class="hero-actions">
            <a href="shop.php" class="btn">Visit Shop</a>
            <button class="btn btn-outline" onclick="document.querySelector('.support-bubble').click()">I have a question</button>
        </div>
    </div>
</section>

<div class="container">
    <!-- Featured Collections -->
    <h2 class="section-title">Collections</h2>
    <div class="collections-grid">
        <div class="collection-card">
            <img src="https://placehold.co/600x400/D4A5A5/FFFFFF?text=German+Collection" alt="German Collection">
            <div class="collection-info">
                <h3>German Collection</h3>
                <p>Precision engineering meets street style.</p>
                <a href="shop.php?category=german" class="btn">Shop Now</a>
            </div>
        </div>
        <div class="collection-card">
            <img src="https://placehold.co/600x400/D4A5A5/FFFFFF?text=Japanese+Collection" alt="Japanese Collection">
            <div class="collection-info">
                <h3>Japanese Collection</h3>
                <p>JDM legends and drift culture.</p>
                <a href="shop.php?category=japanese" class="btn">Shop Now</a>
            </div>
        </div>
    </div>

    <!-- Product Highlights -->
    <h2 class="section-title">Latest Drops</h2>
    <div class="product-grid">
        <?php foreach ($featuredProducts as $product): ?>
            <div class="product-card">
                <div class="product-img">
                    <img src="<?php echo htmlspecialchars($product['image_url'] ?: 'assets/img/placeholder.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($product['title']); ?>"
                         onerror="this.src='https://placehold.co/400x400/222/e10600?text=Product'">
                </div>
                <div class="product-details">
                    <h3 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h3>
                    <span class="product-price">$<?php echo htmlspecialchars($product['price']); ?></span>
                    <button class="btn" style="width: 100%" onclick="addToCart(<?php echo $product['id']; ?>)">Add to Cart</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- M Town Story Section -->
    <div class="container" style="margin: 6rem auto; display: flex; align-items: center; gap: 4rem;">
        <div style="flex: 1;">
            <h2 style="font-size: 3rem; margin-bottom: 1.5rem; color: #2C2C2C; font-weight: 700;">Welcome to M Town</h2>
            <p style="font-size: 1.2rem; margin-bottom: 2rem; color: #4a4a4a; font-weight: 400;">1200 HP BMW — Where the streets are always wet and the tires are always smoking. Experience the thrill of the ultimate driving machine through our exclusive M-Town collection.</p>
            <a href="shop.php?category=m-town" class="btn btn-outline">Explore M Town</a>
        </div>
        <div style="flex: 1;">
             <img src="https://placehold.co/800x600/D4A5A5/FFFFFF?text=M+Town" alt="M Town" style="border-radius: var(--radius); box-shadow: var(--shadow);">
        </div>
    </div>
</div>

<?php require_once 'inc/footer.php'; ?>
