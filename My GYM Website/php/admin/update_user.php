<?php
require_once '../middleware/AdminAuth.php';
AdminAuth::requireAdmin();

header('Content-Type: application/json');
require_once '../db_config.php';

try {
    $userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);
    
    if (!$userId || !$username || !$email) {
        throw new Exception('Invalid input data');
    }
    
    // Check if email exists for other users
    $stmt = $conn->prepare("
        SELECT id FROM users 
        WHERE email = ? AND id != ?
    ");
    $stmt->execute([$email, $userId]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('Email already exists');
    }
    
    $stmt = $conn->prepare("
        UPDATE users 
        SET username = ?, email = ?, status = ?, role = ?
        WHERE id = ?
    ");
    
    $stmt->execute([$username, $email, $status, $role, $userId]);
    
    AdminAuth::logActivity(
        $_SESSION['admin_id'], 
        'update_user', 
        "Updated user ID: $userId"
    );
    
    echo json_encode([
        'status' => 'success',
        'message' => 'User updated successfully'
    ]);
} catch(Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
