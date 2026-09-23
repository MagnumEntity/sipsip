<?php
// actions/add_water.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_login(); // Ensure user is authenticated

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /sipsip/dashboard.php");
    exit;
}

$user_id = get_current_user_id();

// 3. Custom amount validation
// Ensure amount is provided and is a valid integer (rejects decimals, strings)
$amount_ml = filter_input(INPUT_POST, 'amount_ml', FILTER_VALIDATE_INT);

if ($amount_ml === false || $amount_ml <= 0) {
    $_SESSION['water_error'] = 'Please enter a valid positive numeric amount (whole numbers only).';
    header("Location: /sipsip/dashboard.php");
    exit;
}

$confirmed = filter_input(INPUT_POST, 'confirmed', FILTER_VALIDATE_INT) === 1;

// 4. Confirmation rule for amounts greater than 500 mL
if ($amount_ml > 500 && !$confirmed) {
    // Send back to dashboard and ask for confirmation
    $_SESSION['pending_water_amount'] = $amount_ml;
    header("Location: /sipsip/dashboard.php");
    exit;
}

// Validation passed and confirmation handled. Record the intake.
try {
    // Current time
    $recorded_at = (new DateTime())->format('Y-m-d H:i:s');

    // 1. Database recording with user isolation
    $stmt = $pdo->prepare("
        INSERT INTO water_intake_records (user_id, amount_ml, recorded_at) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$user_id, $amount_ml, $recorded_at]);

    $_SESSION['water_success'] = "Successfully added {$amount_ml} mL of water.";
    header("Location: /sipsip/dashboard.php");
    exit;
    
} catch (PDOException $e) {
    $_SESSION['water_error'] = 'A database error occurred while recording water.';
    header("Location: /sipsip/dashboard.php");
    exit;
}
?>
