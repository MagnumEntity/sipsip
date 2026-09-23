<?php
// dashboard.php (Stub for Phase 2)
require_once 'includes/header.php';
require_login(); // Protected page
?>

<div class="auth-container" style="max-width: 600px;">
    <h2>Dashboard Stub</h2>
    <p>You have successfully logged in as User ID: <?php echo escape_html(get_current_user_id()); ?></p>
    <p>This page will be fully implemented in Phase 4.</p>
    <a href="/sipsip/actions/logout.php" class="btn" style="background-color: #e74c3c;">Log Out</a>
</div>

<?php require_once 'includes/footer.php'; ?>
