<?php
session_start();
header('Content-Type: application/json');

require_once 'admin_db_config.php';
require_once '../middleware/AdminAuth.php';

try {
    AdminAuth::requireAdmin();
    $db = AdminDatabase::getInstance();
    $conn = $db->getConnection();

    $stmt = $conn->query("SELECT * FROM workout_plans ORDER BY created_at DESC");
    $workouts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $workouts
    ]);

} catch (Exception $e) {
    error_log("Error in get_workouts: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to load workouts'
    ]);
}
