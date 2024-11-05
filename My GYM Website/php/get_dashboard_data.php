<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Not logged in'
    ]);
    exit;
}

require_once 'db_config.php';

try {
    // Get user data
    $stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Sample activity data (you can replace this with actual database queries)
    $activities = [
        [
            'title' => 'Completed Workout',
            'description' => 'Upper Body Strength Training',
            'date' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ],
        [
            'title' => 'Updated Goals',
            'description' => 'Set new weight target',
            'date' => date('Y-m-d H:i:s', strtotime('-2 days'))
        ],
        [
            'title' => 'Nutrition Log',
            'description' => 'Logged daily meals',
            'date' => date('Y-m-d H:i:s', strtotime('-3 days'))
        ]
    ];

    echo json_encode([
        'status' => 'success',
        'data' => [
            'user' => $user,
            'activities' => $activities
        ]
    ]);

} catch(PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
