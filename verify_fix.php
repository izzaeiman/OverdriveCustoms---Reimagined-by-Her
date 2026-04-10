<?php
require_once 'inc/db.php';
try {
    $stmt = $pdo->prepare("INSERT INTO analytics (page_url, user_id, ip_address) VALUES (?, ?, ?)");
    $stmt->execute(['/test-verification', null, '127.0.0.1']);
    echo "Verification Insert Successful";
} catch (PDOException $e) {
    echo "Verification Failed: " . $e->getMessage();
}
?>
