<?php
require_once 'config.php';
require_once 'inc/db.php';

$username = 'admin';
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE username = ?");
if ($stmt->execute([$hash, $username])) {
    echo "Password for '$username' has been reset to '$password'.<br>";
    echo "New Hash: " . $hash;
} else {
    echo "Error updating password.";
}
?>
