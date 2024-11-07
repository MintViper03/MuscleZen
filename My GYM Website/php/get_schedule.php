<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

require_once 'db_config.php';

try {
    $stmt = $conn->prepare("
        SELECT ws.*, wp.name as workout_name, wp.category 
        FROM workout_schedules ws
        JOIN workout_plans wp ON ws.workout_id = wp.id
        WHERE ws.user_id = ? AND ws.scheduled_date >= CURRENT_DATE
        ORDER BY ws.scheduled_date, ws.scheduled_time
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $schedules
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
