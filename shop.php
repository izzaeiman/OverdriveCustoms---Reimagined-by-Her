<?php
$pageTitle = "Shop";
require_once 'inc/header.php';

$categorySlug = $_GET['category'] ?? null;
$whereClause = "WHERE visible = 1";
$params = [];

if ($categorySlug) {
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE slug = ?");
    $stmt->execute([$categorySlug]);
    $catId = $stmt->fetchColumn();
    
    if ($catId) {
        $whereClause .= " AND category_id = ?";
        $params[] = $catId;
    }
}

$stmt = $pdo->prepare("SELECT * FROM products $whereClause ORDER BY created_at DESC");
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<div class="container" style="padding: 4rem 0;">
    <h1 style="color: var(--accent-color); margin-bottom: 2rem; text-align: center; font-family: var(--font-heading); font-size: 3.5rem;">Shop</h1>
    <div style="display: flex; justify-content: center; align-items: center; margin-bottom: 2rem;">
        <div class="filters">
            <a href="shop.php" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.8rem;">All</a>
            <a href="shop.php?category=german" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.8rem;">German</a>
            <a href="shop.php?category=japanese" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.8rem;">Japanese</a>
        </div>
    </div>

    <?php if (empty($products)): ?>
        <p>No products found in this category.</p>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card" style="position: relative;">
                    <button class="wishlist-btn" onclick="toggleWishlist(<?php echo $product['id']; ?>, this)" style="position: absolute; top: 10px; right: 10px; z-index: 10; background: rgba(0,0,0,0.6); border: none; color: #fff; border-radius: 50%; width: 35px; height: 35px; font-size: 1.2rem; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: color 0.3s;">
                        &hearts;
                    </button>
                    <a href="product.php?id=<?php echo $product['id']; ?>" style="text-decoration: none; color: inherit;">
                        <div class="product-img">
                            <img src="<?php echo htmlspecialchars($product['image_url'] ?: 'assets/img/placeholder.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($product['title']); ?>"
                                 onerror="this.src='https://placehold.co/400x400/222/e10600?text=Product'">
                        </div>
                        <div class="product-details">
                            <h3 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h3>
                            <p style="font-size: 0.8rem; color: #888; margin-bottom: 0.5rem;"><?php echo htmlspecialchars(substr($product['description'], 0, 50)) . '...'; ?></p>
                            <span class="product-price">$<?php echo htmlspecialchars($product['price']); ?></span>
                        </div>
                    </a>
                    <div style="padding: 0 1.5rem 1.5rem;">
                        <a href="product.php?id=<?php echo $product['id']; ?>" class="btn" style="width: 100%; text-align: center; display: block;">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'inc/footer.php'; ?>
