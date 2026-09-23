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

$daily_goal = $settings['daily_goal_ml'];
$button_1 = $settings['button_1_ml'];
$button_2 = $settings['button_2_ml'];

// Phase 4: Current intake is hardcoded to 0. 
// Real intake calculation will be added in Phase 5.
$current_intake = 0; 

// Calculate bottle fill percentage (capped at 100%)
$fill_percentage = 0;
if ($daily_goal > 0) {
    $fill_percentage = ($current_intake / $daily_goal) * 100;
}
if ($fill_percentage > 100) {
    $fill_percentage = 100;
}
?>

<div class="dashboard-container">
    
    <!-- 5. Notification area near the top -->
    <div class="notifications-area">
        <strong>Notification:</strong> You're just starting your day. Keep hydrated!
    </div>

    <!-- 1. Progress text above the bottle -->
    <div class="progress-text">
        <?php echo escape_html($current_intake); ?> mL / <?php echo escape_html($daily_goal); ?> mL
    </div>

    <!-- Bottle and Quick-Add Buttons -->
    <div class="bottle-container">
        <!-- 3. Left quick-add button -->
        <button class="quick-add-btn" type="button">
            +<?php echo escape_html($button_1); ?> mL
        </button>
        
        <!-- 2. Dynamic water bottle -->
        <div class="bottle">
            <div class="bottle-fill" style="height: <?php echo escape_html($fill_percentage); ?>%;"></div>
        </div>

        <!-- 3. Right quick-add button -->
        <button class="quick-add-btn" type="button">
            +<?php echo escape_html($button_2); ?> mL
        </button>
    </div>

    <!-- 4. Custom water amount input below the bottle/buttons -->
    <div class="custom-amount-form">
        <input type="number" id="custom_amount_ml" name="custom_amount_ml" placeholder="Custom amount (mL)" min="1" step="1">
        <button type="button" class="btn">Add Water</button>
    </div>

    <!-- 6. Tips area below the custom amount input -->
    <div class="tips-area">
        <strong>Tip:</strong> Drinking water before meals can help you feel fuller!
    </div>
    
</div>

<?php require_once 'includes/footer.php'; ?>
