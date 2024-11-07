<?php
session_start();
header('Content-Type: application/json');

require_once 'admin_db_config.php';
require_once '../middleware/AdminAuth.php';

try {
    AdminAuth::requireAdmin();
    $db = AdminDatabase::getInstance();
    $conn = $db->getConnection();

    $period = $_GET['period'] ?? 'week';
    $type = $_GET['type'] ?? 'all';
    
    $data = [
        'user_analytics' => getUserAnalytics($conn, $period),
        'workout_analytics' => getWorkoutAnalytics($conn, $period),
        'engagement_metrics' => getEngagementMetrics($conn, $period),
        'performance_metrics' => getPerformanceMetrics($conn)
    ];

    if ($type !== 'all') {
        $data = $data[$type] ?? [];
    }

    echo json_encode([
        'status' => 'success',
        'data' => $data
    ]);

} catch (Exception $e) {
    error_log("Error in analytics: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

function getUserAnalytics($conn, $period) {
    $interval = getPeriodInterval($period);
    
    // New user registrations
    $stmt = $conn->prepare("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as count
        FROM users
        WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL ? DAY)
        GROUP BY DATE(created_at)
        ORDER BY date
    ");
    $stmt->execute([$interval]);
    $registrations = $stmt->fetchAll();

    // User demographics
    $stmt = $conn->query("
        SELECT 
            CASE 
                WHEN age < 20 THEN 'Under 20'
                WHEN age BETWEEN 20 AND 30 THEN '20-30'
                WHEN age BETWEEN 31 AND 40 THEN '31-40'
                ELSE 'Over 40'
            END as age_group,
            COUNT(*) as count
        FROM (
            SELECT TIMESTAMPDIFF(YEAR, dob, CURRENT_DATE) as age
            FROM users
            WHERE dob IS NOT NULL
        ) age_calc
        GROUP BY age_group
    ");
    $demographics = $stmt->fetchAll();

    // Active users
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT user_id) as active_users
        FROM workout_schedules
        WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL ? DAY)
    ");
    $stmt->execute([$interval]);
    $activeUsers = $stmt->fetch()['active_users'];

    return [
        'registrations' => $registrations,
        'demographics' => $demographics,
        'active_users' => $activeUsers
    ];
}

function getWorkoutAnalytics($conn, $period) {
    $interval = getPeriodInterval($period);
    
    // Popular workouts
    $stmt = $conn->prepare("
        SELECT 
            wp.name,
            wp.category,
            COUNT(ws.id) as completion_count
        FROM workout_plans wp
        LEFT JOIN workout_schedules ws ON wp.id = ws.workout_id
        WHERE ws.completed_at >= DATE_SUB(CURRENT_DATE, INTERVAL ? DAY)
        GROUP BY wp.id
        ORDER BY completion_count DESC
        LIMIT 10
    ");
    $stmt->execute([$interval]);
    $popularWorkouts = $stmt->fetchAll();

    // Workout completion rates
    $stmt = $conn->prepare("
        SELECT 
            DATE(completed_at) as date,
            COUNT(*) as completed_count
        FROM workout_schedules
        WHERE completed_at >= DATE_SUB(CURRENT_DATE, INTERVAL ? DAY)
        GROUP BY DATE(completed_at)
        ORDER BY date
    ");
    $stmt->execute([$interval]);
    $completionRates = $stmt->fetchAll();

    return [
        'popular_workouts' => $popularWorkouts,
        'completion_rates' => $completionRates
    ];
}

function getEngagementMetrics($conn, $period) {
    $interval = getPeriodInterval($period);
    
    // Social engagement
    $stmt = $conn->prepare("
        SELECT 
            'posts' as type,
            COUNT(*) as count
        FROM posts
        WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL ? DAY)
        UNION ALL
        SELECT 
            'comments' as type,
            COUNT(*) as count
        FROM comments
        WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL ? DAY)
        UNION ALL
        SELECT 
            'likes' as type,
            COUNT(*) as count
        FROM post_likes
        WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL ? DAY)
    ");
    $stmt->execute([$interval, $interval, $interval]);
    $socialEngagement = $stmt->fetchAll();

    // User retention
    $stmt = $conn->prepare("
        SELECT 
            COUNT(DISTINCT user_id) as returning_users
        FROM workout_schedules
        WHERE user_id IN (
            SELECT user_id 
            FROM workout_schedules 
            WHERE created_at < DATE_SUB(CURRENT_DATE, INTERVAL ? DAY)
        )
        AND created_at >= DATE_SUB(CURRENT_DATE, INTERVAL ? DAY)
    ");
    $stmt->execute([$interval, $interval]);
    $retention = $stmt->fetch();

    return [
        'social_engagement' => $socialEngagement,
        'user_retention' => $retention
    ];
}

function getPerformanceMetrics($conn) {
    // System performance
    $metrics = [
        'response_time' => getAverageResponseTime(),
        'error_rate' => getErrorRate(),
        'server_load' => getServerLoad()
    ];

    // Database performance
    $stmt = $conn->query("SHOW GLOBAL STATUS");
    $dbMetrics = [];
    while ($row = $stmt->fetch()) {
        if (in_array($row['Variable_name'], ['Questions', 'Slow_queries', 'Threads_connected'])) {
            $dbMetrics[$row['Variable_name']] = $row['Value'];
        }
    }

    return [
        'system' => $metrics,
        'database' => $dbMetrics
    ];
}

function getPeriodInterval($period) {
    switch ($period) {
        case 'day':
            return 1;
        case 'week':
            return 7;
        case 'month':
            return 30;
        case 'year':
            return 365;
        default:
            return 7;
    }
}

function getAverageResponseTime() {
    $logFile = '../../logs/performance.log';
    if (!file_exists($logFile)) return 0;

    $times = array_map('floatval', file($logFile));
    return count($times) ? array_sum($times) / count($times) : 0;
}

function getErrorRate() {
    $errorLog = '../../logs/error.log';
    if (!file_exists($errorLog)) return 0;

    $errors = count(file($errorLog));
    $requests = getRequestCount();
    return $requests ? ($errors / $requests) * 100 : 0;
}

function getServerLoad() {
    if (function_exists('sys_getloadavg')) {
        $load = sys_getloadavg();
        return $load[0];
    }
    return 0;
}

function getRequestCount() {
    $accessLog = '../../logs/access.log';
    if (!file_exists($accessLog)) return 0;

    return count(file($accessLog));
}
