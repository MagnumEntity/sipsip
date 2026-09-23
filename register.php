<?php
// register.php
require_once 'includes/header.php';
require_guest(); // Only non-logged-in users can register

$error = '';
if (isset($_SESSION['register_error'])) {
    $error = $_SESSION['register_error'];
    unset($_SESSION['register_error']);
}
?>

<div class="auth-container">
    <h2>Create Account</h2>
    
    <?php if ($error): ?>
        <div class="error-message"><?php echo escape_html($error); ?></div>
    <?php endif; ?>

    <form action="/sipsip/actions/register.php" method="POST">
        <?php echo csrf_field(); ?>
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required minlength="3" maxlength="50" autocomplete="off">
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required minlength="6">
        </div>
        
        <button type="submit" class="btn">Register</button>
    </form>

    <div class="auth-links">
        Already have an account? <a href="/sipsip/login.php">Log In</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
