<?php
// initial_settings.php
require_once 'includes/header.php';
require_login(); // Protected page

// Fetch current settings for the authenticated user
$user_id = get_current_user_id();

try {
    $stmt = $pdo->prepare("SELECT daily_goal_ml, button_1_ml, button_2_ml, reset_time FROM user_settings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $settings = $stmt->fetch();
    
    if (!$settings) {
        // Fallback defaults if somehow missing
        $settings = [
            'daily_goal_ml' => 2000,
            'button_1_ml' => 250,
            'button_2_ml' => 500,
            'reset_time' => '00:00:00'
        ];
    }
} catch (PDOException $e) {
    die("Database error.");
}

$error = '';
if (isset($_SESSION['settings_error'])) {
    $error = $_SESSION['settings_error'];
    unset($_SESSION['settings_error']);
    
    // Restore valid form values
    if (isset($_SESSION['settings_form_data'])) {
        $form_data = $_SESSION['settings_form_data'];
        $settings['daily_goal_ml'] = $form_data['daily_goal_ml'] ?? $settings['daily_goal_ml'];
        $settings['button_1_ml'] = $form_data['button_1_ml'] ?? $settings['button_1_ml'];
        $settings['button_2_ml'] = $form_data['button_2_ml'] ?? $settings['button_2_ml'];
        $settings['reset_time'] = ($form_data['reset_time'] ?? '') . ':00';
        unset($_SESSION['settings_form_data']);
    }
}

// Convert DB time (HH:MM:SS) to HTML time input format (HH:MM)
$reset_time_formatted = substr($settings['reset_time'], 0, 5);
?>

<div class="auth-container">
    <h2>Welcome to SipSip! 💧</h2>
    <p style="text-align: center; margin-bottom: 1.5rem;">Let's customize your hydration goals.</p>
    
    <?php if ($error): ?>
        <div class="error-message"><?php echo escape_html($error); ?></div>
    <?php endif; ?>

    <form action="/sipsip/actions/update_initial_settings.php" method="POST">
        <?php echo csrf_field(); ?>
        <div class="form-group">
            <label for="daily_goal_ml">Daily Water Goal (mL)</label>
            <input type="number" id="daily_goal_ml" name="daily_goal_ml" value="<?php echo escape_html($settings['daily_goal_ml']); ?>" required min="1" step="1">
        </div>
        
        <div class="form-group">
            <label for="button_1_ml">Left Quick-Add Button (mL)</label>
            <input type="number" id="button_1_ml" name="button_1_ml" value="<?php echo escape_html($settings['button_1_ml']); ?>" required min="1" step="1">
        </div>

        <div class="form-group">
            <label for="button_2_ml">Right Quick-Add Button (mL)</label>
            <input type="number" id="button_2_ml" name="button_2_ml" value="<?php echo escape_html($settings['button_2_ml']); ?>" required min="1" step="1">
        </div>
        
        <div class="form-group">
            <label for="reset_time">Daily Reset Time</label>
            <input type="time" id="reset_time" name="reset_time" value="<?php echo escape_html($reset_time_formatted); ?>" required>
            <small style="display: block; margin-top: 0.3rem; color: #666;">When should your tracking day restart?</small>
        </div>
        
        <button type="submit" class="btn">Save and Start Tracking</button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
