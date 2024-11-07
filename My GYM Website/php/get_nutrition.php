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
    $today = date('Y-m-d');

    // Get daily summary
    $stmt = $conn->prepare("
        SELECT 
            SUM(calories) as total_calories,
            SUM(protein) as total_protein,
            SUM(carbs) as total_carbs,
            SUM(fat) as total_fat
        FROM meal_logs
        WHERE user_id = ? AND DATE(created_at) = ?
    ");
    $stmt->execute([$user_id, $today]);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get today's meals
    $stmt = $conn->prepare("
        SELECT * FROM meal_logs
        WHERE user_id = ? AND DATE(created_at) = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user_id, $today]);
    $meals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => [
            'summary' => [
                'calories' => $summary['total_calories'] ?? 0,
                'protein' => $summary['total_protein'] ?? 0,
                'carbs' => $summary['total_carbs'] ?? 0,
                'fat' => $summary['total_fat'] ?? 0
            ],
            'meals' => $meals
        ]
    ]);

} catch(PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
