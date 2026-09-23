<?php
require_once 'includes/header.php';
require_login();

$user_id = get_current_user_id();

// Fetch settings
try {
    $stmt = $pdo->prepare("SELECT daily_goal_ml, reset_time FROM user_settings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $settings = $stmt->fetch();
    
    if (!$settings) {
        $settings = ['daily_goal_ml' => 2000, 'reset_time' => '00:00:00'];
    }
} catch (PDOException $e) {
    die("Database error.");
}

$daily_goal = (int) $settings['daily_goal_ml'];
$reset_time = $settings['reset_time'];

$data = get_user_history_and_streak($pdo, $user_id, $reset_time, $daily_goal);
$history = $data['history'];
$streak = $data['streak'];

?>
<div class="dashboard-container">
    <h2>History</h2>
    
    <div class="streak-card" style="background-color: var(--white); padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); text-align: center; border-left: 4px solid #f39c12; width: 100%;">
        <h3 style="margin-top: 0; color: #f39c12;">Current Streak</h3>
        <div style="font-size: 2rem; font-weight: bold; color: var(--primary-dark);">
            🔥 <?php echo escape_html($streak); ?> days
        </div>
    </div>

    <?php if (empty($history)): ?>
        <p style="text-align: center; margin-top: 2rem;">No history yet.</p>
    <?php else: ?>
        <div class="history-table-container" style="width: 100%; overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; background-color: var(--white); border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <thead>
                    <tr style="background-color: var(--primary-color); color: var(--white); text-align: left;">
                        <th style="padding: 1rem;">Date</th>
                        <th style="padding: 1rem;">Intake</th>
                        <th style="padding: 1rem;">Goal</th>
                        <th style="padding: 1rem;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $index => $day): ?>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 1rem;">
                                <?php 
                                    $date_obj = new DateTime($day['date']);
                                    echo escape_html($date_obj->format('F j, Y'));
                                    if ($index === 0) {
                                        $bounds = get_tracking_day_bounds($reset_time);
                                        $current_tracking_date = substr($bounds['start'], 0, 10);
                                        if ($day['date'] === $current_tracking_date) {
                                            echo ' <span style="font-size: 0.8rem; color: #888;">(Today)</span>';
                                        }
                                    }
                                ?>
                            </td>
                            <td style="padding: 1rem; font-weight: bold;">
                                <?php echo escape_html($day['intake']); ?> mL
                            </td>
                            <td style="padding: 1rem; color: #666;">
                                <?php echo escape_html($day['goal']); ?> mL
                            </td>
                            <td style="padding: 1rem;">
                                <?php if ($day['reached']): ?>
                                    <span style="color: #155724; background-color: #d4edda; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.9rem;">✓ Goal Reached</span>
                                <?php else: ?>
                                    <span style="color: #721c24; background-color: #f8d7da; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.9rem;">Not Reached</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php require_once 'includes/footer.php'; ?>
