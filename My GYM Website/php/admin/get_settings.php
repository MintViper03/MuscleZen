<?php
session_start();
header('Content-Type: application/json');

require_once 'admin_db_config.php';
require_once '../middleware/AdminAuth.php';

try {
    AdminAuth::requireAdmin();
    $db = AdminDatabase::getInstance();
    $conn = $db->getConnection();

    $stmt = $conn->query("SELECT setting_key, setting_value FROM user_settings");
    $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    echo json_encode([
        'status' => 'success',
        'data' => $settings
    ]);

} catch (Exception $e) {
    error_log("Error in get_settings: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to load settings'
    ]);
}
