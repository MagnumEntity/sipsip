<?php
// includes/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if the user is currently logged in.
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Get the currently logged-in user's ID.
 */
function get_current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Require the user to be logged in. If not, redirect to login page.
 */
function require_login() {
    if (!is_logged_in()) {
        header("Location: /sipsip/login.php");
        exit;
    }
}

/**
 * Prevent logged-in users from accessing guest-only pages (like login/register).
 */
function require_guest() {
    if (is_logged_in()) {
        header("Location: /sipsip/dashboard.php");
        exit;
    }
}
?>
