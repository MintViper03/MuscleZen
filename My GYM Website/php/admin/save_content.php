<?php
session_start();
header('Content-Type: application/json');

require_once 'admin_db_config.php';
require_once '../middleware/AdminAuth.php';
require_once '../utils/validation.php';

try {
    AdminAuth::requireAdmin();
    $db = AdminDatabase::getInstance();
    $conn = $db->getConnection();

    $contentType = $_POST['content_type'] ?? '';
    $title = filter_var($_POST['title'], FILTER_SANITIZE_STRING);
    $category = filter_var($_POST['category'], FILTER_SANITIZE_STRING);
    $content = $_POST['content'] ?? '';

    // Validate inputs
    if (empty($title) || empty($category)) {
        throw new Exception('Title and category are required');
    }

    switch ($contentType) {
        case 'blog':
            saveBlogPost($conn, $title, $category, $content);
            break;
        case 'video':
            saveVideo($conn, $title, $category, $_FILES['media'] ?? null);
            break;
        case 'image':
            saveImage($conn, $title, $category, $_FILES['media'] ?? null);
            break;
        default:
            throw new Exception('Invalid content type');
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Content saved successfully'
    ]);

} catch (Exception $e) {
    error_log("Error in save_content: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

function saveBlogPost($conn, $title, $category, $content) {
    $stmt = $conn->prepare("
        INSERT INTO posts (user_id, title, category, content, created_at)
        VALUES (:user_id, :title, :category, :content, CURRENT_TIMESTAMP)
    ");

    $stmt->execute([
        ':user_id' => $_SESSION['admin_id'],
        ':title' => $title,
        ':category' => $category,
        ':content' => $content
    ]);
}

function saveVideo($conn, $title, $category, $file) {
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Video file is required');
    }

    $allowedTypes = ['video/mp4', 'video/quicktime'];
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid video format');
    }

    $uploadPath = '../../uploads/videos/';
    $filename = uniqid() . '_' . basename($file['name']);
    
    if (!move_uploaded_file($file['tmp_name'], $uploadPath . $filename)) {
        throw new Exception('Failed to upload video');
    }

    $stmt = $conn->prepare("
        INSERT INTO user_media (user_id, media_type, filename, original_filename, file_size, mime_type)
        VALUES (:user_id, 'video', :filename, :original_filename, :file_size, :mime_type)
    ");

    $stmt->execute([
        ':user_id' => $_SESSION['admin_id'],
        ':filename' => $filename,
        ':original_filename' => $file['name'],
        ':file_size' => $file['size'],
        ':mime_type' => $file['type']
    ]);
}

function saveImage($conn, $title, $category, $file) {
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Image file is required');
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid image format');
    }

    $uploadPath = '../../uploads/images/';
    $filename = uniqid() . '_' . basename($file['name']);
    
    if (!move_uploaded_file($file['tmp_name'], $uploadPath . $filename)) {
        throw new Exception('Failed to upload image');
    }

    $stmt = $conn->prepare("
        INSERT INTO user_media (user_id, media_type, filename, original_filename, file_size, mime_type)
        VALUES (:user_id, 'image', :filename, :original_filename, :file_size, :mime_type)
    ");

    $stmt->execute([
        ':user_id' => $_SESSION['admin_id'],
        ':filename' => $filename,
        ':original_filename' => $file['name'],
        ':file_size' => $file['size'],
        ':mime_type' => $file['type']
    ]);
}
