<?php
// Database configuration
$host = "localhost";
$dbUsername = "root";
$dbPassword = "Mustafa786.";
$dbName = "gym_db";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8mb4", $dbUsername, $dbPassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $conn;
} catch(PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    throw new Exception('Database connection failed');
}
?>
