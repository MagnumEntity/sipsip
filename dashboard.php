<?php
// dashboard.php
require_once 'includes/header.php';
require_login(); // Protected page

$user_id = get_current_user_id();

// Fetch settings for the user
try {
    $stmt = $pdo->prepare("
        SELECT daily_goal_ml, button_1_ml, button_2_ml, reset_time, notifications_enabled, tips_enabled 
        FROM user_settings 
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    $settings = $stmt->fetch();
    
    if (!$settings) {
        $settings = [
            'daily_goal_ml' => 2000,
            'button_1_ml' => 250,
            'button_2_ml' => 500,
            'reset_time' => '00:00:00',
            'notifications_enabled' => 1,
            'tips_enabled' => 1
        ];
    }
} catch (PDOException $e) {
    die("Database error.");
}

$daily_goal = (int) $settings['daily_goal_ml'];
$button_1 = (int) $settings['button_1_ml'];
$button_2 = (int) $settings['button_2_ml'];
$reset_time = $settings['reset_time'] ?? '00:00:00';
$notifications_enabled = (int) ($settings['notifications_enabled'] ?? 1);
$tips_enabled = (int) ($settings['tips_enabled'] ?? 1);

// Real intake calculation for Phase 5/7
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

// Pending water amount (>500 mL confirmation)
$pending_amount = 0;
if (isset($_SESSION['pending_water_amount'])) {
    $pending_amount = (int) $_SESSION['pending_water_amount'];
    unset($_SESSION['pending_water_amount']);
}

// Pending goal extension confirmation
$pending_goal_extension = 0;
if (isset($_SESSION['pending_goal_extension'])) {
    $pending_goal_extension = (int) $_SESSION['pending_goal_extension'];
    unset($_SESSION['pending_goal_extension']);
}

// Calculate bottle fill percentage
$actual_percentage = calculate_progress_percentage($current_intake, $daily_goal);
$fill_percentage = $actual_percentage > 100 ? 100 : $actual_percentage;

// Generate context-aware notification and tip
$notification = get_dashboard_notification($current_intake, $daily_goal, $notifications_enabled);
$tip = get_dashboard_tip($current_intake, $daily_goal, $tips_enabled);
?>

<div class="dashboard-container">
    
    <!-- 2. Context-Aware Notification Area -->
    <?php if ($notification): ?>
        <div class="notifications-area <?php echo escape_html($notification['class']); ?>">
            <button type="button" class="dismiss-btn" onclick="this.parentElement.style.display='none';" aria-label="Dismiss">&times;</button>
            <strong>Notification:</strong> <?php echo escape_html($notification['message']); ?>

            <!-- Goal Extension Prompt when over goal -->
            <?php if ($notification['type'] === 'over_goal'): ?>
                <div class="goal-extension-container" style="margin-top: 1rem; padding-top: 0.8rem; border-top: 1px dashed rgba(0,0,0,0.15);">
                    <p style="margin: 0 0 0.8rem 0; font-weight: bold;">
                        You've exceeded your goal. Would you like to extend today's goal?
                    </p>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;">
                        <form action="/sipsip/actions/extend_goal.php" method="POST" style="margin: 0;">
                            <input type="hidden" name="extension_amount" value="250">
                            <button type="submit" class="btn" style="padding: 0.4rem 0.8rem; font-size: 0.9rem; width: auto;">+250 mL</button>
                        </form>
                        <form action="/sipsip/actions/extend_goal.php" method="POST" style="margin: 0;">
                            <input type="hidden" name="extension_amount" value="500">
                            <button type="submit" class="btn" style="padding: 0.4rem 0.8rem; font-size: 0.9rem; width: auto;">+500 mL</button>
                        </form>
                        <form action="/sipsip/actions/extend_goal.php" method="POST" style="margin: 0;">
                            <input type="hidden" name="extension_amount" value="1000">
                            <button type="submit" class="btn" style="padding: 0.4rem 0.8rem; font-size: 0.9rem; width: auto;">+1000 mL</button>
                        </form>
                        <form action="/sipsip/actions/extend_goal.php" method="POST" style="display: flex; gap: 0.5rem; margin: 0; align-items: center;">
                            <input type="number" name="extension_amount" placeholder="Custom mL" min="1" step="1" required style="width: 100px; padding: 0.4rem; font-size: 0.9rem; border: 1px solid var(--border-color); border-radius: 4px;">
                            <button type="submit" class="btn" style="padding: 0.4rem 0.8rem; font-size: 0.9rem; width: auto;">Extend</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($water_error): ?>
        <div class="error-message"><?php echo escape_html($water_error); ?></div>
    <?php endif; ?>
    <?php if ($water_success): ?>
        <div class="error-message" style="background-color: #e8f8f5; color: var(--success-color); border-color: #d1f2eb;">
            <?php echo escape_html($water_success); ?>
        </div>
    <?php endif; ?>

    <!-- Goal Extension Confirmation Modal -->
    <?php if ($pending_goal_extension > 0): ?>
        <div class="auth-container" style="border: 2px solid var(--primary-color);">
            <h3 style="text-align: center; color: var(--primary-dark); margin-top: 0;">Confirm Goal Extension</h3>
            <p style="text-align: center; font-size: 1.1rem;">
                Extend your daily goal by <strong><?php echo escape_html($pending_goal_extension); ?> mL</strong>?<br>
                <span style="font-size: 0.9rem; color: #666;">New daily goal will be <?php echo escape_html($daily_goal + $pending_goal_extension); ?> mL.</span>
            </p>
            <form action="/sipsip/actions/extend_goal.php" method="POST" style="display: flex; gap: 1rem; justify-content: center; margin-top: 1.5rem;">
                <input type="hidden" name="extension_amount" value="<?php echo escape_html($pending_goal_extension); ?>">
                <input type="hidden" name="confirmed" value="1">
                <a href="/sipsip/dashboard.php" class="btn" style="background-color: #95a5a6; text-decoration: none; text-align: center; width: auto; padding: 0.8rem 1.5rem;">Cancel</a>
                <button type="submit" class="btn" style="width: auto; padding: 0.8rem 1.5rem;">Confirm</button>
            </form>
        </div>
    <?php endif; ?>

    <!-- Water Entry Confirmation Modal (>500 mL) -->
    <?php if ($pending_amount > 0): ?>
        <div class="auth-container" style="border: 2px solid var(--primary-color);">
            <h3 style="text-align: center; color: var(--primary-dark); margin-top: 0;">Confirm Large Amount</h3>
            <p style="text-align: center; font-size: 1.1rem;">Add <strong><?php echo escape_html($pending_amount); ?> mL</strong> of water?</p>
            <form action="/sipsip/actions/add_water.php" method="POST" style="display: flex; gap: 1rem; justify-content: center; margin-top: 1.5rem;">
                <input type="hidden" name="amount_ml" value="<?php echo escape_html($pending_amount); ?>">
                <input type="hidden" name="confirmed" value="1">
                <a href="/sipsip/dashboard.php" class="btn" style="background-color: #95a5a6; text-decoration: none; text-align: center; width: auto; padding: 0.8rem 1.5rem;">Cancel</a>
                <button type="submit" class="btn" style="width: auto; padding: 0.8rem 1.5rem;">Confirm</button>
            </form>
        </div>
    <?php endif; ?>

    <!-- 3. Progress text above the bottle -->
    <div class="progress-text">
        <?php echo escape_html($current_intake); ?> mL / <?php echo escape_html($daily_goal); ?> mL
        <div class="progress-percent"><?php echo escape_html($actual_percentage); ?>%</div>
    </div>

    <!-- 4. Dynamic Water Bottle and 5. Quick-Add Buttons -->
    <div class="bottle-container">
        <!-- Left quick-add button -->
        <form action="/sipsip/actions/add_water.php" method="POST">
            <input type="hidden" name="amount_ml" value="<?php echo escape_html($button_1); ?>">
            <button class="quick-add-btn" type="submit">
                +<?php echo escape_html($button_1); ?> mL
            </button>
        </form>
        
        <!-- Dynamic water bottle -->
        <div class="bottle">
            <div class="bottle-fill" style="height: <?php echo escape_html($fill_percentage); ?>%;"></div>
        </div>

        <!-- Right quick-add button -->
        <form action="/sipsip/actions/add_water.php" method="POST">
            <input type="hidden" name="amount_ml" value="<?php echo escape_html($button_2); ?>">
            <button class="quick-add-btn" type="submit">
                +<?php echo escape_html($button_2); ?> mL
            </button>
        </form>
    </div>

    <!-- 6. Custom water amount input below the bottle/buttons -->
    <form action="/sipsip/actions/add_water.php" method="POST" class="custom-amount-form">
        <input type="number" id="custom_amount_ml" name="amount_ml" placeholder="Custom amount (mL)" min="1" step="1" required>
        <button type="submit" class="btn">Add Water</button>
    </form>

    <!-- 7. Context-Aware Tips Area -->
    <?php if ($tip): ?>
        <div class="tips-area">
            <button type="button" class="dismiss-btn" onclick="this.parentElement.style.display='none';" aria-label="Dismiss">&times;</button>
            <strong>Tip:</strong> <?php echo escape_html($tip); ?>
        </div>
    <?php endif; ?>
    
</div>

<?php require_once 'includes/footer.php'; ?>
