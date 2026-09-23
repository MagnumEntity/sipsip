<?php
// actions/change_username.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_login(); // Ensure user is authenticated

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /sipsip/settings.php");
    exit;
}

$user_id = get_current_user_id();
$username = trim($_POST['username'] ?? '');

// Validation
if (empty($username)) {
    $_SESSION['username_error'] = 'Username cannot be empty.';
    header("Location: /sipsip/settings.php");
    exit;
}

if (strlen($username) < 3 || strlen($username) > 50) {
    $_SESSION['username_error'] = 'Username must be between 3 and 50 characters.';
    header("Location: /sipsip/settings.php");
    exit;
}

try {
    // Check if the username is already taken by another user
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ? AND user_id != ?");
    $stmt->execute([$username, $user_id]);
    if ($stmt->fetch()) {
        $_SESSION['username_error'] = 'That username is already in use.';
        header("Location: /sipsip/settings.php");
        exit;
    }

    // Update username
    $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE user_id = ?");
    $stmt->execute([$username, $user_id]);

    $_SESSION['username'] = $username;
    $_SESSION['username_success'] = 'Username updated successfully.';
    header("Location: /sipsip/settings.php");
    exit;

} catch (PDOException $e) {
    $_SESSION['username_error'] = 'A database error occurred. Please try again.';
    header("Location: /sipsip/settings.php");
    exit;
}
?>
