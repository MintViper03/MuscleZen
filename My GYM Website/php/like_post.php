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
    $post_id = $_POST['post_id'];

    // Check if user already liked the post
    $stmt = $conn->prepare("
        SELECT id FROM post_likes 
        WHERE user_id = ? AND post_id = ?
    ");
    $stmt->execute([$user_id, $post_id]);
    
    if ($stmt->rowCount() > 0) {
        // Unlike the post
        $stmt = $conn->prepare("
            DELETE FROM post_likes 
            WHERE user_id = ? AND post_id = ?
        ");
        $stmt->execute([$user_id, $post_id]);
        $action = 'unliked';
    } else {
        // Like the post
        $stmt = $conn->prepare("
            INSERT INTO post_likes (user_id, post_id) 
            VALUES (?, ?)
        ");
        $stmt->execute([$user_id, $post_id]);
        $action = 'liked';
    }

    // Get updated like count
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM post_likes 
        WHERE post_id = ?
    ");
    $stmt->execute([$post_id]);
    $likes_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    echo json_encode([
        'status' => 'success',
        'data' => [
            'action' => $action,
            'likes_count' => $likes_count
        ]
    ]);

} catch(PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
