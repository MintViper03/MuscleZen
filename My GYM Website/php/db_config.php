<?php
// Session configuration
ini_set('session.cookie_lifetime', 86400); // 24 hours
ini_set('session.gc_maxlifetime', 86400); // 24 hours
session_start();

// Database configuration
$host = "localhost";
$dbUsername = "root";
$dbPassword = "Mustafa786.";
$dbName = "gym_db";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbName", $dbUsername, $dbPassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    exit;
}
?>
