<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

require_once 'db_config.php';

try {
    $schedule_id = filter_var($_POST['schedule_id'], FILTER_VALIDATE_INT);
    
    // Verify schedule belongs to user
    $stmt = $conn->prepare("
        SELECT id FROM workout_schedules 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$schedule_id, $_SESSION['user_id']]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Schedule not found or access denied');
    }

    // Mark as completed
    $stmt = $conn->prepare("
        UPDATE workout_schedules 
        SET status = 'completed', 
            completed_at = CURRENT_TIMESTAMP 
        WHERE id = ?
    ");
    $stmt->execute([$schedule_id]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Workout marked as completed'
    ]);
} catch(Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
