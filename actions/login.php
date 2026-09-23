<?php
// actions/login.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Only guests can login
require_guest();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /sipsip/login.php");
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $_SESSION['login_error'] = 'Invalid username or password';
    header("Location: /sipsip/login.php");
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT user_id, password_hash FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Secure PHP session: prevent session fixation
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['user_id'];
        
        // Redirect to Dashboard
        header("Location: /sipsip/dashboard.php");
        exit;
    } else {
        // Generic error message for failed login as per spec
        $_SESSION['login_error'] = 'Invalid username or password';
        header("Location: /sipsip/login.php");
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['login_error'] = 'A database error occurred.';
    header("Location: /sipsip/login.php");
    exit;
}
?>
