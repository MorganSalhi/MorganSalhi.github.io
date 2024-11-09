<?php
session_start();
require 'db_connection.php';
include 'logger.php';

// Log access to the page
logAction('modify_role.php', 'Role modification requested');

// Check access rights
if (!isset($_SESSION['login']) || $_SESSION['droit'] != 'super-administrateur') {
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    logAction('modify_role.php', 'Access denied');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username']) && isset($_POST['newRole'])) {
    $username = $conn->real_escape_string($_POST['username']);
    $newRole = $_POST['newRole'];

    // Validate the new role
    if (!in_array($newRole, ['utilisateur', 'administrateur'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid role.']);
        logAction('modify_role.php', 'Invalid role for user: ' . $username);
    } else {
        // Check if the user exists and fetch their current role
        $sql = "SELECT droit FROM utilisateurs WHERE login = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            echo json_encode(['success' => false, 'message' => 'No user found with that username.']);
            logAction('modify_role.php', 'No user found for username: ' . $username);
        } else {
            $row = $result->fetch_assoc();
            // Ensure not to modify a super-administrator
            if ($row['droit'] == 'super-administrateur') {
                echo json_encode(['success' => false, 'message' => 'You do not have permission to modify a super-administrator.']);
                logAction('modify_role.php', 'Modification refused for super-administrator: ' . $username);
            } else {
                // Update the user's role
                $updateSql = "UPDATE utilisateurs SET droit = ? WHERE login = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("ss", $newRole, $username);
                if ($updateStmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'User role successfully updated.']);
                    logAction('modify_role.php', 'Role updated for user: ' . $username . ' to ' . $newRole);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error updating role: ' . $updateStmt->error]);
                    logAction('modify_role.php', 'Error updating role for user: ' . $username . ', Error: ' . $updateStmt->error);
                }
                $updateStmt->close();
            }
        }
        $stmt->close();
    }
}

$conn->close();
?>
