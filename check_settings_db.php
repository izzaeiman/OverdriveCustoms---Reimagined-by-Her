<?php
require_once 'inc/db.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value TEXT
    )");
    echo "Settings table checked/created.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
