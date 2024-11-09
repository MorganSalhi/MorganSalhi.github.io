<?php
session_start();
require 'db_connection.php';
include 'logger.php';

// Log access to the page
logAction('delete_account.php', 'Account deletion requested');

// Check access rights
if (!isset($_SESSION['login']) || $_SESSION['droit'] != 'super-administrateur') {
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    logAction('delete_account.php', 'Access denied');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'])) {
    $username = $conn->real_escape_string($_POST['username']);

    // Delete the selected user
    $deleteSql = "DELETE FROM utilisateurs WHERE login = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bind_param("s", $username);

    if ($deleteStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Account successfully deleted.']);
        logAction('delete_account.php', 'Account deleted: ' . $username);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting the account.']);
        logAction('delete_account.php', 'Error deleting account: ' . $username);
    }

    $deleteStmt->close();
}

$conn->close();
?>
