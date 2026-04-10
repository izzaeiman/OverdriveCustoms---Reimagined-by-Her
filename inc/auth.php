<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';

// Check if admin is logged in (Expanded for RBAC)
function isAdminLoggedIn() {
    $adminRoles = ['admin', 'manager', 'order_manager', 'media_manager'];
    return isset($_SESSION['user_id']) && isset($_SESSION['role']) && in_array($_SESSION['role'], $adminRoles);
}

// Check if user has specific role
function hasRole($allowedRoles) {
    if (!isset($_SESSION['role'])) return false;
    
    // 'admin' role has access to everything
    if ($_SESSION['role'] === 'admin') return true;
    
    if (is_array($allowedRoles)) {
        return in_array($_SESSION['role'], $allowedRoles);
    }
    return $_SESSION['role'] === $allowedRoles;
}

// Redirect if not logged in as admin
function requireAdmin() {
    if (!isAdminLoggedIn()) {
        header("Location: " . BASE_URL . "auth/login.php?redirect=admin");
        exit;
    }
}

// Require specific role(s)
function requireRole($allowedRoles) {
    requireAdmin(); // First ensure they are an admin
    
    if (!hasRole($allowedRoles)) {
        // Show restricted access message or redirect
        echo "<h1>Access Denied</h1><p>You do not have permission to access this page.</p><a href='" . BASE_URL . "admin/index.php'>Return to Dashboard</a>";
        exit;
    }
}

// Logout function (Global)
function logout() {
    session_unset();
    session_destroy();
    header("Location: " . BASE_URL);
    exit;
}
?>
