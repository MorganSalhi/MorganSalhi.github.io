<?php
// logger.php

/**
 * Logs an action to the logs table.
 *
 * @param string $page   The page where the action occurred.
 * @param string $action The action that was performed.
 */
function logAction($page, $action) {
    // Include the database connection
    include 'db_connection.php';

    // Start the session if it is not already started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Get the login of the active user
    $activeUser = isset($_SESSION['login']) ? $_SESSION['login'] : 'Unknown';

    // Prepare the SQL statement to insert the log
    $stmt = $conn->prepare("INSERT INTO logs (page, action, utilisateurs) VALUES (?, ?, ?)");
    
    // Check if the statement was prepared successfully
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind the parameters to the SQL statement
    if (!$stmt->bind_param("sss", $page, $action, $activeUser)) {
        die("Error binding parameters: " . $stmt->error);
    }

    // Execute the SQL statement
    if (!$stmt->execute()) {
        die("Error executing statement: " . $stmt->error);
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>
