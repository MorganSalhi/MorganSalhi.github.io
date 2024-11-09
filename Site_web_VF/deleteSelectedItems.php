<?php
// Start the session
session_start();

// Include the database connection file
require 'db_connection.php';

// Check if the user is logged in and has the appropriate access rights (administrator or super-administrator)
if (!isset($_SESSION['login']) || ($_SESSION['droit'] != 'administrateur' && $_SESSION['droit'] != 'super-administrateur')) {
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit;
}

// Get the input data from the request body
$data = json_decode(file_get_contents('php://input'), true);
$type = $data['type'];
$ids = $data['ids'];

// Check if type and ids are set and if ids is an array
if ($type && $ids && is_array($ids)) {
    // Determine the table and id field based on the type
    if ($type === 'program') {
        $table = 'Programmes';
        $idField = 'numProg';
    } elseif ($type === 'keyword') {
        $table = 'MotsCles';
        $idField = 'numMC';
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid type.']);
        exit;
    }

    // Create placeholders and types string for prepared statement
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    // Delete from the main table
    $stmt = $conn->prepare("DELETE FROM $table WHERE $idField IN ($placeholders)");
    $stmt->bind_param($types, ...$ids);

    if ($stmt->execute()) {
        // Delete associated records from bonnespratiques, association, and appartenance tables
        if ($type === 'program') {
            $stmt = $conn->prepare("DELETE FROM appartenance WHERE Programme IN ($placeholders)");
            $stmt->bind_param($types, ...$ids);
            $stmt->execute();
        } elseif ($type === 'keyword') {
            $stmt = $conn->prepare("DELETE FROM association WHERE numMC IN ($placeholders)");
            $stmt->bind_param($types, ...$ids);
            $stmt->execute();
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Deletion failed.']);
    }

    // Close the statement
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}

// Close the database connection
$conn->close();
?>
