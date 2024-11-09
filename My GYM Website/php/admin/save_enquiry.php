<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require_once 'admin_db_config.php';

try {
    $db = AdminDatabase::getInstance();
    $conn = $db->getConnection();

    // Create enquiries table if it doesn't exist
    $conn->exec("
        CREATE TABLE IF NOT EXISTS enquiries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            message TEXT,
            status ENUM('new', 'read', 'contacted') DEFAULT 'new',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Log received data for debugging
    error_log("Received POST data: " . print_r($_POST, true));

    // Validate and sanitize input
    $name = trim(strip_tags($_POST['cf-name'] ?? ''));
    $email = filter_var(trim($_POST['cf-email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $phone = trim(strip_tags($_POST['cf-phone'] ?? ''));
    $message = trim(strip_tags($_POST['cf-message'] ?? ''));

    // Validation
    if (empty($name)) {
        throw new Exception('Name is required');
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Valid email is required');
    }

    // Begin transaction
    $conn->beginTransaction();

    // Insert enquiry
    $stmt = $conn->prepare("
        INSERT INTO enquiries (name, email, phone, message, status)
        VALUES (:name, :email, :phone, :message, 'new')
    ");

    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'message' => $message
    ]);

    $enquiryId = $conn->lastInsertId();

    // Log activity
    $stmt = $conn->prepare("
        INSERT INTO admin_activity_log (
            admin_id, 
            action, 
            details, 
            ip_address
        ) VALUES (
            1,
            'new_enquiry',
            :details,
            :ip_address
        )
    ");

    $details = "New enquiry received from {$name} ({$email})";
    $stmt->execute([
        'details' => $details,
        'ip_address' => $_SERVER['REMOTE_ADDR']
    ]);

    // Commit transaction
    $conn->commit();

    // Log success for debugging
    error_log("Successfully saved enquiry with ID: " . $enquiryId);

    echo json_encode([
        'status' => 'success',
        'message' => 'Enquiry submitted successfully',
        'enquiry_id' => $enquiryId
    ]);

} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Error in save_enquiry: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
