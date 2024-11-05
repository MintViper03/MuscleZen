<?php
session_start();
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection parameters
$host = "localhost";
$dbUsername = "root";
$dbPassword = "Mustafa786.";
$dbName = "gym_db";

// Get POST data
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

// Basic validation
if(empty($email) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Email and password are required']);
    exit;
}

try {
    // Create database connection
    $conn = new PDO("mysql:host=$host;dbname=$dbName", $dbUsername, $dbPassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            
            // Return success response
            echo json_encode([
                'status' => 'success',
                'message' => 'Login successful',
                'user' => [
                    'username' => $user['username'],
                    'email' => $user['email']
                ]
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid password']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'User not found']);
    }

} catch(PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn = null;
?>
