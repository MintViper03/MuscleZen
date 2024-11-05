<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

require_once 'db_config.php';

try {
    // Get random users excluding the current user
    $stmt = $conn->prepare("
        SELECT id, username, email 
        FROM users 
        WHERE id != ? 
        ORDER BY RAND() 
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $users
    ]);
} catch(PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
