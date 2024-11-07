<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authorized']);
    exit;
}

require_once '../db_config.php';

try {
    $stats = [];
    
    // Get total users
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get active users (last 24 hours)
    $stmt = $conn->query("
        SELECT COUNT(*) as count 
        FROM users 
        WHERE last_login >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ");
    $stats['active_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get total workouts
    $stmt = $conn->query("SELECT COUNT(*) as count FROM workout_plans");
    $stats['total_workouts'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get reported content
    $stmt = $conn->query("
        SELECT COUNT(*) as count 
        FROM post_reports 
        WHERE status = 'pending'
    ");
    $stats['reported_content'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Get recent registrations
    $stmt = $conn->query("
        SELECT COUNT(*) as count 
        FROM users 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $stats['new_users_week'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    echo json_encode([
        'status' => 'success',
        'data' => $stats
    ]);
} catch(Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error fetching dashboard stats'
    ]);
}
?>
