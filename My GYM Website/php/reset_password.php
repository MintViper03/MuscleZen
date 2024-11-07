<?php
require_once 'db_config.php';
require_once 'utils/security.php';
header('Content-Type: application/json');

try {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($token) || empty($password)) {
        throw new Exception('Invalid request');
    }

    // Validate password strength
    if (strlen($password) < 8) {
        throw new Exception('Password must be at least 8 characters long');
    }

    // Check if token exists and is valid
    $stmt = $conn->prepare("
        SELECT user_id, email 
        FROM password_resets 
        WHERE token = ? AND expires_at > NOW() 
        AND used = 0
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset) {
        throw new Exception('Invalid or expired reset link');
    }

    // Hash new password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Update password
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashedPassword, $reset['user_id']]);

    // Mark reset token as used
    $stmt = $conn->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
    $stmt->execute([$token]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Password has been reset successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
