<?php
session_start();
header('Content-Type: application/json');

require_once 'admin_db_config.php';
require_once '../middleware/AdminAuth.php';

try {
    AdminAuth::requireAdmin();
    $db = AdminDatabase::getInstance();
    $conn = $db->getConnection();

    // Get workouts by category
    $stmt = $conn->query("
        SELECT category, COUNT(*) as count 
        FROM workout_plans 
        GROUP BY category
    ");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get total active workouts
    $stmt = $conn->query("
        SELECT COUNT(*) as active 
        FROM workout_plans 
        WHERE status = 'active'
    ");
    $activeWorkouts = $stmt->fetch()['active'];

    // Get recent completions
    $stmt = $conn->query("
        SELECT COUNT(*) as completed 
        FROM workout_schedules 
        WHERE status = 'completed' 
        AND completed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $recentCompletions = $stmt->fetch()['completed'];

    echo json_encode([
        'status' => 'success',
        'data' => [
            'categories' => $categories,
            'active_workouts' => $activeWorkouts,
            'recent_completions' => $recentCompletions
        ]
    ]);

} catch (Exception $e) {
    error_log("Error in get_workout_stats: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to load workout statistics'
    ]);
}
