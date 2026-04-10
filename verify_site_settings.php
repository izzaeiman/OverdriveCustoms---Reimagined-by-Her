<?php
require_once 'inc/db.php';
try {
    $settings = [];
    $stmt = $pdo->query("SELECT * FROM site_settings");
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    if (isset($settings['logo_icon'])) {
        echo "Verification Successful: Found logo_icon setting.";
    } else {
        echo "Verification Warning: Table exists but logo_icon not found.";
    }
} catch (PDOException $e) {
    echo "Verification Failed: " . $e->getMessage();
}
?>
