<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

require_once 'db_config.php';

try {
    $settings = $_POST;
    
    $conn->beginTransaction();

    // Delete existing settings
    $stmt = $conn->prepare("DELETE FROM user_settings WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);

    // Insert new settings
    $stmt = $conn->prepare("
        INSERT INTO user_settings (user_id, setting_key, setting_value)
        VALUES (?, ?, ?)
    ");

    foreach ($settings as $key => $value) {
        $stmt->execute([$_SESSION['user_id'], $key, $value]);
    }

    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Settings updated successfully'
    ]);
} catch(Exception $e) {
    $conn->rollBack();
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
