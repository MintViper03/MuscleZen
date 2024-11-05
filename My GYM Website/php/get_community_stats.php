<?php
session_start();
header('Content-Type: application/json');

require_once 'db_config.php';

try {
    // Get total members
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $members = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Get posts made today
    $stmt = $conn->query("
        SELECT COUNT(*) as count 
        FROM posts 
        WHERE DATE(created_at) = CURRENT_DATE
    ");
    $posts_today = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Get active users (users who posted/commented in last 24 hours)
    $stmt = $conn->query("
        SELECT COUNT(DISTINCT user_id) as count 
        FROM (
            SELECT user_id FROM posts 
            WHERE created_at >= NOW() - INTERVAL 24 HOUR
            UNION
            SELECT user_id FROM comments 
            WHERE created_at >= NOW() - INTERVAL 24 HOUR
        ) as active_users
    ");
    $active_now = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    echo json_encode([
        'status' => 'success',
        'data' => [
            'members' => $members,
            'posts_today' => $posts_today,
            'active_now' => $active_now
        ]
    ]);

} catch(PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
