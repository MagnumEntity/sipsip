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

/**
 * Fetch history and calculate streak.
 * Returns an array: ['history' => [...], 'streak' => int]
 */
function get_user_history_and_streak($pdo, $user_id, $reset_time, $daily_goal) {
    // 1. Fetch grouped history
    $stmt = $pdo->prepare("
        SELECT 
            DATE(SUBTIME(recorded_at, ?)) AS tracking_date, 
            SUM(amount_ml) AS total_intake
        FROM water_intake_records
        WHERE user_id = ?
        GROUP BY tracking_date
        ORDER BY tracking_date DESC
    ");
    $stmt->execute([$reset_time, $user_id]);
    $rows = $stmt->fetchAll();

    $history = [];
    foreach ($rows as $row) {
        $row_date = $row['tracking_date'];
        $row_intake = (int) $row['total_intake'];
        $reached = $row_intake >= $daily_goal;
        
        $history[] = [
            'date' => $row_date,
            'intake' => $row_intake,
            'goal' => $daily_goal,
            'reached' => $reached
        ];
    }

    // 2. Calculate Streak
    $bounds = get_tracking_day_bounds($reset_time);
    $current_tracking_date = substr($bounds['start'], 0, 10);
    
    $streak = 0;
    $expected_date = $current_tracking_date;
    $history_map = array_column($history, null, 'date');

    // Check current day
    if (isset($history_map[$expected_date]) && $history_map[$expected_date]['reached']) {
        $streak++;
    }

    // Step backwards day by day
    $expected_date_obj = new DateTime($current_tracking_date, new DateTimeZone('Asia/Manila'));
    $expected_date_obj->modify('-1 day');
    $expected_date = $expected_date_obj->format('Y-m-d');

    while (true) {
        if (isset($history_map[$expected_date]) && $history_map[$expected_date]['reached']) {
            $streak++;
            $expected_date_obj->modify('-1 day');
            $expected_date = $expected_date_obj->format('Y-m-d');
        } else {
            // Missing day or goal not reached genuinely breaks the streak
            break;
        }
    }

    return [
        'history' => $history,
        'streak' => $streak
    ];
}

/**
 * Fetch and calculate user statistics for Day, Week, and Month views.
 */
function get_user_statistics($pdo, $user_id, $reset_time, $daily_goal) {
    $data = get_user_history_and_streak($pdo, $user_id, $reset_time, $daily_goal);
    $history_map = array_column($data['history'], null, 'date');
    
    $bounds = get_tracking_day_bounds($reset_time);
    $current_tracking_date = substr($bounds['start'], 0, 10);
    
    // Day Stats
    $day_intake = isset($history_map[$current_tracking_date]) ? $history_map[$current_tracking_date]['intake'] : 0;
    
    // Helper to calculate stats for N days
    $calc_period = function($num_days) use ($history_map, $current_tracking_date, $daily_goal) {
        $total_intake = 0;
        $days_reached = 0;
        $graph_data = [];
        
        $date_obj = new DateTime($current_tracking_date, new DateTimeZone('Asia/Manila'));
        
        $dates = [];
        for ($i = 0; $i < $num_days; $i++) {
            $dates[] = $date_obj->format('Y-m-d');
            $date_obj->modify('-1 day');
        }
        $dates = array_reverse($dates);
        
        foreach ($dates as $date) {
            $intake = isset($history_map[$date]) ? $history_map[$date]['intake'] : 0;
            $reached = $intake >= $daily_goal;
            
            $total_intake += $intake;
            if ($reached) {
                $days_reached++;
            }
            
            $graph_data[] = [
                'date' => $date,
                'intake' => $intake,
                'goal' => $daily_goal
            ];
        }
        
        $average = $total_intake / $num_days;
        $achievement_rate = ($days_reached / $num_days) * 100;
        
        return [
            'total' => $total_intake,
            'average' => round($average, 1),
            'days_reached' => $days_reached,
            'total_days' => $num_days,
            'achievement_rate' => round($achievement_rate, 1),
            'graph_data' => $graph_data
        ];
    };
    
    return [
        'day' => [
            'intake' => $day_intake,
            'goal' => $daily_goal,
            'progress' => calculate_progress_percentage($day_intake, $daily_goal)
        ],
        'week' => $calc_period(7),
        'month' => $calc_period(30),
        'streak' => $data['streak']
    ];
}
?>
