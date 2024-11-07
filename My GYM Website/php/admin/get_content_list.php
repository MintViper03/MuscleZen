<?php
session_start();
header('Content-Type: application/json');

require_once 'admin_db_config.php';
require_once '../middleware/AdminAuth.php';

try {
    AdminAuth::requireAdmin();
    $db = AdminDatabase::getInstance();
    $conn = $db->getConnection();

    $type = $_GET['type'] ?? 'posts';
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = 10;
    $offset = ($page - 1) * $limit;

    $content = [];
    $total = 0;

    switch ($type) {
        case 'posts':
            $stmt = $conn->prepare("
                SELECT p.*, u.username as author
                FROM posts p
                JOIN users u ON p.user_id = u.id
                ORDER BY p.created_at DESC
                LIMIT :offset, :limit
            ");
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $content = $stmt->fetchAll();

            $total = $conn->query("SELECT COUNT(*) FROM posts")->fetchColumn();
            break;

        case 'videos':
            $stmt = $conn->prepare("
                SELECT m.*, u.username as author
                FROM user_media m
                JOIN users u ON m.user_id = u.id
                WHERE m.media_type = 'video'
                ORDER BY m.created_at DESC
                LIMIT :offset, :limit
            ");
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $content = $stmt->fetchAll();

            $total = $conn->query("
                SELECT COUNT(*) FROM user_media WHERE media_type = 'video'
            ")->fetchColumn();
            break;

        case 'images':
            $stmt = $conn->prepare("
                SELECT m.*, u.username as author
                FROM user_media m
                JOIN users u ON m.user_id = u.id
                WHERE m.media_type = 'image'
                ORDER BY m.created_at DESC
                LIMIT :offset, :limit
            ");
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $content = $stmt->fetchAll();

            $total = $conn->query("
                SELECT COUNT(*) FROM user_media WHERE media_type = 'image'
            ")->fetchColumn();
            break;
    }

    echo json_encode([
        'status' => 'success',
        'data' => [
            'items' => $content,
            'total' => $total,
            'pages' => ceil($total / $limit),
            'current_page' => $page
        ]
    ]);

} catch (Exception $e) {
    error_log("Error in get_content_list: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to load content list'
    ]);
}
