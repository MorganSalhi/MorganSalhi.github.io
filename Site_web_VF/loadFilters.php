<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include the database connection file
include 'db_connection.php';

// Initialize response array
$response = [
    'phases' => [],
    'programs' => [],
    'keywords' => [],
    'bps' => [] // Add best practices to the response
];

try {
    // Query to select distinct phases
    $phasesResult = $conn->query("SELECT DISTINCT numPhases, nomPhase FROM phases");
    if (!$phasesResult) {
        throw new Exception("Error fetching phases: " . $conn->error);
    }

    // Query to select distinct programs
    $programsResult = $conn->query("SELECT DISTINCT numProg, nomProgramme FROM programmes");
    if (!$programsResult) {
        throw new Exception("Error fetching programs: " . $conn->error);
    }

    // Query to select distinct keywords
    $keywordsResult = $conn->query("SELECT DISTINCT numMC, nomMotsCles FROM motscles");
    if (!$keywordsResult) {
        throw new Exception("Error fetching keywords: " . $conn->error);
    }

    // Query to select best practices
    $bpsResult = $conn->query("SELECT numBP, texte FROM bonnespratiques");
    if (!$bpsResult) {
        throw new Exception("Error fetching best practices: " . $conn->error);
    }

    // Fetch phases and add to response
    while ($phase = $phasesResult->fetch_assoc()) {
        $response['phases'][] = $phase;
    }

    // Fetch programs and add to response
    while ($program = $programsResult->fetch_assoc()) {
        $response['programs'][] = $program;
    }

    // Fetch keywords and add to response
    while ($keyword = $keywordsResult->fetch_assoc()) {
        $response['keywords'][] = $keyword;
    }

    // Fetch best practices and add to response
    while ($bp = $bpsResult->fetch_assoc()) {
        $response['bps'][] = $bp;
    }

    // Encode and return the response as JSON
    echo json_encode($response);
} catch (Exception $e) {
    // Handle any exceptions by returning an error message
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    // Close the database connection
    $conn->close();
}
?>
