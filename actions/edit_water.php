<?php
// actions/edit_water.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /sipsip/history.php");
    exit;
}

if (!verify_csrf_token()) {
    $_SESSION['history_error'] = 'Invalid session request. Please try again.';
    header("Location: /sipsip/history.php");
    exit;
}

$user_id = get_current_user_id();

// Validate entry_id
$entry_id = filter_input(INPUT_POST, 'entry_id', FILTER_VALIDATE_INT);
if ($entry_id === false || $entry_id <= 0) {
    $_SESSION['history_error'] = 'Invalid entry ID.';
    header("Location: /sipsip/history.php");
    exit;
}

// Validate new amount
$amount_ml = filter_input(INPUT_POST, 'amount_ml', FILTER_VALIDATE_INT);
if ($amount_ml === false || $amount_ml <= 0) {
    $_SESSION['history_error'] = 'Please enter a valid positive whole-number amount (mL).';
    header("Location: /sipsip/history.php");
    exit;
}

try {
    // Only update if the entry belongs to the authenticated user (user isolation)
    $stmt = $pdo->prepare("
        UPDATE water_intake_records
        SET amount_ml = ?
        WHERE entry_id = ? AND user_id = ?
    ");
    $stmt->execute([$amount_ml, $entry_id, $user_id]);

    if ($stmt->rowCount() === 0) {
        $_SESSION['history_error'] = 'Entry not found or you do not have permission to edit it.';
    } else {
        $_SESSION['history_success'] = "Entry updated to {$amount_ml} mL.";
    }
} catch (PDOException $e) {
    error_log('edit_water.php PDOException: ' . $e->getMessage());
    $_SESSION['history_error'] = 'A database error occurred while updating the entry.';
}

header("Location: /sipsip/history.php");
exit;
?>
