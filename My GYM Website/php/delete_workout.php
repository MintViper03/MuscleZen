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
    
    // Verify workout belongs to user
    $stmt = $conn->prepare("SELECT id FROM workout_plans WHERE id = ? AND user_id = ?");
    $stmt->execute([$workout_id, $_SESSION['user_id']]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Workout not found or access denied');
    }

    // Delete associated schedules first
    $stmt = $conn->prepare("DELETE FROM workout_schedules WHERE workout_id = ?");
    $stmt->execute([$workout_id]);

    // Delete the workout
    $stmt = $conn->prepare("DELETE FROM workout_plans WHERE id = ?");
    $stmt->execute([$workout_id]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Workout deleted successfully'
    ]);
} catch(Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
