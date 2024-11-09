<?php
// Start the session to track user authentication and access rights
session_start();
// Include database connection and logger functions
require 'db_connection.php';
include 'logger.php';

// Log the access to the page
logAction('deleteBPs.php', 'Page accessed');

// Check user access rights
if (!isset($_SESSION['login']) || ($_SESSION['droit'] != 'administrateur' && $_SESSION['droit'] != 'super-administrateur')) {
    echo "<p class='error'>Access denied. You must be logged in as an administrator.</p>";
    logAction('deleteBPs.php', 'Access denied');
    exit;
}

$message = '';
$bpIds = $_GET['bpIds'] ?? '';
$phase = $_GET['phase'] ?? '';
$program = $_GET['program'] ?? '';
$keyword = $_GET['keyword'] ?? '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    if ($_POST['confirm'] === 'yes') {
        // Convert the BP IDs string to an array
        $bpIdsArray = explode(',', $bpIds);
        $placeholders = implode(',', array_fill(0, count($bpIdsArray), '?'));

        // Construct the base query for deleting from Appartenance table with filters
        $queryAppartenance = "DELETE FROM appartenance WHERE BP IN ($placeholders)";
        $params = $bpIdsArray;
        $types = str_repeat('i', count($bpIdsArray));

        // Add filters to the query
        if (!empty($phase)) {
            $queryAppartenance .= " AND Phases = ?";
            $params[] = $phase;
            $types .= 'i';
        }
        if (!empty($program)) {
            $queryAppartenance .= " AND Programme = ?";
            $params[] = $program;
            $types .= 'i';
        }
        if (!empty($keyword)) {
            $queryAppartenance .= " AND BP IN (SELECT assoc.BP FROM association assoc LEFT JOIN motscles mc ON assoc.numMC = mc.numMC WHERE mc.nomMotsCles LIKE ?)";
            $params[] = "%" . $keyword . "%";
            $types .= 's';
        }

        // Prepare and execute the deletion query
        $stmt = $conn->prepare($queryAppartenance);
        if ($stmt === false) {
            echo "<p class='error'>Error preparing statement: " . htmlspecialchars($conn->error) . "</p>";
            exit;
        }
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute() === false) {
            echo "<p class='error'>Error executing statement: " . htmlspecialchars($stmt->error) . "</p>";
            exit;
        }
        $stmt->close();

        // Log the successful deletion
        logAction('deleteBPs.php', "appartenance entries deleted: " . implode(', ', $bpIdsArray));

        // Check if the BP is in other Appartenance entries
        foreach ($bpIdsArray as $bpId) {
            $queryCheck = "SELECT COUNT(*) as count FROM appartenance WHERE BP = ?";
            $stmtCheck = $conn->prepare($queryCheck);
            if ($stmtCheck === false) {
                echo "<p class='error'>Error preparing check statement: " . htmlspecialchars($conn->error) . "</p>";
                exit;
            }
            $stmtCheck->bind_param('i', $bpId);
            $stmtCheck->execute();
            $resultCheck = $stmtCheck->get_result()->fetch_assoc();
            if ($resultCheck['count'] == 0) { // If the BP is not linked to any other appartenance, delete from bonnespratiques
                $queryBP = "DELETE FROM bonnespratiques WHERE numBP = ?";
                $stmtBP = $conn->prepare($queryBP);
                if ($stmtBP === false) {
                    echo "<p class='error'>Error preparing BP statement: " . htmlspecialchars($conn->error) . "</p>";
                    exit;
                }
                $stmtBP->bind_param('i', $bpId);
                $stmtBP->execute();
                $stmtBP->close();
                
                // Log the successful deletion of BP
                logAction('deleteBPs.php', "BP deleted: $bpId");
            }
            $stmtCheck->close();
        }

        $message = "Best practices have been permanently deleted.";
    } elseif ($_POST['confirm'] === 'no') {
        $message = "Deletion cancelled.";
        // Log the cancellation
        logAction('deleteBPs.php', 'Deletion cancelled');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BP Deletion</title>
    <link rel="stylesheet" href="deleteBPs.css">
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
            xhr.send("page=deleteBPs.php&action=Back button clicked");
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
            <li><a href="guide-utilisation.php">User Guide</a></li>
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
        <?php if (!empty($message)): ?>
            <p><?php echo $message; ?></p>
        <?php endif; ?>
        <h1>Best Practices Deletion</h1>
        <form method="post">
            <input type="hidden" name="bpIds" value="<?= htmlspecialchars($bpIds) ?>">
            <button type="submit" name="confirm" value="yes" style="background-color: green;">Confirm</button>
            <button type="submit" name="confirm" value="no" style="background-color: red;">Cancel</button>
        </form>
        <button onclick="logAndRedirect()" class="back-button">Back</button>
    </div>
</body>
</html>
