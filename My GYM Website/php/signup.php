<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration
$host = "localhost";
$user = "root";
$password = "Mustafa786.";
$dbname = "gym_db";

try {
    // Create database connection
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Process form data
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validate required fields
        $required_fields = ['username', 'email', 'password', 'street-address', 'city', 'state', 'zip', 'country', 'dob', 'gender'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                throw new Exception("All fields are required. Missing: " . $field);
            }
        }

        // Collect and sanitize form data using htmlspecialchars instead of FILTER_SANITIZE_STRING
        $username = htmlspecialchars(trim($_POST['username']));
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $password = trim($_POST['password']);
        $streetAddress = htmlspecialchars(trim($_POST['street-address']));
        $city = htmlspecialchars(trim($_POST['city']));
        $state = htmlspecialchars(trim($_POST['state']));
        $zip = htmlspecialchars(trim($_POST['zip']));
        $country = htmlspecialchars(trim($_POST['country']));
        $dob = $_POST['dob'];
        $gender = htmlspecialchars(trim($_POST['gender']));
        $terms_accepted = isset($_POST['terms']) ? 1 : 0;

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            throw new Exception("Email already registered");
        }

        // Validate password length
        if (strlen($password) < 6) {
            throw new Exception("Password must be at least 6 characters long");
        }

        // Prepare the full address
        $address = $streetAddress . ", " . $city . ", " . $state . ", " . $zip . ", " . $country;

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user - changed 'name' to 'username' in SQL
        $sql = "INSERT INTO users (username, email, password, address, dob, gender, terms_accepted, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            $username,
            $email,
            $hashedPassword,
            $address,
            $dob,
            $gender,
            $terms_accepted
        ]);

        if ($result) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Registration successful! Redirecting to login...'
            ]);
        } else {
            throw new Exception("Failed to insert user data");
        }

    } else {
        throw new Exception("Invalid request method");
    }

} catch (Exception $e) {
    error_log("Signup Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
