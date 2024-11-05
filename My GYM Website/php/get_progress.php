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
    
    // Get current stats
    $stmt = $conn->prepare("
        SELECT weight, body_fat, date 
        FROM progress 
        WHERE user_id = ? 
        ORDER BY date DESC 
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);

    // Calculate muscle mass (simplified)
    $muscleMass = $current['weight'] * (1 - ($current['body_fat'] / 100));

    // Get history for charts
    $stmt = $conn->prepare("
        SELECT weight, body_fat, date 
        FROM progress 
        WHERE user_id = ? 
        ORDER BY date ASC 
        LIMIT 30
    ");
    $stmt->execute([$user_id]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get measurements
    $stmt = $conn->prepare("
        SELECT * FROM measurements 
        WHERE user_id = ? 
        ORDER BY date DESC 
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $measurements = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get personal records
    $stmt = $conn->prepare("
        SELECT * FROM personal_records 
        WHERE user_id = ? 
        ORDER BY date DESC
    ");
    $stmt->execute([$user_id]);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Count workouts this month
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM workouts 
        WHERE user_id = ? 
        AND MONTH(date) = MONTH(CURRENT_DATE())
    ");
    $stmt->execute([$user_id]);
    $workouts = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

    echo json_encode([
        'status' => 'success',
        'data' => [
            'current' => [
                'weight' => $current['weight'] ?? 0,
                'bodyFat' => $current['body_fat'] ?? 0,
                'muscleMass' => round($muscleMass, 1) ?? 0,
                'date' => $current['date'] ?? '-'
            ],
            'history' => [
                'dates' => array_column($history, 'date'),
                'weights' => array_column($history, 'weight'),
                'bodyFat' => array_column
