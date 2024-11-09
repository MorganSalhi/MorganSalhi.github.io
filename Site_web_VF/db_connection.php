<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection details
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "thales11";

// Create a new MySQLi connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    // Log the connection error
    include 'logger.php';
    session_start();
    logAction('db_connection.php', 'Connection failed: ' . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}
?>
