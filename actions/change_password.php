<?php
// actions/change_password.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_login(); // Ensure user is authenticated

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /sipsip/settings.php");
    exit;
}

$user_id = get_current_user_id();
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validation
if (empty($current_password)) {
    $_SESSION['password_error'] = 'Current password is required.';
    header("Location: /sipsip/settings.php");
    exit;
}

if (empty($new_password)) {
    $_SESSION['password_error'] = 'New password cannot be empty.';
    header("Location: /sipsip/settings.php");
    exit;
}

if (strlen($new_password) < 6) {
    $_SESSION['password_error'] = 'New password must be at least 6 characters.';
    header("Location: /sipsip/settings.php");
    exit;
}

if ($new_password !== $confirm_password) {
    $_SESSION['password_error'] = 'New password and confirmation do not match.';
    header("Location: /sipsip/settings.php");
    exit;
}

try {
    // Fetch current password hash
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($current_password, $user['password_hash'])) {
        $_SESSION['password_error'] = 'Current password is incorrect.';
        header("Location: /sipsip/settings.php");
        exit;
    }

    // Hash the new password securely
    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
    $stmt->execute([$new_hash, $user_id]);

    $_SESSION['password_success'] = 'Password updated successfully.';
    header("Location: /sipsip/settings.php");
    exit;

} catch (PDOException $e) {
    $_SESSION['password_error'] = 'A database error occurred. Please try again.';
    header("Location: /sipsip/settings.php");
    exit;
}
?>
