<?php
// actions/update_settings.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_login(); // Ensure user is authenticated

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /sipsip/settings.php");
    exit;
}

$user_id = get_current_user_id();

// Retrieve POST data
$daily_goal_ml = filter_input(INPUT_POST, 'daily_goal_ml', FILTER_VALIDATE_INT);
$button_1_ml = filter_input(INPUT_POST, 'button_1_ml', FILTER_VALIDATE_INT);
$button_2_ml = filter_input(INPUT_POST, 'button_2_ml', FILTER_VALIDATE_INT);
$reset_time = trim($_POST['reset_time'] ?? '');
$notifications_enabled = isset($_POST['notifications_enabled']) ? 1 : 0;
$tips_enabled = isset($_POST['tips_enabled']) ? 1 : 0;

// Validation for numeric amounts
if ($daily_goal_ml === false || $daily_goal_ml <= 0 ||
    $button_1_ml === false || $button_1_ml <= 0 ||
    $button_2_ml === false || $button_2_ml <= 0) {
    
    $_SESSION['settings_error'] = 'Please enter valid positive numeric values for all water amounts.';
    header("Location: /sipsip/settings.php");
    exit;
}

// Validate time format (HH:MM)
if (!preg_match('/^(?:2[0-3]|[01][0-9]):[0-5][0-9]$/', $reset_time)) {
    $_SESSION['settings_error'] = 'Please enter a valid reset time (HH:MM).';
    header("Location: /sipsip/settings.php");
    exit;
}

// Append seconds to time for database storage (HH:MM:SS)
$reset_time_db = $reset_time . ':00';

try {
    // Update user settings strictly restricted by session user_id
    $stmt = $pdo->prepare("
        UPDATE user_settings 
        SET daily_goal_ml = ?, 
            button_1_ml = ?, 
            button_2_ml = ?, 
            reset_time = ?,
            notifications_enabled = ?,
            tips_enabled = ?
        WHERE user_id = ?
    ");
    
    $stmt->execute([
        $daily_goal_ml,
        $button_1_ml,
        $button_2_ml,
        $reset_time_db,
        $notifications_enabled,
        $tips_enabled,
        $user_id
    ]);

    $_SESSION['settings_success'] = 'Settings saved successfully.';
    header("Location: /sipsip/settings.php");
    exit;

} catch (PDOException $e) {
    $_SESSION['settings_error'] = 'A database error occurred. Please try again.';
    header("Location: /sipsip/settings.php");
    exit;
}
?>
