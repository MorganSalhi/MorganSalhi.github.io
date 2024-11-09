<?php
// Include the database connection file
include 'db_connection.php';

// Check the database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to retrieve logs ordered by timestamp in descending order
$sql = "SELECT * FROM logs ORDER BY timestamp DESC";
$result = $conn->query($sql);

$data = [];

// Check if there are any logs retrieved
if ($result->num_rows > 0) {
    // Fetch each log entry and add it to the data array
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} else {
    // If no logs are found, add an error message to the data array
    $data['error'] = "No logs found.";
}

// Encode the data array as JSON and output it
echo json_encode($data);

// Close the database connection
$conn->close();
?>
