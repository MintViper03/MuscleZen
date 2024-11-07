<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

require_once 'db_config.php';

try {
    $settings = [
        'workout_reminders' => isset($_POST['workout_reminders']) ? 1 : 0,
        'progress_updates' => isset($_POST['progress_updates']) ? 1 : 0,
        'community_notifications' => isset($_POST['community_notifications']) ? 1 : 0
    ];

    $stmt = $conn->prepare("
        INSERT INTO user_settings (user_id, setting_key, setting_value)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ");

    foreach ($settings as $key => $value) {
        $stmt->execute([$_SESSION['user_id'], $key, $value]);
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Notification settings updated successfully'
    ]);
} catch(Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
