<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

require_once 'db_config.php';

try {
    $user_id = $_SESSION['user_id'];
    
    $conn->beginTransaction();

    // Delete user's data from all related tables
    $tables = [
        'meal_logs',
        'workout_schedules',
        'workout_plans',
        'progress_logs',
        'personal_records',
        'user_settings',
        'post_likes',
        'comments',
        'posts',
        'followers'
    ];

    foreach ($tables as $table) {
        $stmt = $conn->prepare("DELETE FROM $table WHERE user_id = ?");
        $stmt->execute([$user_id]);
    }

    // Finally delete the user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);

    $conn->commit();

    // Clear session
    session_destroy();

    echo json_encode([
        'status' => 'success',
        'message' => 'Account deleted successfully'
    ]);
} catch(Exception $e) {
    $conn->rollBack();
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to delete account: ' . $e->getMessage()
    ]);
}
?>
