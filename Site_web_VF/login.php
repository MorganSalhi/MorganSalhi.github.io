<?php
// Start a session to manage user login state
session_start();
// Include database connection and logger files
require 'db_connection.php';
include 'logger.php';

// Log access to the page
logAction('login.php', 'Page accessed');

// Database connection details
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "root";
$dbname = "thales11";

// Create a new MySQLi connection
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to redirect with an error message
function redirectWithError($message) {
    header("Location: thalesaccueil.html?error=" . urlencode($message));
    exit();
}

// Check if the request method is POST (form submission)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve form data
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // Prepare an SQL statement to fetch user details
    $stmt = $conn->prepare("SELECT mdp, droit, bloque, tentative_login, statut FROM utilisateurs WHERE login = ?");
    // Bind parameters to the SQL statement
    $stmt->bind_param("s", $username);
    // Execute the SQL statement
    $stmt->execute();
    // Get the result of the query
    $result = $stmt->get_result();

    // Check if the user exists
    if ($row = $result->fetch_assoc()) {
        // Check if the account is blocked
        if ($row['bloque'] == '1') {
            logAction('login.php', 'Login attempt with blocked account: ' . $username);
            redirectWithError("This account is blocked.");
        } else {
            // Verify the password
            if (password_verify($password, $row['mdp'])) {
                // Set session variables for successful login
                $_SESSION['login'] = $username;
                $_SESSION['droit'] = $row['droit'];
                // Update the user status to active and reset login attempts
                $updateStmt = $conn->prepare("UPDATE utilisateurs SET tentative_login = 0, statut = 'actif' WHERE login = ?");
                $updateStmt->bind_param("s", $username);
                $updateStmt->execute();
                $updateStmt->close();
                logAction('login.php', 'Successful login for user: ' . $username);
                // Redirect to the index page
                header("Location: index.php");
                exit();
            } else {
                // Handle incorrect password
                if ($row['droit'] !== 'super-administrateur') {
                    // Increment login attempt count
                    $newTentative = (int)$row['tentative_login'] + 1;
                    // Block account after 3 failed attempts
                    $bloque = $newTentative >= 3 ? '1' : '0';
                    // Update login attempts and block status
                    $updateStmt = $conn->prepare("UPDATE utilisateurs SET tentative_login = ?, bloque = ? WHERE login = ?");
                    $updateStmt->bind_param("iss", $newTentative, $bloque, $username);
                    if (!$updateStmt->execute()) {
                        redirectWithError("Error updating login attempts: " . $updateStmt->error);
                    }
                    $updateStmt->close();
                }
                logAction('login.php', 'Incorrect password for user: ' . $username);
                redirectWithError("Incorrect password");
            }
        }
    } else {
        // Handle non-existent user
        logAction('login.php', 'User not found: ' . $username);
        redirectWithError("No user found with that username");
    }
    // Close the statement
    $stmt->close();
}
// Close the database connection
$conn->close();
?>
