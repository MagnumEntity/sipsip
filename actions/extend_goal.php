<?php
// actions/extend_goal.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_login(); // Ensure user is authenticated

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /sipsip/dashboard.php");
    exit;
}

$user_id = get_current_user_id();

// Validate extension amount: positive whole integer only
$extension_amount = filter_input(INPUT_POST, 'extension_amount', FILTER_VALIDATE_INT);

if ($extension_amount === false || $extension_amount <= 0) {
    $_SESSION['water_error'] = 'Please enter a valid positive numeric extension amount.';
    header("Location: /sipsip/dashboard.php");
    exit;
}

$confirmed = filter_input(INPUT_POST, 'confirmed', FILTER_VALIDATE_INT) === 1;

if (!$confirmed) {
    // Stage extension for confirmation on dashboard
    $_SESSION['pending_goal_extension'] = $extension_amount;
    header("Location: /sipsip/dashboard.php");
    exit;
}

// Confirmed: update the daily goal by adding the extension amount
try {
    $stmt = $pdo->prepare("SELECT daily_goal_ml FROM user_settings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $current_goal = (int)$stmt->fetchColumn();

    if ($current_goal <= 0) {
        $current_goal = 2000;
    }

    $new_goal = $current_goal + $extension_amount;

    $stmt = $pdo->prepare("UPDATE user_settings SET daily_goal_ml = ? WHERE user_id = ?");
    $stmt->execute([$new_goal, $user_id]);

    $_SESSION['water_success'] = "Goal successfully extended by {$extension_amount} mL! Your new daily goal is {$new_goal} mL.";
    header("Location: /sipsip/dashboard.php");
    exit;

} catch (PDOException $e) {
    $_SESSION['water_error'] = 'A database error occurred while extending your goal.';
    header("Location: /sipsip/dashboard.php");
    exit;
}
?>
