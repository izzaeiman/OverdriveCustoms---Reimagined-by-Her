<?php
$pageTitle = "Access Denied";
require_once 'inc/header.php';
?>

<div class="container" style="padding: 4rem 0; text-align: center;">
    <h1 style="color: #e10600;">Access Denied</h1>
    <p>You do not have permission to view this page.</p>
    <a href="<?php echo BASE_URL; ?>" class="btn">Return Home</a>
</div>

<?php require_once 'inc/footer.php'; ?>
