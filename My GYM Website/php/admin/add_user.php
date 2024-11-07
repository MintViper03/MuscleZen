<?php
require_once '../middleware/AdminAuth.php';
AdminAuth::requireAdmin();

header('Content-Type: application/json');
require_once '../db_config.php';

try {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $role = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING);
    
    if (!$username || !$email || !$password) {
        throw new Exception('All fields are required');
    }
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('Email already exists');
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("
        INSERT INTO users (username, email, password, role, status)
        VALUES (?, ?, ?, ?, 'active')
    ");
    
    $stmt->execute([$username, $email, $hashedPassword, $role]);
    
    AdminAuth::logActivity($_SESSION['admin_id'], 'add_user', "Added user: $username");
    
    echo json_encode([
        'status' => 'success',
        'message' => 'User added successfully'
    ]);
} catch(Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
