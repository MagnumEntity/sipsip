<?php
require_once 'includes/header.php';
require_login();

$user_id = get_current_user_id();

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

$stats = get_user_statistics($pdo, $user_id, $reset_time, $daily_goal);

$view = $_GET['view'] ?? 'week';
if (!in_array($view, ['day', 'week', 'month'])) {
    $view = 'week';
}
?>

<div class="dashboard-container">
    <h2>Statistics</h2>
    
    <div class="stats-tabs">
        <a href="?view=day" class="stats-tab <?php echo $view === 'day' ? 'active' : ''; ?>">Day</a>
        <a href="?view=week" class="stats-tab <?php echo $view === 'week' ? 'active' : ''; ?>">Week</a>
        <a href="?view=month" class="stats-tab <?php echo $view === 'month' ? 'active' : ''; ?>">Month</a>
    </div>

    <?php if ($view === 'day'): ?>
        <?php
        $day = $stats['day'];
        $remaining = $day['goal'] - $day['intake'];
        $exceeded = $day['intake'] - $day['goal'];
        ?>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($day['intake']); ?> mL</div>
                <div class="stat-label">Today's Intake</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($day['goal']); ?> mL</div>
                <div class="stat-label">Goal</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $day['progress']; ?>%</div>
                <div class="stat-label">Progress</div>
            </div>
            <div class="stat-card">
                <?php if ($remaining > 0): ?>
                    <div class="stat-value" style="color: #e74c3c;"><?php echo number_format($remaining); ?> mL</div>
                    <div class="stat-label">Remaining</div>
                <?php elseif ($exceeded > 0): ?>
                    <div class="stat-value" style="color: #2ecc71;"><?php echo number_format($exceeded); ?> mL</div>
                    <div class="stat-label">Exceeded by</div>
                <?php else: ?>
                    <div class="stat-value" style="color: #2ecc71;">0 mL</div>
                    <div class="stat-label">Goal Met</div>
                <?php endif; ?>
            </div>
        </div>
        
    <?php else: ?>
        <?php
        $period = $stats[$view];
        $graph_data = $period['graph_data'];
        
        $max_val = $daily_goal;
        foreach ($graph_data as $g) {
            if ($g['intake'] > $max_val) {
                $max_val = $g['intake'];
            }
        }
        // Avoid division by zero in graph layout
        if ($max_val <= 0) $max_val = 1;
        
        $goal_bottom_percent = ($daily_goal / $max_val) * 100;
        ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($period['total']); ?> mL</div>
                <div class="stat-label"><?php echo ucfirst($view); ?>ly Total</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($period['average'], 1); ?> mL</div>
                <div class="stat-label">Daily Average</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $period['days_reached']; ?> / <?php echo $period['total_days']; ?></div>
                <div class="stat-label">Goals Reached</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $period['achievement_rate']; ?>%</div>
                <div class="stat-label">Achievement Rate</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">🔥 <?php echo $stats['streak']; ?></div>
                <div class="stat-label">Current Streak</div>
            </div>
        </div>
        
        <div class="graph-container">
            <h3><?php echo ucfirst($view); ?>ly Intake Graph</h3>
            <p style="font-size: 0.95rem; color: #555; margin-bottom: 0.5rem;">
                <strong>Summary:</strong> Total: <?php echo number_format($period['total']); ?> mL &bull; Daily Average: <?php echo number_format($period['average'], 1); ?> mL &bull; Goals Reached: <?php echo $period['days_reached']; ?> of <?php echo $period['total_days']; ?> (<?php echo $period['achievement_rate']; ?>%)
            </p>
            <div class="graph-legend" style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; font-size: 0.85rem; color: #555;">
                <div style="display: flex; align-items: center; gap: 0.3rem;"><span style="display: inline-block; width: 12px; height: 12px; background: var(--primary-color); border-radius: 2px;"></span> Reached Goal</div>
                <div style="display: flex; align-items: center; gap: 0.3rem;"><span style="display: inline-block; width: 12px; height: 12px; background: #95a5a6; border-radius: 2px;"></span> Below Goal</div>
                <div style="display: flex; align-items: center; gap: 0.3rem;"><span style="display: inline-block; width: 18px; height: 0px; border-top: 2px dashed #f39c12;"></span> Daily Goal (<?php echo number_format($daily_goal); ?> mL)</div>
            </div>
            <div class="graph-wrapper">
                <div class="graph-goal-line" style="bottom: <?php echo $goal_bottom_percent; ?>%;"></div>
                
                <?php foreach ($graph_data as $i => $g): ?>
                    <?php 
                        $height_percent = ($g['intake'] / $max_val) * 100; 
                        $is_reached = $g['intake'] >= $g['goal'];
                        $date_obj = new DateTime($g['date']);
                        // For week, show day name (Mon). For month, show date (23).
                        $label = $view === 'week' ? $date_obj->format('D') : $date_obj->format('j');
                    ?>
                    <div class="graph-bar-container" title="<?php echo $g['date']; ?>: <?php echo $g['intake']; ?> mL">
                        <div class="graph-bar <?php echo $is_reached ? 'reached' : ''; ?>" style="height: <?php echo $height_percent; ?>%;"></div>
                        <div class="graph-label"><?php echo $label; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php require_once 'includes/footer.php'; ?>
