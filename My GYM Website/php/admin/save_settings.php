<?php
session_start();
header('Content-Type: application/json');

require_once 'admin_db_config.php';
require_once '../middleware/AdminAuth.php';

try {
    AdminAuth::requireAdmin();
    $db = AdminDatabase::getInstance();
    $conn = $db->getConnection();

    $type = $_POST['type'] ?? '';
    
    switch ($type) {
        case 'general':
            saveGeneralSettings($conn, $_POST);
            break;
        case 'security':
            saveSecuritySettings($conn, $_POST);
            break;
        default:
            throw new Exception('Invalid settings type');
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Settings saved successfully'
    ]);

} catch (Exception $e) {
    error_log("Error in save_settings: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to save settings'
    ]);
}

function saveGeneralSettings($conn, $data) {
    $stmt = $conn->prepare("
        INSERT INTO user_settings (setting_key, setting_value) 
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ");

    $settings = [
        'site_name' => $data['site_name'] ?? '',
        'site_description' => $data['site_description'] ?? '',
        'timezone' => $data['timezone'] ?? 'UTC',
        'maintenance_mode' => isset($data['maintenance_mode']) ? '1' : '0'
    ];

    foreach ($settings as $key => $value) {
        $stmt->execute([$key, $value]);
    }
}

function saveSecuritySettings($conn, $data) {
    $stmt = $conn->prepare("
        INSERT INTO user_settings (setting_key, setting_value) 
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ");

    $settings = [
        'session_timeout' => $data['session_timeout'] ?? '30',
        'max_login_attempts' => $data['max_login_attempts'] ?? '5',
        'password_requirements' => json_encode([
            'uppercase' => isset($data['require_uppercase']),
            'numbers' => isset($data['require_numbers']),
            'special' => isset($data['require_special'])
        ])
    ];

    foreach ($settings as $key => $value) {
        $stmt->execute([$key, $value]);
    }
}
