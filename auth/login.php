<?php
$pageTitle = "Login";
require_once __DIR__ . '/../inc/header.php';

$error = '';

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager', 'order_manager', 'media_manager'])) {
        header("Location: " . BASE_URL . "admin/");
    } else {
        header("Location: " . BASE_URL . "customer/");
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "All fields are required.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email'];

            // Merge guest cart if exists (Optional, but good UX)
            // For now, just redirect
            
            if (isset($user['role']) && in_array($user['role'], ['admin', 'manager', 'order_manager', 'media_manager'])) {
                header("Location: " . BASE_URL . "admin/");
            } else {
                header("Location: " . BASE_URL . "customer/");
            }
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>

<div class="container" style="max-width: 500px; margin: 4rem auto; padding: 3rem; background: #fff; border-radius: var(--radius); box-shadow: var(--shadow);">
    <h2 style="text-align: center; margin-bottom: 2rem; color: #333; font-family: var(--font-heading); font-size: 2.5rem;">Login</h2>
    
    <?php if ($error): ?>
        <div style="background: #ffe6e6; color: #d63031; padding: 10px; border-radius: 4px; margin-bottom: 1rem; border: 1px solid #fab1a0;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: #555; font-size: 0.9rem; font-weight: 600;">Email Address</label>
            <input type="email" name="email" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #f9f9f9; color: #333; outline: none; transition: all 0.3s ease;">
        </div>
        
        <div style="margin-bottom: 2rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: #555; font-size: 0.9rem; font-weight: 600;">Password</label>
            <input type="password" name="password" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #f9f9f9; color: #333; outline: none; transition: all 0.3s ease;">
        </div>

        <button type="submit" class="btn" style="width: 100%; padding: 12px; font-size: 1rem;">Login</button>
    </form>
    
    <p style="text-align: center; margin-top: 2rem; color: #888;">
        Don't have an account? <a href="signup.php" style="color: var(--accent-color); font-weight: 600;">Sign Up</a>
    </p>
</div>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
