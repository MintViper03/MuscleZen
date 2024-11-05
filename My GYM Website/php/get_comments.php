<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

require_once 'db_config.php';

try {
    $post_id = $_GET['post_id'];

    $stmt = $conn->prepare("
        SELECT 
            c.*,
            u.username,
            u.profile_image as user_image
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.post_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$post_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $comments
    ]);

} catch(PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
