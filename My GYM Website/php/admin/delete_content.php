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

    $contentId = filter_var($_POST['content_id'], FILTER_VALIDATE_INT);
    $contentType = filter_var($_POST['content_type'], FILTER_SANITIZE_STRING);

    if (!$contentId || !$contentType) {
        throw new Exception('Invalid content information');
    }

    switch ($contentType) {
        case 'post':
            deletePost($conn, $contentId);
            break;
        case 'video':
            deleteMedia($conn, $contentId, 'video');
            break;
        case 'image':
            deleteMedia($conn, $contentId, 'image');
            break;
        default:
            throw new Exception('Invalid content type');
    }

    // Log the deletion
    logContentDeletion($conn, $contentId, $contentType);

    echo json_encode([
        'status' => 'success',
        'message' => 'Content deleted successfully'
    ]);

} catch (Exception $e) {
    error_log("Error in delete_content: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

function deletePost($conn, $postId) {
    // First check if post exists and admin has rights
    $stmt = $conn->prepare("SELECT id FROM posts WHERE id = ?");
    $stmt->execute([$postId]);
    if (!$stmt->fetch()) {
        throw new Exception('Post not found');
    }

    // Delete associated comments
    $stmt = $conn->prepare("DELETE FROM comments WHERE post_id = ?");
    $stmt->execute([$postId]);

    // Delete associated likes
    $stmt = $conn->prepare("DELETE FROM post_likes WHERE post_id = ?");
    $stmt->execute([$postId]);

    // Delete post
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$postId]);
}

function deleteMedia($conn, $mediaId, $type) {
    // Get media info first
    $stmt = $conn->prepare("
        SELECT filename 
        FROM user_media 
        WHERE id = ? AND media_type = ?
    ");
    $stmt->execute([$mediaId, $type]);
    $media = $stmt->fetch();

    if (!$media) {
        throw new Exception('Media not found');
    }

    // Delete physical file
    $uploadDir = $type === 'video' ? '../../uploads/videos/' : '../../uploads/images/';
    $filepath = $uploadDir . $media['filename'];
    
    if (file_exists($filepath)) {
        unlink($filepath);
    }

    // Delete from database
    $stmt = $conn->prepare("DELETE FROM user_media WHERE id = ?");
    $stmt->execute([$mediaId]);
}

function logContentDeletion($conn, $contentId, $contentType) {
    $stmt = $conn->prepare("
        INSERT INTO admin_activity_log (admin_id, action, details, ip_address)
        VALUES (:admin_id, 'content_deleted', :details, :ip_address)
    ");

    $stmt->execute([
        ':admin_id' => $_SESSION['admin_id'],
        ':details' => json_encode([
            'content_type' => $contentType,
            'content_id' => $contentId
        ]),
        ':ip_address' => $_SERVER['REMOTE_ADDR']
    ]);
}
