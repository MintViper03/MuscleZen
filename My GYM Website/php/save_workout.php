<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

require_once 'db_config.php';

try {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $category = filter_var($_POST['category'], FILTER_SANITIZE_STRING);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_STRING);

    $stmt = $conn->prepare("
        INSERT INTO workout_plans (user_id, name, category, description)
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([$_SESSION['user_id'], $name, $category, $description]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Workout saved successfully'
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
