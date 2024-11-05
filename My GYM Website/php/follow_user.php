<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

require_once 'db_config.php';

try {
    $follower_id = $_SESSION['user_id'];
    $following_id = $_POST['user_id'];

    // Check if already following
    $stmt = $conn->prepare("
        SELECT id FROM followers 
        WHERE follower_id = ? AND following_id = ?
    ");
    $stmt->execute([$follower_id, $following_id]);
    
    if ($stmt->rowCount() > 0) {
        // Unfollow
        $stmt = $conn->prepare("
            DELETE FROM followers 
            WHERE follower_id = ? AND following_id = ?
        ");
        $stmt->execute([$follower_id, $following_id]);
        $action = 'unfollowed';
    } else {
        // Follow
        $stmt = $conn->prepare("
            INSERT INTO followers (follower_id, following_id) 
            VALUES (?, ?)
        ");
        $stmt->execute([$follower_id, $following_id]);
        $action = 'followed';

        // Create notification for the followed user
        $stmt = $conn->prepare("
            INSERT INTO notifications (user_id, type, reference_id, reference_type) 
            VALUES (?, 'follow', ?, 'user')
        ");
        $stmt->execute([$following_id, $follower_id]);
    }

    echo json_encode([
        'status' => 'success',
        'data' => ['action' => $action]
    ]);

} catch(PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
