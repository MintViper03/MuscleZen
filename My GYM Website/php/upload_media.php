<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

require_once 'db_config.php';

try {
    $user_id = $_SESSION['user_id'];
    $upload_dir = '../uploads/';
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/quicktime'];
    $max_file_size = 10 * 1024 * 1024; // 10MB

    // Create uploads directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (!empty($_FILES['file'])) {
        $file = $_FILES['file'];
        
        // Validate file type
        if (!in_array($file['type'], $allowed_types)) {
            throw new Exception('Invalid file type');
        }

        // Validate file size
        if ($file['size'] > $max_file_size) {
            throw new Exception('File too large (max 10MB)');
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $filepath = $upload_dir . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            echo json_encode([
                'status' => 'success',
                'data' => [
                    'filename' => $filename,
                    'url' => 'uploads/' . $filename,
                    'type' => $file['type']
                ]
            ]);
        } else {
            throw new Exception('Failed to upload file');
        }
    } else {
        throw new Exception('No file uploaded');
    }

} catch(Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
