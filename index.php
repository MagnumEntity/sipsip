<?php
// index.php
require_once 'includes/auth.php';

if (is_logged_in()) {
    header("Location: /sipsip/dashboard.php");
} else {
    header("Location: /sipsip/login.php");
}
exit;
?>