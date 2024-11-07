<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

require_once 'db_config.php';

try {
    $weight = filter_var($_POST['weight'], FILTER_VALIDATE_FLOAT);
    $bodyFat = filter_var($_POST['bodyFat'], FILTER_VALIDATE_FLOAT);
    $measurements = json_encode([
        'chest' => filter_var($_POST['chest'], FILTER_VALIDATE_FLOAT),
        'waist' => filter_var($_POST['waist'], FILTER_VALIDATE_FLOAT),
        'hips' => filter_var($_POST['hips'], FILTER_VALIDATE_FLOAT),
        'biceps' => filter_var($_POST['biceps'], FILTER_VALIDATE_FLOAT),
        'thighs' => filter_var($_POST['thighs'], FILTER_VALIDATE_FLOAT),
        'calves' => filter_var($_POST['calves'], FILTER_VALIDATE_FLOAT)
    ]);

    $stmt = $conn->prepare("
        INSERT INTO progress_logs (user_id, weight, body_fat, measurements, log_date)
        VALUES (?, ?, ?, ?, CURRENT_DATE)
    ");
    
    $stmt->execute([$_SESSION['user_id'], $weight, $bodyFat, $measurements]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Progress updated successfully'
    ]);
} catch(Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
