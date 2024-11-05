<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

require_once 'db_config.php';

$fullname = filter_var($_POST['fullname'], FILTER_SANITIZE_STRING);
$dob = $_POST['dob'];
$gender = filter_var($_POST['gender'], FILTER_SANITIZE_STRING);

try {
    $stmt = $conn->prepare("UPDATE users SET username = ?, dob = ?, gender = ? WHERE id = ?");
    if ($stmt->execute([$fullname, $dob, $gender, $_SESSION['user_id']])) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Profile updated successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update profile'
        ]);
    }
} catch(PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
