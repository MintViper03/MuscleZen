<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

require_once 'db_config.php';

$content = trim($_POST['content']);

if (empty($content)) {
    echo json_encode(['status' => 'error', 'message' => 'Post content cannot be empty']);
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO posts (user_id, content, created_at) VALUES (?, ?, NOW())");
    if ($stmt->execute([$_SESSION['user_id'], $content])) {
        echo json_encode(['status' => 'success', 'message' => 'Post created successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to create post']);
    }
} catch(PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
