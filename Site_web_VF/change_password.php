<?php
session_start();
require 'db_connection.php';
include 'logger.php';

// Log access to the page
logAction('change_password.php', 'Password change requested');

// Check access rights
if (!isset($_SESSION['login'])) {
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    logAction('change_password.php', 'Access denied');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username']) && isset($_POST['newPassword'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $newPassword = $_POST['newPassword'];

    // Fetch password requirements from the database
    $reqResult = $conn->query("SELECT * FROM exigencesmdp WHERE id = 1");
    $requirements = $reqResult->fetch_assoc();

    // Password validation
    if (!preg_match('/[0-9]{' . $requirements['nombre'] . ',}/', $newPassword) ||
        !preg_match('/[a-z]{' . $requirements['Minuscule'] . ',}/', $newPassword) ||
        !preg_match('/[A-Z]{' . $requirements['Majuscule'] . ',}/', $newPassword) ||
        !preg_match('/[!"#$%&\'()*+,-.\/:;<=>?@[\\]^_`{|}~]{' . $requirements['Caracteres_spe'] . ',}/', $newPassword) ||
        ($requirements['username'] == 'non' && strpos($newPassword, $username) !== false)) {
        echo json_encode(['success' => false, 'message' => 'The password does not meet the security requirements.']);
        logAction('change_password.php', 'Password validation failed for user: ' . $username);
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT); // Hash the password

        // Update the password
        $updateSql = "UPDATE utilisateurs SET mdp = ?, tentative_login = 0, bloque = 0 WHERE login = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ss", $hashedPassword, $username);

        if ($updateStmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Password successfully changed.']);
            logAction('change_password.php', 'Password successfully changed for user: ' . $username);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error updating the password: ' . $updateStmt->error]);
            logAction('change_password.php', 'Error updating password for user: ' . $username . ', Error: ' . $updateStmt->error);
        }

        $updateStmt->close();
    }
}

$conn->close();
?>
