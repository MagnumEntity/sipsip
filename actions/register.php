<?php
// actions/register.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Only guests can register
require_guest();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /sipsip/register.php");
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Basic validation
if (strlen($username) < 3 || strlen($username) > 50 || empty($password) || strlen($password) < 6) {
    $_SESSION['register_error'] = 'Invalid username or password length.';
    header("Location: /sipsip/register.php");
    exit;
}

try {
    // Check if username already exists
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $_SESSION['register_error'] = 'Username already exists.';
        header("Location: /sipsip/register.php");
        exit;
    }

    // Hash password securely
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Start transaction to ensure both user and settings are created
    $pdo->beginTransaction();

    // Create user
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
    $stmt->execute([$username, $password_hash]);
    
    $user_id = $pdo->lastInsertId();

    // Create default user settings
    $stmt = $pdo->prepare("INSERT INTO user_settings (user_id, daily_goal_ml, button_1_ml, button_2_ml, reset_time, notifications_enabled, tips_enabled) VALUES (?, 2000, 250, 500, '00:00:00', 1, 1)");
    $stmt->execute([$user_id]);

    $pdo->commit();

    // Log the user in
    $_SESSION['user_id'] = $user_id;
    session_regenerate_id(true); // Prevent session fixation

    // Send user to Initial Settings
    header("Location: /sipsip/initial_settings.php");
    exit;

} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['register_error'] = 'A database error occurred. Please try again.';
    header("Location: /sipsip/register.php");
    exit;
}
?>
