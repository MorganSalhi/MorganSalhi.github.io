<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include the database connection file
include 'db_connection.php';

try {
    // Prepare the SQL query to retrieve best practices
    $query = "SELECT numBP, texte FROM bonnespratiques";
    $result = $conn->query($query);

    // Check if there are any results
    if ($result->num_rows > 0) {
        $bps = [];
        // Fetch each row and add it to the bps array
        while ($row = $result->fetch_assoc()) {
            $bps[] = $row;
        }
        // Encode the bps array as JSON and output it
        echo json_encode($bps);
    } else {
        // If no rows were returned, output an empty JSON array
        echo json_encode([]);
    }
} catch (Exception $e) {
    // Output a JSON-encoded error message
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    // Close the database connection
    $conn->close();
}
?>
