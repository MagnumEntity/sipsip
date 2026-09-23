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

// Fetch all individual entries grouped by tracking date
try {
    $stmt = $pdo->prepare("
        SELECT
            entry_id,
            amount_ml,
            recorded_at,
            DATE(SUBTIME(recorded_at, ?)) AS tracking_date
        FROM water_intake_records
        WHERE user_id = ?
        ORDER BY recorded_at DESC
    ");
    $stmt->execute([$reset_time, $user_id]);
    $all_entries = $stmt->fetchAll();
} catch (PDOException $e) {
    $all_entries = [];
}

// Group individual entries by tracking_date
$entries_by_date = [];
foreach ($all_entries as $entry) {
    $entries_by_date[$entry['tracking_date']][] = $entry;
}

// Retrieve flash messages
$history_error = $_SESSION['history_error'] ?? '';
$history_success = $_SESSION['history_success'] ?? '';
unset($_SESSION['history_error'], $_SESSION['history_success']);

// Determine current tracking date
$bounds = get_tracking_day_bounds($reset_time);
$current_tracking_date = substr($bounds['start'], 0, 10);
?>
<div class="dashboard-container">
    <h2>History</h2>

    <div class="streak-card" style="background-color: var(--white); padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); text-align: center; border-left: 4px solid #f39c12; width: 100%;">
        <h3 style="margin-top: 0; color: #f39c12;">Current Streak</h3>
        <div style="font-size: 2rem; font-weight: bold; color: var(--primary-dark);">
            🔥 <?php echo escape_html($streak); ?> days
        </div>
    </div>

    <?php if ($history_error): ?>
        <div class="error-message" style="width: 100%;"><?php echo escape_html($history_error); ?></div>
    <?php endif; ?>
    <?php if ($history_success): ?>
        <div class="success-message" style="width: 100%;"><?php echo escape_html($history_success); ?></div>
    <?php endif; ?>

    <?php if (empty($history)): ?>
        <p style="text-align: center; margin-top: 2rem;">No history yet.</p>
    <?php else: ?>
        <?php foreach ($history as $index => $day): ?>
            <?php
                $day_date = $day['date'];
                $date_obj = new DateTime($day_date);
                $is_today = ($day_date === $current_tracking_date);
                $day_entries = $entries_by_date[$day_date] ?? [];
            ?>
            <div class="history-day-block" style="width: 100%; background-color: var(--white); border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); overflow: hidden;">
                <!-- Day header -->
                <div style="background-color: <?php echo $day['reached'] ? '#d4edda' : '#f8d7da'; ?>; padding: 0.8rem 1rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                    <div>
                        <strong><?php echo escape_html($date_obj->format('F j, Y')); ?></strong>
                        <?php if ($is_today): ?>
                            <span style="font-size: 0.8rem; color: #555;"> (Today)</span>
                        <?php endif; ?>
                    </div>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <span style="font-size: 0.95rem; font-weight: bold;">
                            <?php echo escape_html($day['intake']); ?> / <?php echo escape_html($day['goal']); ?> mL
                        </span>
                        <?php if ($day['reached']): ?>
                            <span style="color: #155724; background-color: #c3e6cb; padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.85rem;">✓ Goal Reached</span>
                        <?php else: ?>
                            <span style="color: #721c24; background-color: #f5c6cb; padding: 0.2rem 0.6rem; border-radius: 4px; font-size: 0.85rem;">Not Reached</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Individual entries -->
                <?php if (!empty($day_entries)): ?>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background-color: #f4f6f7; font-size: 0.85rem; color: #555; text-align: left;">
                                <th style="padding: 0.5rem 1rem;">Time</th>
                                <th style="padding: 0.5rem 1rem;">Amount</th>
                                <th style="padding: 0.5rem 1rem; text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($day_entries as $entry): ?>
                                <tr style="border-top: 1px solid var(--border-color);" id="entry-row-<?php echo escape_html($entry['entry_id']); ?>">
                                    <td style="padding: 0.6rem 1rem; font-size: 0.9rem; color: #555;">
                                        <?php
                                            $rec_time = new DateTime($entry['recorded_at']);
                                            echo escape_html($rec_time->format('g:i A'));
                                        ?>
                                    </td>
                                    <td style="padding: 0.6rem 1rem;">
                                        <!-- Static display -->
                                        <span class="entry-display-<?php echo escape_html($entry['entry_id']); ?>">
                                            <?php echo escape_html($entry['amount_ml']); ?> mL
                                        </span>
                                        <!-- Edit inline form (hidden by default) -->
                                        <form action="/sipsip/actions/edit_water.php" method="POST"
                                              class="entry-edit-form-<?php echo escape_html($entry['entry_id']); ?>"
                                              style="display:none; margin:0;">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="entry_id" value="<?php echo escape_html($entry['entry_id']); ?>">
                                            <input type="number" name="amount_ml"
                                                   value="<?php echo escape_html($entry['amount_ml']); ?>"
                                                   min="1" step="1" required
                                                   style="width:80px; padding:0.3rem; font-size:0.9rem; border:1px solid var(--border-color); border-radius:4px;">
                                            mL
                                            <button type="submit" class="btn" style="display:inline; width:auto; padding:0.3rem 0.7rem; font-size:0.85rem; margin-left:0.3rem;">Save</button>
                                            <button type="button" class="btn"
                                                    onclick="cancelEdit(<?php echo (int)$entry['entry_id']; ?>)"
                                                    style="display:inline; width:auto; padding:0.3rem 0.7rem; font-size:0.85rem; background-color:#95a5a6; margin-left:0.2rem;">Cancel</button>
                                        </form>
                                    </td>
                                    <td style="padding: 0.6rem 1rem; text-align: right; white-space: nowrap;">
                                        <!-- Edit button -->
                                        <button type="button"
                                                onclick="startEdit(<?php echo (int)$entry['entry_id']; ?>)"
                                                class="btn edit-entry-btn-<?php echo escape_html($entry['entry_id']); ?>"
                                                style="display:inline; width:auto; padding:0.3rem 0.7rem; font-size:0.85rem; background-color:#f39c12; margin-right:0.3rem;">
                                            Edit
                                        </button>
                                        <!-- Delete form -->
                                        <form action="/sipsip/actions/delete_water.php" method="POST"
                                              style="display:inline; margin:0;"
                                              onsubmit="return confirm('Delete this water entry? This cannot be undone.');">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="entry_id" value="<?php echo escape_html($entry['entry_id']); ?>">
                                            <button type="submit" class="btn"
                                                    style="display:inline; width:auto; padding:0.3rem 0.7rem; font-size:0.85rem; background-color:var(--error-color);">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="padding: 0.8rem 1rem; color: #888; font-size: 0.9rem; margin: 0;">No individual entries recorded for this day.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function startEdit(entryId) {
    document.querySelector('.entry-display-' + entryId).style.display = 'none';
    document.querySelector('.entry-edit-form-' + entryId).style.display = 'inline';
    document.querySelector('.edit-entry-btn-' + entryId).style.display = 'none';
}
function cancelEdit(entryId) {
    document.querySelector('.entry-display-' + entryId).style.display = '';
    document.querySelector('.entry-edit-form-' + entryId).style.display = 'none';
    document.querySelector('.edit-entry-btn-' + entryId).style.display = 'inline';
}
</script>

<?php require_once 'includes/footer.php'; ?>
