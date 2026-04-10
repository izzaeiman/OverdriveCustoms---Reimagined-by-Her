<?php
require_once '../config.php';
require_once '../inc/auth.php';

requireAdmin();

$id = $_GET['id'] ?? null;
$product = null;
$images = [];
$options = [];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    // Fetch Images
    $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ?");
    $stmt->execute([$id]);
    $images = $stmt->fetchAll();

    // Fetch Options
    $stmt = $pdo->prepare("SELECT * FROM product_options WHERE product_id = ?");
    $stmt->execute([$id]);
    $options = $stmt->fetchAll();
}

// Fetch Categories
// Fetch Categories
$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll();

// Fetch Media Library
$stmt = $pdo->query("SELECT * FROM media ORDER BY uploaded_at DESC");
$mediaLibrary = $stmt->fetchAll();

// Handle Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $image_url = $_POST['image_url']; // Main image

    // Handle File Upload
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
        $uploadDir = '../uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = basename($_FILES['image_file']['name']);
        $targetFile = $uploadDir . time() . '_' . $fileName;
        
        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $targetFile)) {
            // Success
            // Store relative path for DB
            // We want "uploads/filename" for the frontend to work
            // Since we moved to "../uploads/filename", the relative path from root is "uploads/filename"
            // Wait, we used time() prefix. We need to capture that.
            $dbFileName = time() . '_' . $fileName;
            $image_url = "uploads/" . $dbFileName;
            
            // Optional: Add to media library table so it shows up there too
            $stmt = $pdo->prepare("INSERT INTO media (filename, original_name, category) VALUES (?, ?, 'Product')");
            $stmt->execute([$dbFileName, $fileName]);
        }
    }

    try {
        $pdo->beginTransaction();

        if ($id) {
            $stmt = $pdo->prepare("UPDATE products SET title=?, price=?, description=?, category_id=?, image_url=? WHERE id=?");
            $stmt->execute([$title, $price, $description, $category_id, $image_url, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO products (title, slug, price, description, category_id, image_url) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $price, $description, $category_id, $image_url]);
            $id = $pdo->lastInsertId();
        }

        // Handle Additional Images
        // For simplicity, we'll just delete and re-insert for now or handle via AJAX in a real app
        // Here we will just handle the main image update above. 
        // A full image manager would require file uploads which is complex for this single file.
        // Let's assume user adds image URLs manually for now as per previous pattern.
        
        if (isset($_POST['new_images']) && is_array($_POST['new_images'])) {
            $stmt = $pdo->prepare("INSERT INTO product_images (product_id, image_url) VALUES (?, ?)");
            foreach ($_POST['new_images'] as $url) {
                if (!empty($url)) $stmt->execute([$id, $url]);
            }
        }

        // Handle Options
        // Clear old options and re-insert (simple way)
        if ($id) {
            $pdo->prepare("DELETE FROM product_options WHERE product_id = ?")->execute([$id]);
        }
        
        if (isset($_POST['options']) && is_array($_POST['options'])) {
            $stmt = $pdo->prepare("INSERT INTO product_options (product_id, option_group, option_value, price_modifier) VALUES (?, ?, ?, ?)");
            foreach ($_POST['options'] as $opt) {
                if (!empty($opt['group']) && !empty($opt['value'])) {
                    $stmt->execute([$id, $opt['group'], $opt['value'], $opt['price'] ?? 0]);
                }
            }
        }

        $pdo->commit();
        header("Location: products.php?msg=saved");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error saving product: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $id ? 'Edit' : 'Add'; ?> Product - Overdrive Admin</title>
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
        
        .admin-container { max-width: 900px; background: var(--card-bg); padding: 2.5rem; border-radius: 12px; box-shadow: var(--shadow); border: 1px solid var(--border-color); }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; color: #555; font-weight: 600; font-size: 0.9rem; }
        .form-control { width: 100%; padding: 0.8rem; background: #fff; border: 1px solid #ddd; color: #333; border-radius: 6px; transition: border-color 0.2s; outline: none; }
        .form-control:focus { border-color: var(--accent-color); }
        .row { display: flex; gap: 1.5rem; }
        .col { flex: 1; }
        .dynamic-list { margin-top: 0.5rem; }
        .dynamic-item { display: flex; gap: 0.5rem; margin-bottom: 0.5rem; align-items: center; }
        
        h1 { font-family: var(--font-heading); color: var(--accent-color); margin-bottom: 2rem; font-size: 2.5rem; }
        
        .btn { padding: 0.7rem 1.5rem; background: var(--accent-color); color: #fff; border: none; cursor: pointer; border-radius: 6px; font-weight: 500; transition: opacity 0.2s; }
        .btn:hover { opacity: 0.9; }
        .btn-outline { background: transparent; border: 1px solid #ddd; color: #555; }
        .btn-outline:hover { border-color: var(--accent-color); color: var(--accent-color); }
        .btn-sm { padding: 0.4rem 0.8rem; font-size: 0.85rem; }
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
            <div class="header-bar" style="margin-bottom: 2rem;">
                <a href="products.php" class="btn btn-outline" style="text-decoration: none; margin-bottom: 1rem; display: inline-block;">&larr; Back to Products</a>
                <h1><?php echo $id ? 'Edit' : 'Add'; ?> Product</h1>
            </div>
            
            <div class="admin-container">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($product['title'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="row">
                        <div class="col form-group">
                            <label>Price</label>
                            <input type="number" step="0.01" name="price" class="form-control" value="<?php echo htmlspecialchars($product['price'] ?? ''); ?>" required>
                        </div>
                        <div class="col form-group">
                            <label>Category</label>
                            <select name="category_id" class="form-control">
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo ($product['category_id'] ?? '') == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Main Image</label>
                        <div style="margin-bottom: 0.5rem;">
                            <input type="file" name="image_file" class="form-control" accept="image/*">
                            <small style="color: #666;">Upload a new image from your computer</small>
                        </div>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <span style="font-weight: 500;">OR</span>
                            <input type="text" name="image_url" id="image_url" class="form-control" value="<?php echo htmlspecialchars($product['image_url'] ?? ''); ?>" placeholder="Enter URL or Select from Library">
                            <button type="button" class="btn btn-outline" onclick="openMediaModal('image_url')" style="white-space: nowrap;">Select from Library</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="5" style="resize: vertical;"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                    </div>

                    <!-- Additional Images -->
                    <div class="form-group" style="background: #fdfbf7; padding: 1.5rem; border-radius: 8px; border: 1px solid #eee;">
                        <label style="margin-bottom: 1rem;">Additional Images</label>
                        <div id="image-list">
                            <?php foreach ($images as $img): ?>
                                <div class="dynamic-item">
                                    <input type="text" name="new_images[]" class="form-control" value="<?php echo htmlspecialchars($img['image_url']); ?>">
                                    <button type="button" class="btn btn-outline" onclick="this.parentElement.remove()">X</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-sm" onclick="addImageField()" style="margin-top: 0.5rem; background: #6c757d; border-color: #6c757d;">+ Add Image</button>
                    </div>

                    <!-- Options -->
                    <div class="form-group" style="background: #fdfbf7; padding: 1.5rem; border-radius: 8px; border: 1px solid #eee;">
                        <label style="margin-bottom: 1rem;">Product Options (Size, Color, etc.)</label>
                        <div id="option-list">
                            <?php foreach ($options as $i => $opt): ?>
                                <div class="dynamic-item">
                                    <input type="text" name="options[<?php echo $i; ?>][group]" class="form-control" placeholder="Group (e.g. Size)" value="<?php echo htmlspecialchars($opt['option_group']); ?>">
                                    <input type="text" name="options[<?php echo $i; ?>][value]" class="form-control" placeholder="Value (e.g. M)" value="<?php echo htmlspecialchars($opt['option_value']); ?>">
                                    <input type="number" step="0.01" name="options[<?php echo $i; ?>][price]" class="form-control" placeholder="Price Mod" value="<?php echo htmlspecialchars($opt['price_modifier']); ?>">
                                    <button type="button" class="btn btn-outline" onclick="this.parentElement.remove()">X</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-sm" onclick="addOptionField()" style="margin-top: 0.5rem; background: #6c757d; border-color: #6c757d;">+ Add Option</button>
                    </div>

                    <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                        <button type="submit" class="btn" style="padding: 12px 24px; font-size: 1.1rem;">Save Product</button>
                        <a href="products.php" class="btn btn-outline" style="text-decoration: none; padding: 12px 24px;">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <!-- Media Selection Modal -->
    <div id="mediaModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999;">
        <div class="modal-content" style="background: #fff; width: 80%; max-width: 900px; margin: 40px auto; padding: 20px; border-radius: 8px; height: 80vh; display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="font-family: var(--font-heading); color: var(--accent-color); margin: 0;">Select Image</h2>
                <span onclick="closeMediaModal()" style="cursor: pointer; font-size: 2rem; color: #666;">&times;</span>
            </div>
            
            <div style="overflow-y: auto; flex: 1; display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1rem;">
                <?php foreach ($mediaLibrary as $item): ?>
                    <div style="position: relative; cursor: pointer; border: 2px solid transparent;" onclick="selectImage('assets/uploads/<?php echo $item['filename']; ?>')">
                        <?php if (strpos($item['filename'], '.mp4') !== false): ?>
                            <video src="../uploads/<?php echo $item['filename']; ?>" style="width: 100%; height: 120px; object-fit: cover;"></video>
                        <?php else: ?>
                            <img src="../uploads/<?php echo $item['filename']; ?>" style="width: 100%; height: 120px; object-fit: cover;">
                        <?php endif; ?>
                        <div style="font-size: 0.8rem; text-align: center; padding: 5px; background: #f8f9fa; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?php echo htmlspecialchars($item['original_name']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        // Global state
        let currentInputId = '';

        // Expose functions globally to ensure onclick attributes find them
        window.openMediaModal = function(inputId) {
            currentInputId = inputId;
            const modal = document.getElementById('mediaModal');
            if (modal) modal.style.display = 'block';
        };

        window.closeMediaModal = function() {
            const modal = document.getElementById('mediaModal');
            if (modal) modal.style.display = 'none';
        };

        window.selectImage = function(url) {
            if (currentInputId) {
                // Ensure we get just the filename to construct the clean path
                const filename = url.split('/').pop();
                const cleanUrl = 'uploads/' + filename;
                
                const input = document.getElementById(currentInputId);
                if (input) input.value = cleanUrl;
            }
            window.closeMediaModal();
        };

        window.addImageField = function() {
            const div = document.createElement('div');
            div.className = 'dynamic-item';
            const uniqueId = 'img_' + Date.now();
            // Note: We use type="button" to prevent form submission
            div.innerHTML = `
                <input type="text" name="new_images[]" id="${uniqueId}" class="form-control" placeholder="Image URL">
                <button type="button" class="btn btn-outline" onclick="window.openMediaModal('${uniqueId}')">Select</button>
                <button type="button" class="btn btn-outline" onclick="this.parentElement.remove()" style="color: red; border-color: red;">X</button>
            `;
            document.getElementById('image-list').appendChild(div);
        };

        // Initialize index based on existing options
        let optIndex = <?php echo count($options) > 0 ? count($options) : 0; ?>;

        window.addOptionField = function() {
            const div = document.createElement('div');
            div.className = 'dynamic-item';
            div.innerHTML = `
                <input type="text" name="options[${optIndex}][group]" class="form-control" placeholder="Group (e.g. Size)">
                <input type="text" name="options[${optIndex}][value]" class="form-control" placeholder="Value (e.g. M)">
                <input type="number" step="0.01" name="options[${optIndex}][price]" class="form-control" placeholder="Price Mod">
                <button type="button" class="btn btn-outline" onclick="this.parentElement.remove()" style="color: red; border-color: red;">X</button>
            `;
            document.getElementById('option-list').appendChild(div);
            optIndex++;
        };
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('mediaModal');
            if (event.target == modal) {
                window.closeMediaModal();
            }
        };
    </script>
</body>
</html>
