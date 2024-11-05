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
    $date = $_POST['date'];
    $weight = floatval($_POST['weight']);
    $bodyFat = !empty($_POST['bodyFat']) ? floatval($_POST['bodyFat']) : null;
    
    // Start transaction
    $conn->beginTransaction();

    // Insert progress data
    $stmt = $conn->prepare("
        INSERT INTO progress (user_id, date, weight, body_fat) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$user_id, $date, $weight, $bodyFat]);

    // Insert measurements if provided
    if (!empty($_POST['measurements'])) {
        $measurements = $_POST['measurements'];
        $stmt = $conn->prepare("
            INSERT INTO measurements (
                user_id, 
                date, 
                chest, 
                waist, 
                hips, 
                biceps, 
                thighs, 
                calves
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user_id,
            $date,
            floatval($measurements['chest']),
            floatval($measurements['waist']),
            floatval($measurements['hips']),
            floatval($measurements['biceps']),
            floatval($measurements['thighs']),
            floatval($measurements['calves'])
        ]);
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Progress saved successfully'
    ]);

} catch(PDOException $e) {
    // Rollback transaction on error
    $conn->rollBack();
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
