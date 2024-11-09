<?php
// Include the database connection and logger files
include 'db_connection.php';
include 'logger.php';

// Initialize the response array
$response = [];

try {
    // Start session to track user
    session_start();
    
    // Retrieve data sent via POST
    $data = json_decode(file_get_contents('php://input'), true);
    $keywordName = $data['keywordName'];

    // Get the highest numMC value and increment by 1
    $result = $conn->query("SELECT MAX(numMC) AS maxNumMC FROM motscles");
    $row = $result->fetch_assoc();
    $newNumMC = $row['maxNumMC'] + 1;

    // Insert the new keyword
    $stmt = $conn->prepare("INSERT INTO motscles (numMC, nomMotsCles) VALUES (?, ?)");
    $stmt->bind_param("is", $newNumMC, $keywordName);

    if ($stmt->execute()) {
        // Log the successful insertion of the keyword
        logAction('createKeyword.php', 'Keyword added: ' . $keywordName);
        $response['success'] = true;
    } else {
        // Log the error if keyword insertion fails
        logAction('createKeyword.php', 'Error adding keyword: ' . $stmt->error);
        $response['error'] = 'Error adding keyword';
    }
} catch (Exception $e) {
    // Log the exception message
    logAction('createKeyword.php', 'Exception: ' . $e->getMessage());
    $response['error'] = 'Error: ' . $e->getMessage();
}

// Return the response as JSON
echo json_encode($response);

// Close the database connection
$conn->close();
?>
