<?php
require 'db_connection.php'; // Include the database connection file

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the username from the POST data
    $username = $_POST['username'];

    // Prepare a SQL statement to count the number of users with the given username
    $stmt = $conn->prepare("SELECT COUNT(*) FROM utilisateurs WHERE login = ?");
    $stmt->bind_param("s", $username); // Bind the username parameter to the SQL statement
    $stmt->execute(); // Execute the SQL statement
    $stmt->bind_result($count); // Bind the result to the $count variable
    $stmt->fetch(); // Fetch the result
    $stmt->close(); // Close the statement

    // Return a JSON response indicating whether the username exists
    echo json_encode(['exists' => $count > 0]);
}
?>
