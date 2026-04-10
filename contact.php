<?php
$pageTitle = "Contact";
require_once 'inc/header.php';
require_once 'inc/mail.php';

$messageSent = false;
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];

    if (sendContactEmail($name, $email, $message)) {
        $messageSent = true;
    } else {
        $error = "Failed to send message. Please try again later.";
    }
}
?>

<div class="container" style="padding-top: 2rem;">


    <div style="max-width: 600px; margin: 0 auto;">
        <?php if ($messageSent): ?>
            <div style="padding: 2rem; background: rgba(0,255,0,0.1); border: 1px solid #00ff00; color: #00ff00; text-align: center; margin-bottom: 2rem;">
                <h3>Message Sent!</h3>
                <p>We'll get back to you as soon as possible.</p>
            </div>
        <?php else: ?>
            <?php if ($error): ?>
                <div style="padding: 1rem; background: rgba(255,0,0,0.1); border: 1px solid #ff0000; color: #ff0000; margin-bottom: 1rem;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="container" style="max-width: 600px; padding: 4rem 0;">
    <h1 style="text-align: center; margin-bottom: 3rem; color: var(--accent-color); font-family: var(--font-heading); font-size: 3.5rem;">Get in Touch</h1>
    
    <?php if ($success): ?>
        <div style="background: #e6ffed; color: #2ecc71; padding: 15px; border-radius: 8px; margin-bottom: 2rem; border: 1px solid #b7ebc5; text-align: center;">
            <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <div style="background: #fff; padding: 3rem; border-radius: var(--radius); box-shadow: var(--shadow);">
        <form method="POST" action="">
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #555; font-weight: 600;">Name</label>
                <input type="text" name="name" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #f9f9f9; color: #333; outline: none; transition: all 0.3s ease;">
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #555; font-weight: 600;">Email</label>
                <input type="email" name="email" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #f9f9f9; color: #333; outline: none; transition: all 0.3s ease;">
            </div>

            <div style="margin-bottom: 2rem;">
                <label style="display: block; margin-bottom: 0.5rem; color: #555; font-weight: 600;">Message</label>
                <textarea name="message" rows="5" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #f9f9f9; color: #333; outline: none; transition: all 0.3s ease; resize: vertical;"></textarea>
            </div>

            <button type="submit" class="btn" style="width: 100%; padding: 12px; font-size: 1rem;">Send Message</button>
        </form>
    </div>
</div>
            <div style="margin-top: 3rem; text-align: center; color: #ccc;">
                <p style="margin-bottom: 0.5rem;">Or reach us directly:</p>
                <p><strong style="color: #fff;">Email:</strong> support@overdrivecustoms.shop</p>
                <p><strong style="color: #fff;">Phone:</strong> +92 337 3333696</p>
                <p><strong style="color: #fff;">Instagram:</strong> <a href="https://instagram.com/overdrivecustoms" target="_blank" style="color: #e10600;">@overdrivecustoms</a></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'inc/footer.php'; ?>
