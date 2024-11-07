<?php
session_start();
header('Content-Type: application/json');

require_once 'admin_db_config.php';
require_once '../middleware/AdminAuth.php';

try {
    AdminAuth::requireAdmin();
    $db = AdminDatabase::getInstance();
    $conn = $db->getConnection();

    // Get blog posts count
    $stmt = $conn->query("SELECT COUNT(*) as posts FROM posts");
    $posts = $stmt->fetch()['posts'];

    // Get videos count
    $stmt = $conn->query("SELECT COUNT(*) as videos FROM user_media WHERE media_type = 'video'");
    $videos = $stmt->fetch()['videos'];

    // Get images count
    $stmt = $conn->query("SELECT COUNT(*) as images FROM user_media WHERE media_type = 'image'");
    $images = $stmt->fetch()['images'];

    // Get reported content count
    $stmt = $conn->query("SELECT COUNT(*) as reported FROM post_reports WHERE status = 'pending'");
    $reported = $stmt->fetch()['reported'];

    echo json_encode([
        'status' => 'success',
        'data' => [
            'posts' => $posts,
            'videos' => $videos,
            'images' => $images,
            'reported' => $reported
        ]
    ]);

} catch (Exception $e) {
    error_log("Error in get_content_stats: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to load content statistics'
    ]);
}
