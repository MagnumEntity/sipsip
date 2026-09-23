<?php
// includes/functions.php

/**
 * Basic helper functions for SipSip.
 * Further functions will be implemented in subsequent phases.
 */

/**
 * Sanitize output for HTML display to prevent XSS.
 */
function escape_html($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Get the start and next_start datetime for the current tracking day.
 * Returns an array ['start' => 'Y-m-d H:i:s', 'next_start' => 'Y-m-d H:i:s']
 */
function get_tracking_day_bounds($reset_time_str) {
    $timezone = new DateTimeZone('Asia/Manila');
    // Current time in Manila timezone
    $now = new DateTime('now', $timezone);
    
    // Create a DateTime object for today's reset time in Manila timezone
    $reset_today = new DateTime($now->format('Y-m-d') . ' ' . $reset_time_str, $timezone);
    
    if ($now < $reset_today) {
        // If current time is before today's reset time, we are still in yesterday's tracking day
        $start = clone $reset_today;
        $start->modify('-1 day');
        $next_start = clone $reset_today;
    } else {
        // We are in today's tracking day
        $start = clone $reset_today;
        $next_start = clone $reset_today;
        $next_start->modify('+1 day');
    }
    
    return [
        'start' => $start->format('Y-m-d H:i:s'),
        'next_start' => $next_start->format('Y-m-d H:i:s')
    ];
}

/**
 * Get the total intake for the user for the current tracking day.
 */
function get_current_tracking_day_intake($pdo, $user_id, $reset_time_str) {
    $bounds = get_tracking_day_bounds($reset_time_str);
    
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount_ml), 0) AS total 
        FROM water_intake_records 
        WHERE user_id = ? 
        AND recorded_at >= ? 
        AND recorded_at < ?
    ");
    $stmt->execute([$user_id, $bounds['start'], $bounds['next_start']]);
    $row = $stmt->fetch();
    return (int) $row['total'];
}

/**
 * Calculate the progress percentage safely, avoiding division by zero.
 */
function calculate_progress_percentage($intake, $goal) {
    if ($goal <= 0) {
        return 0;
    }
    return round(($intake / $goal) * 100, 1);
}
?>
