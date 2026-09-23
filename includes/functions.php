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

/**
 * Generate context-aware in-app notification based on current intake and goal.
 */
function get_dashboard_notification($current_intake, $daily_goal, $notifications_enabled) {
    if (!$notifications_enabled) {
        return null;
    }

    $remaining = $daily_goal - $current_intake;
    $exceeded = $current_intake - $daily_goal;

    if ($current_intake > $daily_goal) {
        return [
            'type' => 'over_goal',
            'message' => "You've exceeded your daily goal by {$exceeded} mL!",
            'exceeded_amount' => $exceeded,
            'class' => 'goal-exceeded'
        ];
    }

    if ($current_intake === $daily_goal && $daily_goal > 0) {
        return [
            'type' => 'goal_reached',
            'message' => "Goal reached! Great job staying on track today!",
            'class' => 'goal-reached'
        ];
    }

    if ($remaining === 100) {
        return [
            'type' => 'close_100',
            'message' => "You're almost there! 100 mL away!",
            'class' => 'goal-info'
        ];
    }

    if ($remaining === 500) {
        return [
            'type' => 'close_500',
            'message' => "You're 500 mL away from your goal!",
            'class' => 'goal-info'
        ];
    }

    // Far behind (e.g. remaining > 500)
    if ($remaining > 500) {
        return [
            'type' => 'far_behind',
            'message' => "Keep going! Stay hydrated and work toward your daily goal.",
            'class' => 'goal-info'
        ];
    }

    // Default encouragement for other remaining amounts
    return [
        'type' => 'in_progress',
        'message' => "Keep it up! {$remaining} mL remaining to reach today's goal.",
        'class' => 'goal-info'
    ];
}

/**
 * Generate context-aware hydration tip.
 */
function get_dashboard_tip($current_intake, $daily_goal, $tips_enabled) {
    if (!$tips_enabled) {
        return null;
    }

    $tips = [
        "Try taking small sips throughout the day.",
        "Keeping your water nearby can make regular hydration easier.",
        "A quick water break can help you stay consistent with your daily goal.",
        "You're making progress toward today's goal!",
        "Drinking a glass of water when you wake up is a great way to start your day."
    ];

    $index = ($current_intake / 250) % count($tips);
    return $tips[(int)$index];
}

/**
 * Generate or retrieve the existing CSRF token for the current session.
 */
function get_csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output a hidden HTML input field containing the CSRF token.
 */
function csrf_field() {
    $token = get_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . escape_html($token) . '">';
}

/**
 * Verify that the submitted CSRF token matches the session token.
 */
function verify_csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $token = $_POST['csrf_token'] ?? '';
    $session_token = $_SESSION['csrf_token'] ?? '';

    if (empty($token) || empty($session_token) || !hash_equals($session_token, $token)) {
        return false;
    }
    return true;
}
?>
