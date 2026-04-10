<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure config is loaded for BASE_URL
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config.php';
}

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "auth/login.php");
    exit;
}
?>
