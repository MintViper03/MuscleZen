<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

try {
    require_once 'db_config.php';

    // Check if file was uploaded
    if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error occurred');
    }

    $file = $_FILES['profile_photo'];
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception('Invalid file type. Only JPG, PNG and GIF are allowed');
    }

    // Validate file size (max 5MB)
    $max_size = 5 * 1024 * 1024;
    if ($file['size'] > $max_size) {
        throw new Exception('File too large. Maximum size is 5MB');
    }

    // Create uploads directory if it doesn't exist
    $upload_dir = '../uploads/profile_photos/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . $_SESSION['user_id'] . '_' . uniqid() . '.' . $extension;
    $filepath = $upload_dir . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to move uploaded file');
    }

    // Update database with new photo path
    $photo_url = 'uploads/profile_photos/' . $filename;
    $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
    $stmt->execute([$photo_url, $_SESSION['user_id']]);

    // Delete old profile photo if exists
    $stmt = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $old_photo = $stmt->fetchColumn();

    if ($old_photo && $old_photo !== $photo_url && file_exists('../' . $old_photo)) {
        unlink('../' . $old_photo);
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Profile photo updated successfully',
        'data' => ['photo_url' => $photo_url]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
