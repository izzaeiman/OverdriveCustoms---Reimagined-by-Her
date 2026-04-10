<?php
require 'config.php';
require 'inc/db.php';

$users = [
    [
        'email' => 'manager@test.com',
        'password' => password_hash('123456', PASSWORD_DEFAULT),
        'role' => 'manager'
    ],
    [
        'email' => 'orders@test.com',
        'password' => password_hash('123456', PASSWORD_DEFAULT),
        'role' => 'order_manager'
    ],
    [
        'email' => 'media@test.com',
        'password' => password_hash('123456', PASSWORD_DEFAULT),
        'role' => 'media_manager'
    ]
];

foreach ($users as $user) {
    try {
        $stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$user['email'], $user['password'], $user['role']]);
        echo "Created user: {$user['email']} ({$user['role']})\n";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            // Duplicate entry, update role instead
            $stmt = $pdo->prepare("UPDATE users SET role = ?, password = ? WHERE email = ?");
            $stmt->execute([$user['role'], $user['password'], $user['email']]);
            echo "Updated user: {$user['email']} to role ({$user['role']})\n";
        } else {
            echo "Error creating {$user['email']}: " . $e->getMessage() . "\n";
        }
    }
}
?>
