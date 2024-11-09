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
    $programName = $data['programName'];
    $assignedBPs = $data['assignedBPs'];

    // Get the highest numProg value and increment by 1
    $result = $conn->query("SELECT MAX(numProg) AS maxNumProg FROM programmes");
    $row = $result->fetch_assoc();
    $newNumProg = $row['maxNumProg'] + 1;

    // Insert the new program
    $stmt = $conn->prepare("INSERT INTO programmes (numProg, nomProgramme) VALUES (?, ?)");
    $stmt->bind_param("is", $newNumProg, $programName);

    if ($stmt->execute()) {
        // Log the successful insertion of the program
        logAction('createProgram.php', 'Program added: ' . $programName);
        $response['programSuccess'] = true;

        // Add associations in the Appartenance table
        foreach ($assignedBPs as $bpId => $bpData) {
            if ($bpData['selected']) {
                $phase = $bpData['phase'];

                // Insert the association
                $stmt = $conn->prepare("INSERT INTO appartenance (BP, Programme, Phases) VALUES (?, ?, ?)");
                $stmt->bind_param("iii", $bpId, $newNumProg, $phase);
                if ($stmt->execute()) {
                    // Log the successful insertion of the association
                    logAction('createProgram.php', 'Association added: BP=' . $bpId . ', Program=' . $newNumProg . ', Phase=' . $phase);
                } else {
                    // Log the error if association insertion fails
                    logAction('createProgram.php', 'Error adding association: ' . $stmt->error);
                    $response['error'] = 'Error adding association: ' . $stmt->error;
                }
            }
        }

        $response['success'] = true;
    } else {
        // Log the error if program insertion fails
        logAction('createProgram.php', 'Error adding program: ' . $stmt->error);
        $response['error'] = 'Error adding program';
    }
} catch (Exception $e) {
    // Log the exception message
    logAction('createProgram.php', 'Exception: ' . $e->getMessage());
    $response['error'] = 'Error: ' . $e->getMessage();
}

// Return the response as JSON
echo json_encode($response);

// Close the database connection
$conn->close();
?>
