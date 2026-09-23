<?php
// actions/logout.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Only allow POST to prevent CSRF-based forced logout via GET links
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /sipsip/dashboard.php");
    exit;
}

// Verify CSRF token
if (!verify_csrf_token()) {
    header("Location: /sipsip/dashboard.php");
    exit;
}

// Properly destroy/clear the authenticated session
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Return the user to the login page
header("Location: /sipsip/login.php");
exit;
?>
