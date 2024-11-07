<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

require_once 'db_config.php';

try {
    $privacy_settings = [
        'profile_visibility' => filter_var($_POST['profile_visibility'], FILTER_SANITIZE_STRING),
        'share_workouts' => isset($_POST['share_workouts']) ? 1 : 0,
        'share_progress' => isset($_POST['share_progress']) ? 1 : 0
    ];

    $stmt = $conn->prepare("
        INSERT INTO user_settings (user_id, setting_key, setting_value)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ");

    foreach ($privacy_settings as $key => $value) {
        $stmt->execute([$_SESSION['user_id'], $key, $value]);
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Privacy settings updated successfully'
    ]);
} catch(Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
