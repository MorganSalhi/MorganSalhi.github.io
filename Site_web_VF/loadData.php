<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include the database connection file
include 'db_connection.php';

try {
    // Retrieve filter criteria from POST request
    $phase = $_POST['phase'] ?? '';
    $program = $_POST['program'] ?? '';
    $keyword = $_POST['keyword'] ?? '';

    // Start the session to retrieve exclusion filters
    session_start();
    $excludePrograms = $_SESSION['excludePrograms'] ?? [];
    $excludePhases = $_SESSION['excludePhases'] ?? [];
    $excludeKeywords = $_SESSION['excludeKeywords'] ?? [];

    // Base SQL query to retrieve best practices
    $query = "
        SELECT bp.numBP, bp.texte, 
               GROUP_CONCAT(DISTINCT ph.nomPhase ORDER BY ph.nomPhase ASC SEPARATOR ', ') AS phases, 
               GROUP_CONCAT(DISTINCT pr.nomProgramme ORDER BY pr.nomProgramme ASC SEPARATOR ', ') AS programmes,
               GROUP_CONCAT(DISTINCT mc.nomMotsCles ORDER BY mc.nomMotsCles ASC SEPARATOR ', ') AS keywords,
               GROUP_CONCAT(DISTINCT a.numAppart ORDER BY a.numAppart ASC SEPARATOR ', ') AS appartenance_ids
        FROM bonnespratiques bp
        LEFT JOIN appartenance a ON bp.numBP = a.BP
        LEFT JOIN phases ph ON a.Phases = ph.numPhases
        LEFT JOIN programmes pr ON a.Programme = pr.numProg
        LEFT JOIN association assoc ON bp.numBP = assoc.BP
        LEFT JOIN motscles mc ON assoc.numMC = mc.numMC
        WHERE active = 1";

    $params = [];
    $types = '';

    // Add filter for phase if specified
    if (!empty($phase)) {
        $query .= " AND a.Phases = ?";
        $params[] = $phase;
        $types .= 'i';
    }

    // Add filter for program if specified
    if (!empty($program)) {
        $query .= " AND a.Programme = ?";
        $params[] = $program;
        $types .= 'i';
    }

    // Add filter for keyword if specified
    if (!empty($keyword)) {
        $query .= " AND mc.nomMotsCles LIKE ?";
        $params[] = "%" . $keyword . "%";
        $types .= 's';
    }

    // Exclude specified programs
    if (!empty($excludePrograms)) {
        $query .= " AND a.Programme NOT IN (" . implode(',', array_fill(0, count($excludePrograms), '?')) . ")";
        $params = array_merge($params, $excludePrograms);
        $types .= str_repeat('i', count($excludePrograms));
    }

    // Exclude specified phases
    if (!empty($excludePhases)) {
        $query .= " AND a.Phases NOT IN (" . implode(',', array_fill(0, count($excludePhases), '?')) . ")";
        $params = array_merge($params, $excludePhases);
        $types .= str_repeat('i', count($excludePhases));
    }

    // Exclude specified keywords
    if (!empty($excludeKeywords)) {
        $excludeKeywordConditions = array_map(function($keyword) {
            return "mc.nomMotsCles NOT LIKE ?";
        }, $excludeKeywords);
        $query .= " AND (" . implode(' AND ', $excludeKeywordConditions) . ")";
        $params = array_merge($params, array_map(function($keyword) {
            return "%" . $keyword . "%";
        }, $excludeKeywords));
        $types .= str_repeat('s', count($excludeKeywords));
    }

    // Group the results by best practice
    $query .= " GROUP BY bp.numBP, bp.texte";

    // Prepare the SQL statement
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        throw new Exception('Preparation error: ' . $conn->error);
    }

    // Bind the parameters to the SQL statement
    if (!empty($types)) {
        if (!$stmt->bind_param($types, ...$params)) {
            throw new Exception('Binding error: ' . $stmt->error);
        }
    }

    // Execute the SQL statement
    if (!$stmt->execute()) {
        throw new Exception('Execution error: ' . $stmt->error);
    }

    // Get the result of the query
    $result = $stmt->get_result();
    if ($result === false) {
        throw new Exception('Fetching result error: ' . $stmt->error);
    }

    // Initialize an array to store the data
    $data = [];

    // Fetch each row and add it to the data array
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'numBP' => $row['numBP'],
            'texte' => $row['texte'],
            'phases' => $row['phases'] ?: 'None',
            'programmes' => $row['programmes'] ?: 'None',
            'keywords' => $row['keywords'] ?: 'None',
            'appartenance_ids' => $row['appartenance_ids'] // Adding appartenance IDs
        ];
    }

    // Output the data as a JSON-encoded string
    echo json_encode($data);
} catch (Exception $e) {
    // Output a JSON-encoded error message
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    // Close the database connection
    $conn->close();
}
?>
