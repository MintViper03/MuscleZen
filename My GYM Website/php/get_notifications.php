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

    // Get unread notifications
    $stmt = $conn->prepare("
        SELECT 
            n.*,
            u.username as actor_name,
            u.profile_image as actor_image
        FROM notifications n
        LEFT JOIN users u ON n.reference_id = u.id
        WHERE n.user_id = ? 
        ORDER BY n.created_at DESC
        LIMIT 20
    ");
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format notifications
    $formatted_notifications = array_map(function($notification) {
        $message = '';
        switch($notification['type']) {
            case 'follow':
                $message = "{$notification['actor_name']} started following you";
                break;
            case 'like':
                $message = "{$notification['actor_name']} liked your post";
                break;
            case 'comment':
                $message = "{$notification['actor_name']} commented on your post";
                break;
        }
        
        return [
            'id' => $notification['id'],
            'message' => $message,
            'actor_image' => $notification['actor_image'],
            'created_at' => $notification['created_at'],
            'read' => $notification['read_status']
        ];
    }, $notifications);

    echo json_encode([
        'status' => 'success',
        'data' => $formatted_notifications
    ]);

} catch(PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
