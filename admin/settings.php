<?php
require_once '../config.php';
require_once '../inc/auth.php';

requireAdmin();
requireRole('manager');

$message = '';
$error = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check for POST max size error
    if (empty($_POST) && $_SERVER['CONTENT_LENGTH'] > 0) {
        $error = "The uploaded file is too large. It exceeds the server's limit of " . ini_get('post_max_size') . ". Please upload a smaller file or increase the limit in php.ini.";
    } else {
        try {
        // Update Text Settings
        $settings = [
            'hero_heading' => $_POST['hero_heading'],
            'hero_subheading' => $_POST['hero_subheading']
        ];

        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (:key, :value) ON DUPLICATE KEY UPDATE setting_value = :value");

        foreach ($settings as $key => $value) {
            $stmt->execute([':key' => $key, ':value' => $value]);
        }

        // Handle File Uploads
        $uploadDir = '../assets/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Hero Video
        if (isset($_FILES['hero_video']) && $_FILES['hero_video']['error'] === UPLOAD_ERR_OK) {
            $videoName = 'hero_video_' . time() . '.' . pathinfo($_FILES['hero_video']['name'], PATHINFO_EXTENSION);
            $videoPath = $uploadDir . $videoName;
            if (move_uploaded_file($_FILES['hero_video']['tmp_name'], $videoPath)) {
                $dbPath = 'assets/uploads/' . $videoName;
                $stmt->execute([':key' => 'hero_video', ':value' => $dbPath]);
            }
        }

        // Logo Icon
        if (isset($_FILES['logo_icon']) && $_FILES['logo_icon']['error'] === UPLOAD_ERR_OK) {
            $logoName = 'logo_icon_' . time() . '.' . pathinfo($_FILES['logo_icon']['name'], PATHINFO_EXTENSION);
            $logoPath = $uploadDir . $logoName;
            if (move_uploaded_file($_FILES['logo_icon']['tmp_name'], $logoPath)) {
                $dbPath = 'assets/uploads/' . $logoName;
                $stmt->execute([':key' => 'logo_icon', ':value' => $dbPath]);
            }
        }

        $message = "Settings updated successfully!";

    } catch (Exception $e) {
        $error = "Error updating settings: " . $e->getMessage();
    }
    } // End else
}

// Fetch Current Settings
$currentSettings = [];
$stmt = $pdo->query("SELECT * FROM site_settings");
while ($row = $stmt->fetch()) {
    $currentSettings[$row['setting_key']] = $row['setting_value'];
}

// Helper to get setting safely
function getSetting($key, $settings) {
    return isset($settings[$key]) ? htmlspecialchars($settings[$key]) : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Settings - Admin</title>
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
        
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; color: #555; font-weight: 600; font-size: 0.95rem; }
        .form-group input[type="text"], .form-group input[type="file"] { width: 100%; padding: 0.8rem; background: #fff; border: 1px solid #ddd; border-radius: 6px; color: #333; outline: none; transition: border-color 0.2s; }
        .form-group input:focus { border-color: var(--accent-color); }
        
        .btn { padding: 0.7rem 1.5rem; background: var(--accent-color); color: #fff; border: none; cursor: pointer; border-radius: 6px; font-weight: 500; transition: opacity 0.2s; display: inline-block; }
        .btn:hover { opacity: 0.9; }
        
        .preview-media { margin-top: 1rem; max-width: 200px; border: 1px solid #eee; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        
        .alert { padding: 1rem; margin-bottom: 1rem; border-radius: 6px; border: 1px solid transparent; }
        .alert-success { background: #e3f9e5; color: #1f7a1f; border-color: #ccebcc; }
        .alert-error { background: #ffe6e6; color: #cc0000; border-color: #ffcccc; }
        
        h1 { font-family: var(--font-heading); font-size: 2.5rem; margin-bottom: 1.5rem; color: var(--text-main); }
        h3 { color: var(--accent-color) !important; font-family: var(--font-heading); margin-bottom: 1.5rem; }
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
                    <a href="media.php">Media Library</a>
                <?php endif; ?>
                
                <?php if (hasRole('manager')): ?>
                    <a href="settings.php" class="active">Settings</a>
                <?php endif; ?>
                
                <a href="../index.php" target="_blank">View Storefront</a>
                <a href="../auth/logout.php">Logout</a>
            </nav>
        </aside>
        <main class="admin-content">
            <h1>Site Settings</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-success"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" style="max-width: 600px;">
                
                <h3 style="margin: 2rem 0 1rem; color: #e10600;">Hero Section</h3>
                
                <div class="form-group">
                    <label>Hero Heading</label>
                    <input type="text" name="hero_heading" value="<?php echo getSetting('hero_heading', $currentSettings); ?>" required>
                </div>

                <div class="form-group">
                    <label>Hero Subheading</label>
                    <input type="text" name="hero_subheading" value="<?php echo getSetting('hero_subheading', $currentSettings); ?>" required>
                </div>

                <div class="form-group">
                    <label>Hero Background Video</label>
                    <input type="file" name="hero_video" accept="video/*">
                    <?php if (!empty($currentSettings['hero_video'])): ?>
                        <p style="margin-top: 0.5rem; font-size: 0.9rem; color: #888;">Current Video: <?php echo $currentSettings['hero_video']; ?></p>
                        <video src="../<?php echo $currentSettings['hero_video']; ?>" class="preview-media" controls></video>
                    <?php endif; ?>
                </div>

                <h3 style="margin: 2rem 0 1rem; color: #e10600;">Branding</h3>

                <div class="form-group">
                    <label>Logo Icon</label>
                    <input type="file" name="logo_icon" accept="image/*">
                    <?php if (!empty($currentSettings['logo_icon'])): ?>
                        <img src="../<?php echo $currentSettings['logo_icon']; ?>" class="preview-media" alt="Current Logo">
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn">Save Settings</button>
            </form>
        </main>
    </div>
</body>
</html>
