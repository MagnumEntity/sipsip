<?php
// actions/delete_account.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_login(); // Ensure user is authenticated

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /sipsip/settings.php");
    exit;
}

$user_id = get_current_user_id();
$confirm_delete = $_POST['confirm_delete'] ?? '';

// Server-side confirmation check
if ($confirm_delete !== '1') {
    $_SESSION['account_error'] = 'Please confirm that you want to delete your account by checking the confirmation box.';
    header("Location: /sipsip/settings.php");
    exit;
}

try {
    // Delete user from users table.
    // Database foreign keys have ON DELETE CASCADE for water_intake_records and user_settings.
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // Destroy session completely
    $_SESSION = [];

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();

    // Start a new session solely to set the confirmation flash message
    session_start();
    $_SESSION['login_success'] = 'Account deleted successfully.';

    header("Location: /sipsip/login.php");
    exit;

} catch (PDOException $e) {
    $_SESSION['account_error'] = 'A database error occurred while deleting your account. Please try again.';
    header("Location: /sipsip/settings.php");
    exit;
}
?>
