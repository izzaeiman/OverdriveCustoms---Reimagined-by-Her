<?php
require_once '../config.php';
require_once '../inc/auth.php';

requireAdmin();
requireRole(['manager', 'media_manager']);

$message = '';
$error = '';

// Handle Create/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';
    $title = $_POST['title'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $image_url = $_POST['image_url'] ?? '';

    if ($action === 'create') {
        $stmt = $pdo->prepare("INSERT INTO products (title, slug, price, description, category_id, image_url) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$title, $slug, $price, $description, $category_id, $image_url])) {
            $message = "Product created successfully.";
        } else {
            $error = "Error creating product.";
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("UPDATE products SET title=?, price=?, description=?, category_id=?, image_url=? WHERE id=?");
        if ($stmt->execute([$title, $price, $description, $category_id, $image_url, $id])) {
            $message = "Product updated successfully.";
        } else {
            $error = "Error updating product.";
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $message = "Product deleted.";
}

// Fetch Products
$stmt = $pdo->query("SELECT p.*, c.title as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC");
$products = $stmt->fetchAll();

// Fetch Categories for Dropdown
$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Overdrive Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root {
            --admin-bg: #f8f9fa;
            --sidebar-bg: #ffffff;
            --card-bg: #ffffff;
            --text-main: #333333;
            --text-mute: #777777;
            --border-color: #eeeeee;
        }
        body { background: var(--admin-bg); color: var(--text-main); font-family: var(--font-body); }
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 260px; background: var(--sidebar-bg); padding: 2rem; border-right: 1px solid var(--border-color); box-shadow: 2px 0 10px rgba(0,0,0,0.02); }
        .admin-content { flex: 1; padding: 2rem 3rem; background: var(--admin-bg); }
        
        .admin-nav a { display: block; padding: 1rem 1.5rem; color: var(--text-mute); text-decoration: none; border-radius: 8px; margin-bottom: 0.5rem; transition: all 0.3s ease; font-weight: 500; }
        .admin-nav a:hover, .admin-nav a.active { color: var(--accent-color); background: #fff0f3; }
        
        .product-table { width: 100%; border-collapse: separate; border-spacing: 0; margin-top: 2rem; background: var(--card-bg); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; }
        .product-table th, .product-table td { padding: 1.2rem; text-align: left; border-bottom: 1px solid var(--border-color); color: var(--text-main); }
        .product-table th { background: #fdfbf7; color: var(--text-mute); text-transform: uppercase; font-size: 0.8rem; font-weight: 600; letter-spacing: 0.5px; }
        .product-table tr:hover td { background: #fcfcfc; }
        
        .btn { padding: 0.6rem 1.2rem; background: var(--accent-color); color: #fff; border: none; cursor: pointer; text-decoration: none; border-radius: 6px; display: inline-block; font-weight: 500; font-size: 0.95rem; transition: opacity 0.2s; }
        .btn:hover { opacity: 0.9; }
        .btn-sm { padding: 0.4rem 0.8rem; font-size: 0.85rem; }
        .btn-delete { background: #ff6b6b; margin-left: 0.5rem; }
        
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; backdrop-filter: blur(2px); }
        .modal-content { background: #fff; width: 500px; margin: 80px auto; padding: 2.5rem; border-radius: 12px; box-shadow: 0 15px 30px rgba(0,0,0,0.1); border: none; }
        .close { float: right; font-size: 1.5rem; cursor: pointer; color: #aaa; transition: color 0.2s; }
        .close:hover { color: #333; }
        
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; color: #555; font-weight: 600; font-size: 0.95rem; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 0.8rem; background: #fff; border: 1px solid #ddd; color: #333; box-sizing: border-box; border-radius: 6px; font-family: inherit; font-size: 0.95rem; transition: border-color 0.2s; }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus { border-color: var(--accent-color); outline: none; }
        
        h1, h2 { font-family: var(--font-heading); color: var(--text-main); }
        h1 { margin-bottom: 0; font-size: 2.5rem; }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <h2 style="color: var(--accent-color); font-family: var(--font-heading); font-size: 2rem; margin-bottom: 3rem; text-align: center;">Overdrive</h2>
            <nav class="admin-nav">
                <a href="index.php">Dashboard</a>
                
                <?php if (hasRole(['manager', 'media_manager'])): ?>
                    <a href="products.php" class="active">Products</a>
                <?php endif; ?>
                
                <?php if (hasRole('order_manager')): ?>
                    <a href="orders.php">Orders</a>
                <?php endif; ?>
                
                <?php if (hasRole('manager')): ?>
                    <a href="users.php">Users</a>
                <?php endif; ?>
                
                <?php if (hasRole(['manager', 'media_manager'])): ?>
                    <a href="media.php">Media Library</a>
                <?php endif; ?>
                
                <?php if (hasRole('manager')): ?>
                    <a href="settings.php">Settings</a>
                <?php endif; ?>
                
                <a href="../index.php" target="_blank">View Storefront</a>
                <a href="../auth/logout.php">Logout</a>
            </nav>
        </aside>
        <main class="admin-content">
            <div class="header-bar" style="display: flex; justify-content: space-between; align-items: center;">
                <h1>Products</h1>
                <a href="product_form.php" class="btn">Add New Product</a>
            </div>
            
            <?php if ($message): ?>
                <div style="padding: 1rem; background: rgba(0,255,0,0.1); color: #00ff00; margin-bottom: 1rem;"><?php echo $message; ?></div>
            <?php endif; ?>

            <table class="product-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Price</th>
                        <th>Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($product['title']); ?></td>
                            <td>$<?php echo htmlspecialchars($product['price']); ?></td>
                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                            <td>
                                <a href="product_form.php?id=<?php echo $product['id']; ?>" class="btn btn-sm">Edit</a>
                                <a href="?delete=<?php echo $product['id']; ?>" class="btn btn-sm btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>

    <script>
        // No inline scripts needed for the table view
    </script>
</body>
</html>
