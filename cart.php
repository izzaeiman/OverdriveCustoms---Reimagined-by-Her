<?php
$pageTitle = "Cart";
require_once 'inc/header.php';

// Handle AJAX Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];
    $productId = $_POST['product_id'] ?? null;
    $quantity = (int)($_POST['quantity'] ?? 1);
    $options = $_POST['options'] ?? []; // Array of options e.g. {'Size': 'M'}

    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Generate unique key for cart item
    // If options is a string (from JSON in DB), decode it first
    if (is_string($options)) $options = json_decode($options, true) ?? [];
    
    // Sort options to ensure consistent keys
    ksort($options);
    $cartKey = $productId . '_' . md5(json_encode($options));

    if ($action === 'add' && $productId) {
        if (isset($_SESSION['cart'][$cartKey])) {
            $_SESSION['cart'][$cartKey]['qty'] += $quantity;
        } else {
            $_SESSION['cart'][$cartKey] = [
                'product_id' => $productId,
                'qty' => $quantity,
                'options' => $options
            ];
        }
    } elseif ($action === 'remove') {
        $keyToRemove = $_POST['key'] ?? null;
        if ($keyToRemove) unset($_SESSION['cart'][$keyToRemove]);
    } elseif ($action === 'update') {
        $keyToUpdate = $_POST['key'] ?? null;
        if ($keyToUpdate && isset($_SESSION['cart'][$keyToUpdate])) {
            if ($quantity > 0) {
                $_SESSION['cart'][$keyToUpdate]['qty'] = $quantity;
            } else {
                unset($_SESSION['cart'][$keyToUpdate]);
            }
        }
    }

    // Sync with DB if logged in
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$userId]);
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity, options) VALUES (?, ?, ?, ?)");
        foreach ($_SESSION['cart'] as $item) {
            // Handle old cart format migration if necessary
            if (!is_array($item)) continue; 
            $stmt->execute([$userId, $item['product_id'], $item['qty'], json_encode($item['options'])]);
        }
    }

    $count = 0;
    foreach ($_SESSION['cart'] as $item) {
        if (is_array($item)) $count += $item['qty'];
    }
    echo json_encode(['success' => true, 'count' => $count]);
    exit;
}

// Fetch Cart Items
$cartItems = [];
$subtotal = 0;

if (!empty($_SESSION['cart'])) {
    // Collect all product IDs
    $productIds = [];
    foreach ($_SESSION['cart'] as $item) {
        if (is_array($item)) $productIds[] = $item['product_id'];
    }
    
    if (!empty($productIds)) {
        $ids = implode(',', array_unique($productIds));
        // Sanitize
        $ids = preg_replace('/[^0-9,]/', '', $ids);
        
        if ($ids) {
            $stmt = $pdo->query("SELECT * FROM products WHERE id IN ($ids)");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $productMap = [];
            foreach ($products as $p) $productMap[$p['id']] = $p;

            foreach ($_SESSION['cart'] as $key => $item) {
                if (!is_array($item)) continue; // Skip malformed items
                $pId = $item['product_id'];
                if (isset($productMap[$pId])) {
                    $product = $productMap[$pId];
                    $product['qty'] = $item['qty'];
                    $product['cart_key'] = $key;
                    $product['options'] = $item['options'];
                    $product['line_total'] = $product['price'] * $item['qty'];
                    $subtotal += $product['line_total'];
                    $cartItems[] = $product;
                }
            }
        }
    }
}
?>

<div class="container" style="padding: 4rem 0;">
    <h1 style="color: var(--accent-color); margin-bottom: 2rem; font-family: var(--font-heading); font-size: 3rem;">Your Cart</h1>

    <?php if (empty($cartItems)): ?>
        <p>Your cart is empty. <a href="shop.php" style="color: #e10600;">Go Shopping</a></p>
    <?php else: ?>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartItems as $item): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <img src="<?php echo htmlspecialchars($item['image_url'] ?: 'assets/img/placeholder.jpg'); ?>" width="50" height="50" style="object-fit: cover;">
                                <div>
                                    <?php echo htmlspecialchars($item['title']); ?>
                                    <?php if (!empty($item['options'])): ?>
                                        <div style="font-size: 0.8rem; color: #888;">
                                            <?php foreach ($item['options'] as $k => $v): ?>
                                                <?php echo htmlspecialchars($k) . ': ' . htmlspecialchars($v); ?><br>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>$<?php echo htmlspecialchars($item['price']); ?></td>
                        <td>
                            <input type="number" value="<?php echo $item['qty']; ?>" min="1" style="width: 60px; padding: 0.3rem; background: #222; border: 1px solid #444; color: #fff;" onchange="updateCart('<?php echo $item['cart_key']; ?>', this.value)">
                        </td>
                        <td>$<?php echo number_format($item['line_total'], 2); ?></td>
                        <td>
                            <button class="btn btn-outline" style="padding: 0.3rem 0.5rem; font-size: 0.8rem; border-color: #ff4444; color: #ff4444;" onclick="removeFromCart('<?php echo $item['cart_key']; ?>')">Remove</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="cart-summary">
            <div class="summary-row">
                <span>Subtotal</span>
                <span>$<?php echo number_format($subtotal, 2); ?></span>
            </div>
            <a href="checkout.php" class="btn" style="width: 100%; text-align: center;">Proceed to Checkout</a>
        </div>
    <?php endif; ?>
</div>

<script>
function updateCart(key, qty) {
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('key', key);
    formData.append('quantity', qty);

    fetch('cart.php', { method: 'POST', body: formData })
        .then(() => window.location.reload());
}

function removeFromCart(key) {
    if(!confirm('Remove this item?')) return;
    const formData = new FormData();
    formData.append('action', 'remove');
    formData.append('key', key);

    fetch('cart.php', { method: 'POST', body: formData })
        .then(() => window.location.reload());
}
</script>

<?php require_once 'inc/footer.php'; ?>
