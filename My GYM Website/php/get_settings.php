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
        SELECT setting_key, setting_value 
        FROM user_settings 
        WHERE user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Get user account info
    $stmt = $conn->prepare("
        SELECT created_at as member_since 
        FROM users 
        WHERE id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => [
            'settings' => $settings,
            'member_since' => $user['member_since'],
            'account_type' => 'Standard' // You can modify this based on your subscription system
        ]
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
