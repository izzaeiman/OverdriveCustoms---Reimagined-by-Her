<?php
require_once '../config.php';
require_once '../inc/auth.php';

requireAdmin();
requireRole(['manager', 'media_manager']);

// Create media table if it doesn't exist
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS media (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL,
        original_name VARCHAR(255) NOT NULL,
        category VARCHAR(50) DEFAULT NULL,
        product_id INT DEFAULT NULL,
        visible TINYINT(1) NOT NULL DEFAULT 1,
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {
    // Table might already exist or creation failed, continue anyway
}

$message = '';
$error = '';

// Handle Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
    $targetDir = "../uploads/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    $fileName = basename($_FILES["media_file"]["name"]);
    $targetFilePath = $targetDir . time() . '_' . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'mp4');
    if (in_array(strtolower($fileType), $allowTypes)) {
        if (move_uploaded_file($_FILES["media_file"]["tmp_name"], $targetFilePath)) {
            $dbFileName = time() . '_' . $fileName;
            $originalName = $fileName;
            $category = $_POST['category'] ?? 'Uncategorized';
            
            $stmt = $pdo->prepare("INSERT INTO media (filename, original_name, category) VALUES (?, ?, ?)");
            if ($stmt->execute([$dbFileName, $originalName, $category])) {
                $message = "File uploaded successfully.";
            } else {
                $error = "Database error.";
            }
        } else {
            $error = "Sorry, there was an error uploading your file.";
        }
    } else {
        $error = "Sorry, only JPG, JPEG, PNG, GIF, & MP4 files are allowed.";
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("SELECT filename FROM media WHERE id = ?");
    $stmt->execute([$id]);
    $file = $stmt->fetch();
    
    if ($file) {
        $filePath = "../uploads/" . $file['filename'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $stmt = $pdo->prepare("DELETE FROM media WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Media deleted.";
    }
}

// Fetch Media
try {
    $stmt = $pdo->query("SELECT * FROM media ORDER BY uploaded_at DESC");
    $mediaItems = $stmt->fetchAll();
} catch (PDOException $e) {
    // If table still doesn't exist, create it again and set empty array
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS media (
            id INT AUTO_INCREMENT PRIMARY KEY,
            filename VARCHAR(255) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            category VARCHAR(50) DEFAULT NULL,
            product_id INT DEFAULT NULL,
            visible TINYINT(1) NOT NULL DEFAULT 1,
            uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $mediaItems = [];
    } catch (PDOException $e2) {
        $mediaItems = [];
        $error = "Database error: Could not create media table.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media Library - Overdrive Admin</title>
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
        
        .media-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1.5rem; margin-top: 2rem; }
        .media-item { background: #fff; padding: 0.5rem; border: none; border-radius: 8px; position: relative; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s; }
        .media-item:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(0,0,0,0.1); }
        .media-item img, .media-item video { width: 100%; height: 160px; object-fit: cover; border-radius: 6px; }
        .media-info { font-size: 0.85rem; color: #666; margin-top: 0.8rem; word-break: break-all; padding: 0 0.5rem 0.5rem; }
        .delete-btn { display: inline-block; margin-top: 0.5rem; color: #ff6b6b; text-decoration: none; font-size: 0.85rem; font-weight: 500; padding: 0 0.5rem 0.5rem; }
        
        .upload-form { background: #fff; padding: 2rem; border-radius: 12px; margin-bottom: 2rem; border: none; box-shadow: var(--shadow); }
        .upload-form h3 { margin-top: 0; color: var(--text-main); }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; color: #555; font-weight: 600; font-size: 0.9rem; }
        .form-group input, .form-group select { padding: 0.8rem; background: #fff; border: 1px solid #ddd; border-radius: 6px; color: #333; width: 100%; max-width: 350px; outline: none; transition: border-color 0.2s; }
        .form-group input:focus, .form-group select:focus { border-color: var(--accent-color); }
        
        .btn { padding: 0.7rem 1.5rem; background: var(--accent-color); color: #fff; border: none; cursor: pointer; border-radius: 6px; font-weight: 500; font-size: 0.95rem; transition: opacity 0.2s; }
        .btn:hover { opacity: 0.9; }
        
        .alert { padding: 1rem; margin-bottom: 1rem; border-radius: 6px; border: 1px solid transparent; }
        .alert-success { background: #e3f9e5; color: #1f7a1f; border-color: #ccebcc; }
        .alert-error { background: #ffe6e6; color: #cc0000; border-color: #ffcccc; }
        
        h1 { font-family: var(--font-heading); font-size: 2.5rem; margin-bottom: 1.5rem; color: var(--text-main); }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <h2 style="color: var(--accent-color); font-family: var(--font-heading); font-size: 2rem; margin-bottom: 3rem; text-align: center;">Overdrive</h2>
            <nav class="admin-nav">
                <a href="index.php">Dashboard</a>
                
                <?php if (hasRole(['manager', 'media_manager'])): ?>
                    <a href="products.php">Products</a>
                <?php endif; ?>
                
                <?php if (hasRole('order_manager')): ?>
                    <a href="orders.php">Orders</a>
                <?php endif; ?>
                
                <?php if (hasRole('manager')): ?>
                    <a href="users.php">Users</a>
                <?php endif; ?>
                
                <?php if (hasRole(['manager', 'media_manager'])): ?>
                    <a href="media.php" class="active">Media Library</a>
                <?php endif; ?>
                
                <?php if (hasRole('manager')): ?>
                    <a href="settings.php">Settings</a>
                <?php endif; ?>
                
                <a href="../index.php" target="_blank">View Storefront</a>
                <a href="../auth/logout.php">Logout</a>
            </nav>
        </aside>
        <main class="admin-content">
            <h1>Media Library</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="upload-form">
                <h3>Upload New Media</h3>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Select File</label>
                        <input type="file" name="media_file" required>
                    </div>
                    <div class="form-group">
                        <label>Category (Optional)</label>
                        <select name="category">
                            <option value="General">General</option>
                            <option value="German">German</option>
                            <option value="Japanese">Japanese</option>
                            <option value="BMW">BMW</option>
                            <option value="Porsche">Porsche</option>
                        </select>
                    </div>
                    <button type="submit" class="btn">Upload</button>
                </form>
            </div>

            <div class="media-grid">
                <?php foreach ($mediaItems as $item): ?>
                    <div class="media-item">
                        <?php if (strpos($item['filename'], '.mp4') !== false): ?>
                            <video src="../uploads/<?php echo $item['filename']; ?>" controls></video>
                        <?php else: ?>
                            <img src="../uploads/<?php echo $item['filename']; ?>" alt="<?php echo htmlspecialchars($item['original_name']); ?>">
                        <?php endif; ?>
                        <div class="media-info">
                            <strong><?php echo htmlspecialchars($item['category']); ?></strong><br>
                            <?php echo htmlspecialchars($item['original_name']); ?>
                        </div>
                        <a href="?delete=<?php echo $item['id']; ?>" class="delete-btn" onclick="return confirm('Are you sure?')">Delete</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</body>
</html>
