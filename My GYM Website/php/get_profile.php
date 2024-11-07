<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

try {
    require_once 'db_config.php';

    $stmt = $conn->prepare("
        SELECT username, email, dob, gender, profile_image, 
               (SELECT setting_value FROM user_settings WHERE user_id = users.id AND setting_key = 'primary_goal') as primary_goal,
               (SELECT setting_value FROM user_settings WHERE user_id = users.id AND setting_key = 'target_weight') as target_weight,
               (SELECT setting_value FROM user_settings WHERE user_id = users.id AND setting_key = 'weekly_workouts') as weekly_workouts
        FROM users 
        WHERE id = ?
    ");
    
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode([
            'status' => 'success',
            'data' => [
                'username' => $user['username'],
                'email' => $user['email'],
                'dob' => $user['dob'],
                'gender' => $user['gender'],
                'profile_image' => $user['profile_image'] ?: 'images/default-avatar.png',
                'primary_goal' => $user['primary_goal'],
                'target_weight' => $user['target_weight'],
                'weekly_workouts' => $user['weekly_workouts']
            ]
        ]);
    } else {
        throw new Exception('User not found');
    }

} catch(Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
