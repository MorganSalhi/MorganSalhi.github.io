<?php
// Start a session to manage user login state
session_start();
// Include database connection and logger files
require 'db_connection.php';
include 'logger.php';

// Check if the user is logged in
if (isset($_SESSION['login'])) {
    // Log the logout action
    logAction('logout.php', 'User logged out: ' . $_SESSION['login']);

    // Update the user's status to inactive in the database
    $conn = new mysqli("localhost", "root", "root", "thales11");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "UPDATE utilisateurs SET statut = 'inactif' WHERE login = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $_SESSION['login']);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    // Destroy the session
    session_destroy();
}

// Log the redirection after logout
logAction('logout.php', 'Redirection after logout');
header("Location: thalesaccueil.html"); // Redirect to the login page
exit;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Déconnexion</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .message {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        button {
            padding: 10px 20px;
            border: none;
            background: #007BFF;
            color: #fff;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
        }
    </style>
    <script>
        // Function to log action and redirect to the index page
        function logAndRedirect() {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "logger_ajax.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    window.location.href = 'index.php';
                }
            };
            xhr.send("page=logout.php&action=Back button clicked");
        }
    </script>
</head>
<body>
    <div class="message">
        <p>Vous avez été déconnecté avec succès.</p>
        <button onclick="logAndRedirect()">Retour à l'accueil</button>
    </div>
</body>
</html>
