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
    
    // Validate and sanitize input
    $meal_type = filter_var($_POST['type'], FILTER_SANITIZE_STRING);
    $food_item = filter_var($_POST['food'], FILTER_SANITIZE_STRING);
    $calories = filter_var($_POST['calories'], FILTER_VALIDATE_INT);
    $protein = filter_var($_POST['protein'], FILTER_VALIDATE_FLOAT);
    $carbs = filter_var($_POST['carbs'], FILTER_VALIDATE_FLOAT);
    $fat = filter_var($_POST['fat'], FILTER_VALIDATE_FLOAT);

    $stmt = $conn->prepare("
        INSERT INTO meal_logs (user_id, meal_type, food_item, calories, protein, carbs, fat)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([$user_id, $meal_type, $food_item, $calories, $protein, $carbs, $fat]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Meal logged successfully'
    ]);

} catch(PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
