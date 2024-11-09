<?php
// Database connection details
$servername = "localhost";
$dbUsername = "root";
$dbPassword = "root";
$dbname = "thales11";

// Create a new MySQLi connection
$conn = new mysqli($servername, $dbUsername, $dbPassword, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get filters from GET request
$phaseFilter = $_GET['phase'] ?? '';
$programFilter = $_GET['program'] ?? '';
$keywordFilter = $_GET['keyword'] ?? '';

// Prepare the SQL query to fetch data based on filters
$sql = "SELECT bp.numBP, bp.texte, p.nomProgramme, ph.nomPhase, mk.nomMotsCles
        FROM bonnespratiques bp
        JOIN appartenance a ON bp.numBP = a.numBP
        JOIN programmes p ON a.numProgramme = p.numProg
        JOIN phases ph ON a.numPhases = ph.numPhases
        JOIN association ass ON bp.numBP = ass.numBP
        JOIN motscles mk ON ass.numMC = mk.numMC
        WHERE active=1";

// Append conditions based on filters
if ($phaseFilter) {
    $sql .= " AND ph.nomPhase = '$phaseFilter'";
}
if ($programFilter) {
    $sql .= " AND p.nomProgramme = '$programFilter'";
}
if ($keywordFilter) {
    $sql .= " AND mk.nomMotsCles = '$keywordFilter'";
}

// Execute the query
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Sort Best Practices</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        header, form, table {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        form {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            padding: 20px;
            border: 1px solid #ddd;
            background: #fff;
        }
        form label {
            flex-basis: 100%;
            margin-top: 10px;
        }
        form input, form select, form button {
            padding: 8px;
            width: calc(50% - 10px);
            margin-top: 5px;
        }
        form button {
            width: auto;
            padding: 10px 20px;
            background-color: #0056b3;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        form button:hover {
            background-color: #004494;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f8f8f8;
        }
        .error {
            color: #d9534f;
            text-align: center;
            padding: 10px;
        }
        .success {
            color: #5cb85c;
            text-align: center;
            padding: 10px;
        }
    </style>
</head>
<body>
    <h1>Sort Best Practices</h1>
    <form action="" method="get">
        <label for="phase">Phase:</label>
        <input type="text" id="phase" name="phase" value="<?= htmlspecialchars($phaseFilter) ?>">
        <label for="program">Program:</label>
        <input type="text" id="program" name="program" value="<?= htmlspecialchars($programFilter) ?>">
        <label for="keyword">Keyword:</label>
        <input type="text" id="keyword" name="keyword" value="<?= htmlspecialchars($keywordFilter) ?>">
        <button type="submit">Filter</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>BP Number</th>
                <th>Text</th>
                <th>Program</th>
                <th>Phase</th>
                <th>Keyword</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Display the results if any
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['numBP']}</td>
                            <td>{$row['texte']}</td>
                            <td>{$row['nomProgramme']}</td>
                            <td>{$row['nomPhase']}</td>
                            <td>{$row['nomMotsCles']}</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No data found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
