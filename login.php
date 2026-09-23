<?php
// login.php
require_once 'includes/header.php';
require_guest(); // Only non-logged-in users can login

$error = '';
if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

$success = '';
if (isset($_SESSION['login_success'])) {
    $success = $_SESSION['login_success'];
    unset($_SESSION['login_success']);
}
?>

<div class="auth-container">
    <h2>Log In</h2>
    
    <?php if ($error): ?>
        <div class="error-message"><?php echo escape_html($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success-message"><?php echo escape_html($success); ?></div>
    <?php endif; ?>

    <form action="/sipsip/actions/login.php" method="POST">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required autocomplete="username">
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">
        </div>
        
        <button type="submit" class="btn">Log In</button>
    </form>

    <div class="auth-links">
        Don't have an account? <a href="/sipsip/register.php">Register</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
