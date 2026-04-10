<?php
$pageTitle = "Checkout";
require_once 'inc/header.php';

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

// Calculate Subtotal
$subtotal = 0;
$cartItems = [];
if (!empty($_SESSION['cart'])) {
    // Collect IDs
    $productIds = [];
    foreach ($_SESSION['cart'] as $item) {
        if (is_array($item)) $productIds[] = $item['product_id'];
    }

    if (!empty($productIds)) {
        $ids = implode(',', array_unique($productIds));
        $ids = preg_replace('/[^0-9,]/', '', $ids);
        
        if ($ids) {
            $stmt = $pdo->query("SELECT * FROM products WHERE id IN ($ids)");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $productMap = [];
            foreach ($products as $p) $productMap[$p['id']] = $p;

            foreach ($_SESSION['cart'] as $item) {
                if (!is_array($item)) continue;
                $pId = $item['product_id'];
                if (isset($productMap[$pId])) {
                    $product = $productMap[$pId];
                    $product['qty'] = $item['qty'];
                    $product['options'] = $item['options'];
                    $subtotal += $product['price'] * $item['qty'];
                    $cartItems[] = $product;
                }
            }
        }
    }
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Require Login
    if (!isset($_SESSION['user_id'])) {
        header("Location: auth/login.php?redirect=checkout.php");
        exit;
    }

    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $payment = $_POST['payment'] ?? 'cod';
    
    // Combine address info
    $shipping_address = "Name: $name\nEmail: $email\nPhone: $phone\nAddress: $address\nPayment: $payment";

    try {
        $pdo->beginTransaction();

        // Combine address info for shipping_address
        $shipping_address = "Name: $name\nEmail: $email\nPhone: $phone\nAddress: $address\nPayment: $payment";

        // Insert Order using new schema
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status, shipping_address) VALUES (?, ?, 'Pending', ?)");
        $stmt->execute([$_SESSION['user_id'], $subtotal, $shipping_address]);
        $orderId = $pdo->lastInsertId();

        // Insert Order Items
        $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, options) VALUES (?, ?, ?, ?, ?)");
        foreach ($cartItems as $item) {
            $optionsJson = !empty($item['options']) ? json_encode($item['options']) : null;
            $stmt->execute([$orderId, $item['id'], $item['qty'], $item['price'], $optionsJson]);
        }

        // Clear Cart (Session + DB)
        unset($_SESSION['cart']);
        if (isset($_SESSION['user_id'])) {
            $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$_SESSION['user_id']]);
        }

        $pdo->commit();
        $message = "Order placed successfully! Order ID: " . $orderId;
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Error placing order: " . $e->getMessage();
    }
}
?>

<div class="container" style="padding-top: 2rem;">
    <?php if ($message): ?>
        <div style="text-align: center; padding: 4rem;">
            <h1 style="color: var(--accent-color); margin-bottom: 1rem; font-family: var(--font-heading);">Thank You!</h1>
            <p style="font-size: 1.2rem; margin-bottom: 2rem; color: #555;"><?php echo $message; ?></p>
            <a href="shop.php" class="btn">Continue Shopping</a>
        </div>
    <?php else: ?>
        <h1 style="color: var(--accent-color); margin-bottom: 2rem; font-family: var(--font-heading); text-align: center;">Checkout</h1>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; max-width: 1200px; margin: 0 auto;">
            <!-- Billing Details -->
            <div style="background: #fff; padding: 2rem; border-radius: var(--radius); box-shadow: var(--shadow);">
                <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; color: var(--accent-color); font-family: var(--font-heading);">Billing Details</h3>
                <form method="POST">
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; color: #555; font-size: 0.9rem; font-weight: 600;">Full Name</label>
                        <input type="text" name="name" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #fff; color: #333; outline: none; transition: all 0.3s ease;">
                    </div>
                    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                        <div class="form-col">
                            <label style="display: block; margin-bottom: 0.5rem; color: #555; font-size: 0.9rem; font-weight: 600;">Email</label>
                            <input type="email" name="email" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #fff; color: #333; outline: none;">
                        </div>
                        <div class="form-col">
                            <label style="display: block; margin-bottom: 0.5rem; color: #555; font-size: 0.9rem; font-weight: 600;">Phone</label>
                            <input type="tel" name="phone" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #fff; color: #333; outline: none;">
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; color: #555; font-size: 0.9rem; font-weight: 600;">Address</label>
                        <textarea name="address" rows="4" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #fff; color: #333; outline: none; resize: vertical;"></textarea>
                    </div>
                    
                    <h3 style="margin: 2rem 0 1rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; color: var(--accent-color); font-family: var(--font-heading);">Payment Method</h3>
                    <div style="background: #fdfbf7; padding: 1.5rem; border: 1px solid #eee; border-radius: 8px; margin-bottom: 2rem;">
                        <label style="display: flex; align-items: center; gap: 0.8rem; cursor: pointer; color: #555; font-weight: 500;">
                            <input type="radio" name="payment" value="cod" checked style="accent-color: var(--accent-color); transform: scale(1.2);">
                            Cash on Delivery / DM to Confirm
                        </label>
                    </div>

                    <button type="submit" class="btn" style="width: 100%; padding: 14px; font-size: 1.1rem; border-radius: 8px;">Place Order</button>
                </form>
            </div>

            <!-- Order Summary -->
            <div style="background: #fff; padding: 2rem; border-radius: var(--radius); box-shadow: var(--shadow); height: fit-content;">
                <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; color: var(--accent-color); font-family: var(--font-heading);">Order Summary</h3>
                <div style="padding: 1rem 0;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <?php foreach ($cartItems as $item): ?>
                            <tr>
                                <td style="padding: 1rem 0; border-bottom: 1px solid #f0f0f0; color: #555;">
                                    <div style="font-weight: 600; color: #333; margin-bottom: 0.3rem;"><?php echo htmlspecialchars($item['title']); ?></div>
                                    <div style="font-size: 0.9rem; color: #888;">Qty: <?php echo $item['qty']; ?></div>
                                    <?php if (!empty($item['options'])): ?>
                                        <div style="font-size: 0.85rem; color: #999; margin-top: 0.2rem;">
                                            <?php foreach ($item['options'] as $k => $v) echo htmlspecialchars("$k: $v "); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem 0; border-bottom: 1px solid #f0f0f0; text-align: right; color: #333; font-weight: 500;">
                                    $<?php echo number_format($item['price'] * $item['qty'], 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td style="padding-top: 1.5rem; font-weight: 700; color: #333; font-size: 1.1rem;">Total</td>
                            <td style="padding-top: 1.5rem; font-weight: 700; text-align: right; color: var(--accent-color); font-size: 1.4rem;">
                                $<?php echo number_format($subtotal, 2); ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'inc/footer.php'; ?>
