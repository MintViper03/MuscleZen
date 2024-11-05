<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

require_once 'db_config.php';

try {
    $stmt = $conn->prepare("
        SELECT p.*, u.username, u.email 
        FROM posts p 
        JOIN users u ON p.user_id = u.id 
        ORDER BY p.created_at DESC 
        LIMIT 20
    ");
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $posts
    ]);
} catch(PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
