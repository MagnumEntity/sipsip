<?php
// actions/delete_water.php
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

try {
    // Only delete if the entry belongs to the authenticated user (user isolation)
    $stmt = $pdo->prepare("
        DELETE FROM water_intake_records
        WHERE entry_id = ? AND user_id = ?
    ");
    $stmt->execute([$entry_id, $user_id]);

    if ($stmt->rowCount() === 0) {
        $_SESSION['history_error'] = 'Entry not found or you do not have permission to delete it.';
    } else {
        $_SESSION['history_success'] = 'Water entry deleted.';
    }
} catch (PDOException $e) {
    error_log('delete_water.php PDOException: ' . $e->getMessage());
    $_SESSION['history_error'] = 'A database error occurred while deleting the entry.';
}

header("Location: /sipsip/history.php");
exit;
?>
