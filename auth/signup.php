<?php
$pageTitle = "Sign Up";
require_once __DIR__ . '/../inc/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $error = "Password must contain at least one uppercase letter and one number.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email already registered. <a href='login.php'>Login instead?</a>";
        } else {
            // Create user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, 'customer')");
            if ($stmt->execute([$email, $hashed_password])) {
                $success = "Account created successfully! <a href='login.php'>Login here</a>.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>

<div class="container" style="max-width: 500px; margin: 4rem auto; padding: 2rem; background: #1a1a1a; border-radius: 8px;">
    <h2 style="text-align: center; margin-bottom: 2rem; color: #fff;">Create Account</h2>
    
    <?php if ($error): ?>
        <div style="background: #ffe6e6; color: #d63031; padding: 10px; border-radius: 4px; margin-bottom: 1rem; border: 1px solid #fab1a0;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div style="background: #28a745; color: #fff; padding: 10px; border-radius: 4px; margin-bottom: 1rem;">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: #555; font-weight: 600;">Email Address</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #f9f9f9; color: #333; outline: none; transition: all 0.3s ease;">
        </div>
        
        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: #555; font-weight: 600;">Password</label>
            <input type="password" name="password" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #f9f9f9; color: #333; outline: none; transition: all 0.3s ease;">
            <small style="color: #888;">Min 8 chars, 1 uppercase, 1 number.</small>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: #555; font-weight: 600;">Confirm Password</label>
            <input type="password" name="confirm_password" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #f9f9f9; color: #333; outline: none; transition: all 0.3s ease;">
        </div>

        <button type="submit" class="btn" style="width: 100%; padding: 12px; font-size: 1rem;">Sign Up</button>
    </form>
    
    <p style="text-align: center; margin-top: 2rem; color: #888;">
        Already have an account? <a href="login.php" style="color: var(--accent-color); font-weight: 600;">Login</a>
    </p>
</div>

<?php require_once __DIR__ . '/../inc/footer.php'; ?>
