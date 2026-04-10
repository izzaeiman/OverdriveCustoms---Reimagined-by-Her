<?php
require_once 'config.php';
require_once 'inc/header.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "<div class='container'><p>Product not found.</p></div>";
    require_once 'inc/footer.php';
    exit;
}

// Fetch Product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    echo "<div class='container'><p>Product not found.</p></div>";
    require_once 'inc/footer.php';
    exit;
}

// Fetch Images
$stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, id ASC");
$stmt->execute([$id]);
$images = $stmt->fetchAll();

// Fetch Options
$stmt = $pdo->prepare("SELECT * FROM product_options WHERE product_id = ? ORDER BY option_group, id ASC");
$stmt->execute([$id]);
$options = $stmt->fetchAll();

// Group Options
$groupedOptions = [];
foreach ($options as $opt) {
    $groupedOptions[$opt['option_group']][] = $opt;
}
?>

<div class="container" style="padding-top: 4rem; padding-bottom: 4rem;">
    <div class="product-detail-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem;">
        
        <!-- Image Gallery -->
        <div class="product-gallery">
            <div class="main-image" style="margin-bottom: 1rem; border: 1px solid #333; height: 500px; overflow: hidden;">
                <img id="mainImg" src="<?php echo htmlspecialchars($product['image_url'] ?: 'assets/img/placeholder.jpg'); ?>" style="width: 100%; height: 100%; object-fit: cover;">
            </div>
            <?php if (!empty($images)): ?>
                <div class="thumbnails" style="display: flex; gap: 1rem; overflow-x: auto;">
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" onclick="changeImage(this.src)" style="width: 80px; height: 80px; object-fit: cover; cursor: pointer; border: 1px solid #333;">
                    <?php foreach ($images as $img): ?>
                        <img src="<?php echo htmlspecialchars($img['image_url']); ?>" onclick="changeImage(this.src)" style="width: 80px; height: 80px; object-fit: cover; cursor: pointer; border: 1px solid #333;">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Product Info -->
        <div class="product-info">
            <h1 style="color: var(--accent-color); font-family: var(--font-heading); font-size: 3rem; margin-bottom: 1rem;"><?php echo htmlspecialchars($product['title']); ?></h1>
            
            <p style="font-size: 1.5rem; color: #333; font-weight: bold; margin-bottom: 2rem;">$<?php echo htmlspecialchars($product['price']); ?></p>
            
            <p style="color: #333; margin-bottom: 2rem; line-height: 1.8;"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

            <!-- Options Form -->
            <div class="product-options" style="margin-bottom: 2rem;">
                <?php foreach ($groupedOptions as $group => $opts): ?>
                    <div class="option-group" style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: bold; color: #333;"><?php echo htmlspecialchars($group); ?></label>
                        <select class="option-select" data-group="<?php echo htmlspecialchars($group); ?>" style="width: 100%; padding: 0.8rem; background: #fff; border: 1px solid #ddd; color: #333; border-radius: 6px;">
                            <?php foreach ($opts as $opt): ?>
                                <option value="<?php echo htmlspecialchars($opt['option_value']); ?>">
                                    <?php echo htmlspecialchars($opt['option_value']); ?>
                                    <?php if ($opt['price_modifier'] > 0): ?>
                                        (+$<?php echo $opt['price_modifier']; ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="display: flex; gap: 1rem; margin-bottom: 2rem;">
                <input type="number" id="qty" value="1" min="1" style="width: 80px; padding: 0.8rem; background: #fff; border: 1px solid #ddd; color: #333; text-align: center; border-radius: 6px;">
                <button class="btn" style="flex: 1;" onclick="addToCartWithOptions(<?php echo $product['id']; ?>)">Add to Cart</button>
            </div>
            
            <button class="btn btn-outline" onclick="toggleWishlist(<?php echo $product['id']; ?>, this)">Add to Wishlist &hearts;</button>
        </div>
    </div>
</div>

<script>
function changeImage(src) {
    document.getElementById('mainImg').src = src;
}

function addToCartWithOptions(productId) {
    const qty = document.getElementById('qty').value;
    const selects = document.querySelectorAll('.option-select');
    const options = {};
    
    selects.forEach(select => {
        options[select.dataset.group] = select.value;
    });

    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('product_id', productId);
    formData.append('quantity', qty);
    formData.append('options', JSON.stringify(options));

    fetch('cart.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const badge = document.getElementById('cart-count');
                if (badge) badge.innerText = data.count;
                alert('Added to cart!');
            }
        });
}
</script>

<?php require_once 'inc/footer.php'; ?>
