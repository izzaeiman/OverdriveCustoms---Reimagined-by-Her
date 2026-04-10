<?php
$pageTitle = "Wishlist";
require_once __DIR__ . '/../inc/header.php';
require_once __DIR__ . '/../includes/session_check.php';

$user_id = $_SESSION['user_id'];

// Handle Remove
if (isset($_GET['remove'])) {
    $w_id = $_GET['remove'];
    $stmt = $pdo->prepare("DELETE FROM wishlist WHERE id = ? AND user_id = ?");
    $stmt->execute([$w_id, $user_id]);
    header("Location: wishlist.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*, w.id as wishlist_id 
    FROM wishlist w 
    JOIN products p ON w.product_id = p.id 
    WHERE w.user_id = ?
");
$stmt->execute([$user_id]);
$wishlist_items = $stmt->fetchAll();
?>

<div class="container" style="padding: 4rem 0;">
    <div style="margin-bottom: 2rem;">
        <a href="index.php" style="color: #888; text-decoration: none;">&larr; Back to Dashboard</a>
    </div>
    <h1 style="color: #e10600; margin-bottom: 2rem;">My Wishlist</h1>

    <?php if (empty($wishlist_items)): ?>
        <p>Your wishlist is empty.</p>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($wishlist_items as $product): ?>
                <div class="product-card">
                    <div class="product-img">
                        <img src="<?php echo BASE_URL . htmlspecialchars($product['image_url'] ?: 'assets/img/placeholder.jpg'); ?>" 
                             alt="<?php echo htmlspecialchars($product['title']); ?>"
                             onerror="this.src='https://placehold.co/400x400/222/e10600?text=Product'">
                    </div>
                    <div class="product-details">
                        <h3 class="product-title"><?php echo htmlspecialchars($product['title']); ?></h3>
                        <span class="product-price">$<?php echo htmlspecialchars($product['price']); ?></span>
                        
                        <div style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                            <button class="btn" style="flex: 1;" onclick="addToCart(<?php echo $product['id']; ?>)">Add to Cart</button>
                            <a href="?remove=<?php echo $product['wishlist_id']; ?>" class="btn btn-outline" style="padding: 0 1rem;">&times;</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
