<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'overdrive');

// SMTP Configuration (PHPMailer)
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_USER', 'user@example.com');
define('SMTP_PASS', 'secret');
define('SMTP_PORT', 587);

// App Configuration
define('BASE_URL', 'http://localhost/Overdrive/');
define('SITE_NAME', 'Overdrive Customs');
define('ADMIN_EMAIL', 'support@overdrivecustoms.shop');

// Error Reporting (Disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
