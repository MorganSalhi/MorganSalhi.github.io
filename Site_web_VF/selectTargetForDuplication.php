<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require 'db_connection.php';
include 'logger.php';

// Log access to the page
logAction('selectTargetForDuplication.php', 'Page accessed');

$bpIds = $_GET['bpIds'] ?? ''; // These IDs are numBP now
$phase = $_GET['phase'] ?? '';
$program = $_GET['program'] ?? '';
$keyword = $_GET['keyword'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPhase = $_POST['newPhase'];
    $newProgram = $_POST['newProgram'];
    $bpIdsArray = explode(',', $bpIds);

    // Get the maximum numAppart value from the database
    $maxIdResult = $conn->query("SELECT MAX(numAppart) AS maxId FROM appartenance");
    if ($maxIdResult) {
        $maxIdRow = $maxIdResult->fetch_assoc();
        $maxId = $maxIdRow['maxId'] + 1;

        foreach ($bpIdsArray as $numBP) {
            // Debug message
            echo "Processing numBP: $numBP<br>";

            // Check if the record matches the filter criteria
            $queryCheck = "SELECT a.numAppart, a.BP, a.Phases, a.Programme, bp.texte, bp.nom 
                           FROM appartenance a 
                           LEFT JOIN association assoc ON a.BP = assoc.BP 
                           LEFT JOIN motscles mc ON assoc.numMC = mc.numMC 
                           LEFT JOIN bonnespratiques bp ON a.BP = bp.numBP 
                           WHERE a.BP = ?";

            $params = [$numBP];
            $types = 'i';

            if (!empty($phase)) {
                $queryCheck .= " AND a.Phases = ?";
                $params[] = $phase;
                $types .= 'i';
            }
            if (!empty($program)) {
                $queryCheck .= " AND a.Programme = ?";
                $params[] = $program;
                $types .= 'i';
            }
            if (!empty($keyword)) {
                $queryCheck .= " AND mc.nomMotsCles LIKE ?";
                $params[] = "%" . $keyword . "%";
                $types .= 's';
            }

            $stmtCheck = $conn->prepare($queryCheck);
            if ($stmtCheck === false) {
                die("Preparation error for check query: " . $conn->error);
                logAction('selectTargetForDuplication.php', 'Preparation error for check query: ' . $conn->error);
            }
            $stmtCheck->bind_param($types, ...$params);
            $stmtCheck->execute();
            $checkResult = $stmtCheck->get_result();
            if ($resultRow = $checkResult->fetch_assoc()) {
                $bpId = $resultRow['BP'];
                $bpTexte = $resultRow['texte'];
                $bpNom = $resultRow['nom'];

                // Debug message
                echo "Found matching BP: $bpId<br>";
                echo "BP Text: $bpTexte<br>";
                echo "BP Name: $bpNom<br>";

                // Duplicate the Appartenance record
                $insertQuery = "INSERT INTO appartenance (numAppart, BP, Programme, Phases) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($insertQuery);
                if ($stmt) {
                    $stmt->bind_param('iiii', $maxId, $bpId, $newProgram, $newPhase);
                    if ($stmt->execute()) {
                        logAction('selectTargetForDuplication.php', "Successfully inserted: numAppart = $maxId, BP = $bpId, Programme = $newProgram, Phases = $newPhase");
                        echo "Successfully inserted: numAppart = $maxId, BP = $bpId, Programme = $newProgram, Phases = $newPhase<br>";
                        $maxId++; // Increment for next usage
                    } else {
                        logAction('selectTargetForDuplication.php', 'Execution error for insert query: ' . $stmt->error);
                        die("Execution error for insert query: " . $stmt->error);
                    }
                } else {
                    die("Preparation error for insert query: " . $conn->error);
                    logAction('selectTargetForDuplication.php', 'Preparation error for insert query: ' . $conn->error);
                }
            } else {
                echo "No matching record found for filters with BP: $numBP<br>";
                logAction('selectTargetForDuplication.php', 'No matching record found for filters with BP: ' . $numBP);
            }
            $stmtCheck->close();
        }
        echo "Successfully duplicated selected best practices into new program/phase.";
        logAction('selectTargetForDuplication.php', 'Successfully duplicated selected best practices');
        header("Location: index.php"); // Redirect to the main page
        exit;
    } else {
        die("Error retrieving max numAppart: " . $conn->error);
        logAction('selectTargetForDuplication.php', 'Error retrieving max numAppart: ' . $conn->error);
    }
}

// Load options for phases and programs
$phasesQuery = "SELECT numPhases, nomPhase FROM phases";
$programsQuery = "SELECT numProg, nomProgramme FROM programmes";

$phasesResult = $conn->query($phasesQuery);
$programsResult = $conn->query($programsQuery);

$phases = $phasesResult->fetch_all(MYSQLI_ASSOC);
$programs = $programsResult->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Duplicate Best Practices</title>
    <link rel="stylesheet" href="selectTargetForDuplication.css">
    <script>
        // Function to log action and redirect to index page
        function logAndRedirect() {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "logger_ajax.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    window.location.href = 'index.php';
                }
            };
            xhr.send("page=selectTargetForDuplication.php&action=Back button clicked");
        }

        // Toggle menu visibility on burger button click
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelector(".menu-burger").addEventListener("click", function() {
                var menuContent = document.querySelector("header ul.menu-content");
                menuContent.style.display = menuContent.style.display === "block" ? "none" : "block";
            });
        });
    </script>
</head>
<body>
    <header>
        <button class="menu-burger">☰ Menu</button>
        <ul class="menu-content">
            <?php
            // Display menu options based on user role
            if (isset($_SESSION['droit'])) {
                echo '<li><a href="createBP.php">Create a BP</a></li>';
                
                echo '<li><a href="view_users.php">Manage accounts</a></li>';
                // Show admin-specific options
                if ($_SESSION['droit'] == 'administrateur' || $_SESSION['droit'] == 'super-administrateur') {
                    echo '<li><a href="manageDeactivatedBPs.php">Manage Deactivated BPs</a></li>';
                    echo '<li><a href="modifexigence.php">Modify Password Requirements</a></li>';
                    echo '<li><a href="view_logs.php">Logs</a></li>';
                }
                // Show super-admin-specific options
                if ($_SESSION['droit'] == 'super-administrateur') {
                }
            }
            ?>
        </ul>
        <?php if (isset($_SESSION['login'])): ?>
            <div class="user-info">
                <p><?php echo $_SESSION['login']; ?></p>
                <p><?php echo $_SESSION['droit']; ?></p>
                <a href="logout.php" class="logout-button">Logout</a>
            </div>
        <?php endif; ?>
    </header>
    <div class="container">
        <div class="form-container">
            <h1>Duplicate Best Practices</h1>
            <form method="post">
                <label for="newPhase">New Phase</label>
                <select name="newPhase" id="newPhase">
                    <?php foreach ($phases as $phase) : ?>
                        <option value="<?= $phase['numPhases'] ?>"><?= $phase['nomPhase'] ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="newProgram">New Program</label>
                <select name="newProgram" id="newProgram">
                    <?php foreach ($programs as $program) : ?>
                        <option value="<?= $program['numProg'] ?>"><?= $program['nomProgramme'] ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Duplicate</button>
            </form>
        </div>
    </div>
    <button onclick="logAndRedirect()" class="back-button">Back</button>
</body>
</html>
