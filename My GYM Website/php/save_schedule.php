<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

require_once 'db_config.php';

try {
    $workout_id = filter_var($_POST['workout_id'], FILTER_VALIDATE_INT);
    $date = $_POST['date'];
    $time = $_POST['time'];
    $duration = filter_var($_POST['duration'], FILTER_VALIDATE_INT);

    // Validate inputs
    if (!$workout_id || !strtotime($date) || !strtotime($time) || !$duration) {
        throw new Exception('Invalid input data');
    }

    $stmt = $conn->prepare("
        INSERT INTO workout_schedules (user_id, workout_id, scheduled_date, scheduled_time, duration)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([$_SESSION['user_id'], $workout_id, $date, $time, $duration]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Workout scheduled successfully'
    ]);
} catch(Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
