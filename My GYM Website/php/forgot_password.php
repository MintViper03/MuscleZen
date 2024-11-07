<?php
require_once 'db_config.php';
require_once 'utils/security.php';
require_once 'utils/mailer.php';
require_once 'utils/logger.php';

header('Content-Type: application/json');

try {
    // Validate email
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Rate limiting
    if (isset($_SESSION['reset_attempts'])) {
        if ($_SESSION['reset_attempts'] >= 3) {
            throw new Exception('Too many attempts. Please try again later.');
        }
    } else {
        $_SESSION['reset_attempts'] = 0;
    }

    // Check if email exists
    $stmt = $conn->prepare("SELECT id, username FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['reset_attempts']++;
        throw new Exception('If this email exists in our system, you will receive reset instructions shortly.');
    }

    // Generate secure token
    $token = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Store reset token
    $stmt = $conn->prepare("
        INSERT INTO password_resets (user_id, email, token, expires_at) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$user['id'], $email, $token, $expiry]);

    // Generate reset link
    $resetLink = "https://" . $_SERVER['HTTP_HOST'] . "/reset_password.html?token=" . $token;

    // Send email
    Mailer::sendPasswordReset($email, $user['username'], $resetLink);

    // Log successful request
    Logger::info("Password reset requested for user ID: {$user['id']}");

    // Clear reset attempts on success
    unset($_SESSION['reset_attempts']);

    echo json_encode([
        'status' => 'success',
        'message' => 'If this email exists in our system, you will receive reset instructions shortly.'
    ]);

} catch (Exception $e) {
    Logger::error("Password reset error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
