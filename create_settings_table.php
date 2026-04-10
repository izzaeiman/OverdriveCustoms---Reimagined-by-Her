<?php
require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE TABLE IF NOT EXISTS `site_settings` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `setting_key` varchar(50) NOT NULL UNIQUE,
        `setting_value` text DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sql);
    echo "Table 'site_settings' created successfully.<br>";

    // Insert default values
    $defaults = [
        'hero_video' => '',
        'hero_heading' => 'OVERDRIVE',
        'hero_subheading' => 'FUEL YOUR PASSION',
        'logo_icon' => ''
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES (:key, :value)");

    foreach ($defaults as $key => $value) {
        $stmt->execute([':key' => $key, ':value' => $value]);
        echo "Inserted default for '$key'.<br>";
    }

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>
