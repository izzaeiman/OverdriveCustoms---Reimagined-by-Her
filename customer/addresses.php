<?php
$pageTitle = "My Addresses";
require_once __DIR__ . '/../inc/header.php';
require_once __DIR__ . '/../includes/session_check.php';

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle Delete
if (isset($_GET['delete'])) {
    $addr_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?");
    if ($stmt->execute([$addr_id, $user_id])) {
        $success = "Address deleted.";
    } else {
        $error = "Failed to delete address.";
    }
}

// Handle Add
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $line1 = $_POST['address_line1'];
    $line2 = $_POST['address_line2'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $zip = $_POST['zip'];
    $country = $_POST['country'];

    if (empty($name) || empty($line1) || empty($city) || empty($zip) || empty($country)) {
        $error = "Please fill in all required fields.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO addresses (user_id, name, address_line1, address_line2, city, state, zip, country) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $name, $line1, $line2, $city, $state, $zip, $country])) {
            $success = "Address added successfully.";
        } else {
            $error = "Failed to add address.";
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$addresses = $stmt->fetchAll();
?>

<div class="container" style="padding: 4rem 0;">
    <div style="margin-bottom: 2rem;">
        <a href="index.php" style="color: #888; text-decoration: none;">&larr; Back to Dashboard</a>
    </div>
    <h1 style="color: #e10600; margin-bottom: 2rem;">My Addresses</h1>

    <?php if ($error): ?>
        <div style="background: #e10600; color: #fff; padding: 10px; border-radius: 4px; margin-bottom: 1rem;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div style="background: #28a745; color: #fff; padding: 10px; border-radius: 4px; margin-bottom: 1rem;">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4rem;">
        <!-- List -->
        <div>
            <h3 style="margin-bottom: 1rem;">Saved Addresses</h3>
            <?php if (empty($addresses)): ?>
                <p>No addresses saved.</p>
            <?php else: ?>
                <div style="display: grid; gap: 1rem;">
                    <?php foreach ($addresses as $addr): ?>
                        <div style="background: #1a1a1a; padding: 1rem; border-radius: 4px; position: relative;">
                            <strong style="color: #fff;"><?php echo htmlspecialchars($addr['name']); ?></strong><br>
                            <?php echo htmlspecialchars($addr['address_line1']); ?><br>
                            <?php if ($addr['address_line2']) echo htmlspecialchars($addr['address_line2']) . '<br>'; ?>
                            <?php echo htmlspecialchars($addr['city'] . ', ' . $addr['state'] . ' ' . $addr['zip']); ?><br>
                            <?php echo htmlspecialchars($addr['country']); ?>
                            
                            <a href="?delete=<?php echo $addr['id']; ?>" onclick="return confirm('Are you sure?')" style="position: absolute; top: 1rem; right: 1rem; color: #e10600; text-decoration: none;">&times;</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Add Form -->
        <div>
            <h3 style="margin-bottom: 1rem;">Add New Address</h3>
            <form method="POST" action="" style="background: #1a1a1a; padding: 2rem; border-radius: 8px;">
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: #ccc;">Full Name</label>
                    <input type="text" name="name" required style="width: 100%; padding: 8px; background: #222; border: 1px solid #333; color: #fff;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: #ccc;">Address Line 1</label>
                    <input type="text" name="address_line1" required style="width: 100%; padding: 8px; background: #222; border: 1px solid #333; color: #fff;">
                </div>
                <div style="margin-bottom: 1rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: #ccc;">Address Line 2</label>
                    <input type="text" name="address_line2" style="width: 100%; padding: 8px; background: #222; border: 1px solid #333; color: #fff;">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; color: #ccc;">City</label>
                        <input type="text" name="city" required style="width: 100%; padding: 8px; background: #222; border: 1px solid #333; color: #fff;">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; color: #ccc;">State</label>
                        <input type="text" name="state" style="width: 100%; padding: 8px; background: #222; border: 1px solid #333; color: #fff;">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; color: #ccc;">Zip Code</label>
                        <input type="text" name="zip" required style="width: 100%; padding: 8px; background: #222; border: 1px solid #333; color: #fff;">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; margin-bottom: 0.5rem; color: #ccc;">Country</label>
                        <input type="text" name="country" required style="width: 100%; padding: 8px; background: #222; border: 1px solid #333; color: #fff;">
                    </div>
                </div>
                <button type="submit" class="btn" style="width: 100%;">Save Address</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
