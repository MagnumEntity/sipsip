<?php
// dashboard.php
require_once 'includes/header.php';
require_login(); // Protected page

$user_id = get_current_user_id();

// Fetch settings for the user
try {
    $stmt = $pdo->prepare("SELECT daily_goal_ml, button_1_ml, button_2_ml FROM user_settings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $settings = $stmt->fetch();
    
    if (!$settings) {
        // Fallback if settings somehow don't exist
        $settings = [
            'daily_goal_ml' => 2000,
            'button_1_ml' => 250,
            'button_2_ml' => 500
        ];
    }
} catch (PDOException $e) {
    die("Database error.");
}

$daily_goal = (int) $settings['daily_goal_ml'];
$button_1 = (int) $settings['button_1_ml'];
$button_2 = (int) $settings['button_2_ml'];
$reset_time = $settings['reset_time'];

// Real intake calculation for Phase 5
$current_intake = get_current_tracking_day_intake($pdo, $user_id, $reset_time); 

// Handle flash messages and confirmation states
$water_error = '';
if (isset($_SESSION['water_error'])) {
    $water_error = $_SESSION['water_error'];
    unset($_SESSION['water_error']);
}

$water_success = '';
if (isset($_SESSION['water_success'])) {
    $water_success = $_SESSION['water_success'];
    unset($_SESSION['water_success']);
}

$pending_amount = 0;
if (isset($_SESSION['pending_water_amount'])) {
    $pending_amount = (int) $_SESSION['pending_water_amount'];
    unset($_SESSION['pending_water_amount']);
}

// Calculate bottle fill percentage
$actual_percentage = calculate_progress_percentage($current_intake, $daily_goal);
$fill_percentage = $actual_percentage > 100 ? 100 : $actual_percentage;

// Determine goal status
$goal_status = '';
$goal_message = '';
if ($current_intake >= $daily_goal && $daily_goal > 0) {
    if ($current_intake == $daily_goal) {
        $goal_status = 'goal-reached';
        $goal_message = "Congratulations! You've reached your daily goal!";
    } else {
        $exceeded_amount = $current_intake - $daily_goal;
        $goal_status = 'goal-exceeded';
        $goal_message = "Goal reached! You've exceeded your goal by {$exceeded_amount} mL.";
    }
}
?>

<div class="dashboard-container">
    
    <!-- 5. Notification area near the top -->
    <div class="notifications-area">
        <strong>Notification:</strong> You're just starting your day. Keep hydrated!
    </div>

    <?php if ($water_error): ?>
        <div class="error-message"><?php echo escape_html($water_error); ?></div>
    <?php endif; ?>
    <?php if ($water_success): ?>
        <div class="error-message" style="background-color: #e8f8f5; color: var(--success-color); border-color: #d1f2eb;">
            <?php echo escape_html($water_success); ?>
        </div>
    <?php endif; ?>

    <?php if ($goal_status): ?>
        <div class="goal-status <?php echo escape_html($goal_status); ?>">
            <?php echo escape_html($goal_message); ?>
        </div>
    <?php endif; ?>

    <?php if ($pending_amount > 0): ?>
        <!-- Confirmation Modal / UI -->
        <div class="auth-container" style="border: 2px solid var(--primary-color);">
            <h3 style="text-align: center; color: var(--primary-dark); margin-top: 0;">Confirm Large Amount</h3>
            <p style="text-align: center; font-size: 1.1rem;">Add <strong><?php echo escape_html($pending_amount); ?> mL</strong> of water?</p>
            <form action="/sipsip/actions/add_water.php" method="POST" style="display: flex; gap: 1rem; justify-content: center; margin-top: 1.5rem;">
                <input type="hidden" name="amount_ml" value="<?php echo escape_html($pending_amount); ?>">
                <input type="hidden" name="confirmed" value="1">
                <a href="/sipsip/dashboard.php" class="btn" style="background-color: #95a5a6; text-decoration: none; text-align: center;">Cancel</a>
                <button type="submit" class="btn">Confirm</button>
            </form>
        </div>
    <?php endif; ?>

    <!-- 1. Progress text above the bottle -->
    <div class="progress-text">
        <?php echo escape_html($current_intake); ?> mL / <?php echo escape_html($daily_goal); ?> mL
        <div class="progress-percent"><?php echo escape_html($actual_percentage); ?>%</div>
    </div>

    <!-- Bottle and Quick-Add Buttons -->
    <div class="bottle-container">
        <!-- 3. Left quick-add button -->
        <form action="/sipsip/actions/add_water.php" method="POST">
            <input type="hidden" name="amount_ml" value="<?php echo escape_html($button_1); ?>">
            <button class="quick-add-btn" type="submit">
                +<?php echo escape_html($button_1); ?> mL
            </button>
        </form>
        
        <!-- 2. Dynamic water bottle -->
        <div class="bottle">
            <div class="bottle-fill" style="height: <?php echo escape_html($fill_percentage); ?>%;"></div>
        </div>

        <!-- 3. Right quick-add button -->
        <form action="/sipsip/actions/add_water.php" method="POST">
            <input type="hidden" name="amount_ml" value="<?php echo escape_html($button_2); ?>">
            <button class="quick-add-btn" type="submit">
                +<?php echo escape_html($button_2); ?> mL
            </button>
        </form>
    </div>

    <!-- 4. Custom water amount input below the bottle/buttons -->
    <form action="/sipsip/actions/add_water.php" method="POST" class="custom-amount-form">
        <input type="number" id="custom_amount_ml" name="amount_ml" placeholder="Custom amount (mL)" min="1" step="1" required>
        <button type="submit" class="btn">Add Water</button>
    </form>

    <!-- 6. Tips area below the custom amount input -->
    <div class="tips-area">
        <strong>Tip:</strong> Drinking water before meals can help you feel fuller!
    </div>
    
</div>

<?php require_once 'includes/footer.php'; ?>
