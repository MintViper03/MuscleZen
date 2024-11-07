<?php
session_start();
header('Content-Type: application/json');

require_once 'admin_db_config.php';
require_once '../middleware/AdminAuth.php';
require_once '../utils/validation.php';

try {
    AdminAuth::requireAdmin();
    $db = AdminDatabase::getInstance();
    $conn = $db->getConnection();

    // Get and validate input
    $workoutData = [
        'name' => filter_var($_POST['workout_name'], FILTER_SANITIZE_STRING),
        'category' => filter_var($_POST['category'], FILTER_SANITIZE_STRING),
        'difficulty' => filter_var($_POST['difficulty'], FILTER_SANITIZE_STRING),
        'description' => filter_var($_POST['description'], FILTER_SANITIZE_STRING),
        'duration' => filter_var($_POST['duration'], FILTER_VALIDATE_INT),
        'calories' => filter_var($_POST['calories'], FILTER_VALIDATE_INT)
    ];

    // Validate required fields
    if (empty($workoutData['name']) || empty($workoutData['category']) || 
        empty($workoutData['difficulty']) || empty($workoutData['duration'])) {
        throw new Exception('Required fields are missing');
    }

    // Check if updating existing workout
    $workoutId = filter_var($_POST['workout_id'] ?? null, FILTER_VALIDATE_INT);

    if ($workoutId) {
        updateWorkout($conn, $workoutId, $workoutData);
    } else {
        createWorkout($conn, $workoutData);
    }

    echo json_encode([
        'status' => 'success',
        'message' => $workoutId ? 'Workout updated successfully' : 'Workout created successfully'
    ]);

} catch (Exception $e) {
    error_log("Error in save_workout: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

function createWorkout($conn, $data) {
    $stmt = $conn->prepare("
        INSERT INTO workout_plans (
            user_id,
            name,
            category,
            description,
            difficulty,
            duration,
            calories_burn,
            created_at
        ) VALUES (
            :user_id,
            :name,
            :category,
            :description,
            :difficulty,
            :duration,
            :calories,
            CURRENT_TIMESTAMP
        )
    ");

    $stmt->execute([
        ':user_id' => $_SESSION['admin_id'],
        ':name' => $data['name'],
        ':category' => $data['category'],
        ':description' => $data['description'],
        ':difficulty' => $data['difficulty'],
        ':duration' => $data['duration'],
        ':calories' => $data['calories']
    ]);

    // Log activity
    logWorkoutActivity($conn, $conn->lastInsertId(), 'created');
}

function updateWorkout($conn, $workoutId, $data) {
    // Verify workout exists
    $stmt = $conn->prepare("SELECT id FROM workout_plans WHERE id = ?");
    $stmt->execute([$workoutId]);
    if (!$stmt->fetch()) {
        throw new Exception('Workout not found');
    }

    $stmt = $conn->prepare("
        UPDATE workout_plans SET
            name = :name,
            category = :category,
            description = :description,
            difficulty = :difficulty,
            duration = :duration,
            calories_burn = :calories
        WHERE id = :id
    ");

    $stmt->execute([
        ':id' => $workoutId,
        ':name' => $data['name'],
        ':category' => $data['category'],
        ':description' => $data['description'],
        ':difficulty' => $data['difficulty'],
        ':duration' => $data['duration'],
        ':calories' => $data['calories']
    ]);

    // Log activity
    logWorkoutActivity($conn, $workoutId, 'updated');
}

function logWorkoutActivity($conn, $workoutId, $action) {
    $stmt = $conn->prepare("
        INSERT INTO admin_activity_log (admin_id, action, details, ip_address)
        VALUES (:admin_id, :action, :details, :ip_address)
    ");

    $stmt->execute([
        ':admin_id' => $_SESSION['admin_id'],
        ':action' => 'workout_' . $action,
        ':details' => json_encode(['workout_id' => $workoutId]),
        ':ip_address' => $_SERVER['REMOTE_ADDR']
    ]);
}
