<?php
require_once 'includes/header.php';
require_login();

$user_id = get_current_user_id();

// Fetch current user details
try {
    $stmt = $pdo->prepare("SELECT username FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        die("User account not found.");
    }

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
    die("Database error occurred.");
}

// Convert reset_time from HH:MM:SS to HH:MM for HTML time input
$reset_time_input = substr($settings['reset_time'], 0, 5);

// Retrieve flash messages
$username_error = $_SESSION['username_error'] ?? '';
$username_success = $_SESSION['username_success'] ?? '';
unset($_SESSION['username_error'], $_SESSION['username_success']);

$password_error = $_SESSION['password_error'] ?? '';
$password_success = $_SESSION['password_success'] ?? '';
unset($_SESSION['password_error'], $_SESSION['password_success']);

$settings_error = $_SESSION['settings_error'] ?? '';
$settings_success = $_SESSION['settings_success'] ?? '';
unset($_SESSION['settings_error'], $_SESSION['settings_success']);

$account_error = $_SESSION['account_error'] ?? '';
unset($_SESSION['account_error']);
?>

<div class="dashboard-container" style="max-width: 600px;">
    <h2>Settings / Profile</h2>

    <!-- Account Section -->
    <div class="settings-section">
        <h3>Account</h3>

        <!-- Change Username -->
        <h4>Change Username</h4>
        <?php if ($username_error): ?>
            <div class="error-message"><?php echo escape_html($username_error); ?></div>
        <?php endif; ?>
        <?php if ($username_success): ?>
            <div class="success-message"><?php echo escape_html($username_success); ?></div>
        <?php endif; ?>

        <form action="/sipsip/actions/change_username.php" method="POST" style="margin-bottom: 2rem;">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo escape_html($user['username']); ?>" required minlength="3" maxlength="50" autocomplete="username">
            </div>
            <button type="submit" class="btn">Update Username</button>
        </form>

        <!-- Change Password -->
        <h4>Change Password</h4>
        <?php if ($password_error): ?>
            <div class="error-message"><?php echo escape_html($password_error); ?></div>
        <?php endif; ?>
        <?php if ($password_success): ?>
            <div class="success-message"><?php echo escape_html($password_success); ?></div>
        <?php endif; ?>

        <form action="/sipsip/actions/change_password.php" method="POST">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
            </div>
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required minlength="6" autocomplete="new-password">
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6" autocomplete="new-password">
            </div>
            <button type="submit" class="btn">Change Password</button>
        </form>
    </div>

    <!-- Water Settings & Preferences Section -->
    <div class="settings-section">
        <h3>Water Settings &amp; Preferences</h3>

        <?php if ($settings_error): ?>
            <div class="error-message"><?php echo escape_html($settings_error); ?></div>
        <?php endif; ?>
        <?php if ($settings_success): ?>
            <div class="success-message"><?php echo escape_html($settings_success); ?></div>
        <?php endif; ?>

        <form action="/sipsip/actions/update_settings.php" method="POST">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label for="daily_goal_ml">Daily Water Goal (mL)</label>
                <input type="number" id="daily_goal_ml" name="daily_goal_ml" value="<?php echo escape_html($settings['daily_goal_ml']); ?>" required min="1" step="1">
            </div>

            <div class="form-group">
                <label for="button_1_ml">Quick-Add Button 1 Amount (mL)</label>
                <input type="number" id="button_1_ml" name="button_1_ml" value="<?php echo escape_html($settings['button_1_ml']); ?>" required min="1" step="1">
            </div>

            <div class="form-group">
                <label for="button_2_ml">Quick-Add Button 2 Amount (mL)</label>
                <input type="number" id="button_2_ml" name="button_2_ml" value="<?php echo escape_html($settings['button_2_ml']); ?>" required min="1" step="1">
            </div>

            <div class="form-group">
                <label for="reset_time">Daily Reset Time</label>
                <input type="time" id="reset_time" name="reset_time" value="<?php echo escape_html($reset_time_input); ?>" required>
            </div>

            <h4 style="margin-top: 1.5rem; margin-bottom: 0.8rem; color: var(--primary-dark);">Preferences</h4>

            <div class="form-group-checkbox">
                <input type="checkbox" id="notifications_enabled" name="notifications_enabled" value="1" <?php echo $settings['notifications_enabled'] ? 'checked' : ''; ?>>
                <label for="notifications_enabled">Enable Notifications</label>
            </div>

            <div class="form-group-checkbox">
                <input type="checkbox" id="tips_enabled" name="tips_enabled" value="1" <?php echo $settings['tips_enabled'] ? 'checked' : ''; ?>>
                <label for="tips_enabled">Enable Hydration Tips</label>
            </div>

            <button type="submit" class="btn" style="margin-top: 1.5rem;">Save Settings</button>
        </form>
    </div>

    <!-- Account Management (Danger Zone) -->
    <div class="settings-section settings-danger">
        <h3>Account Management</h3>
        <p style="color: #666; margin-bottom: 1.2rem;">
            This will permanently delete your account and all associated water records. This action cannot be undone.
        </p>

        <?php if ($account_error): ?>
            <div class="error-message"><?php echo escape_html($account_error); ?></div>
        <?php endif; ?>

        <form action="/sipsip/actions/delete_account.php" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete your account? This action cannot be undone.');">
            <?php echo csrf_field(); ?>
            <div class="form-group-checkbox" style="margin-bottom: 1.2rem;">
                <input type="checkbox" id="confirm_delete" name="confirm_delete" value="1" required>
                <label for="confirm_delete" style="color: var(--error-color); font-weight: bold;">I understand that this action is permanent and cannot be undone.</label>
            </div>
            <button type="submit" class="btn btn-danger">Delete My Account</button>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
