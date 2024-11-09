<?php
// Start session to track user authentication and access rights
session_start();
// Include database connection and logger files
require 'db_connection.php';
include 'logger.php';

// Log access to the page
logAction('deactivateBPs.php', 'Page accessed');

// Retrieve BP IDs and filter criteria from the query string
$bpIds = $_GET['bpIds'] ?? '';
$phaseFilter = $_GET['phase'] ?? '';
$programFilter = $_GET['program'] ?? '';
$keywordFilter = $_GET['keyword'] ?? '';
$message = '';

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    if ($_POST['confirm'] === 'yes') {
        // Deactivation process after confirmation
        $bpIdsArray = explode(',', $bpIds);
        
        // Base query to deactivate records
        $query = "UPDATE appartenance a
                  LEFT JOIN association assoc ON a.BP = assoc.BP
                  LEFT JOIN motscles mc ON assoc.numMC = mc.numMC
                  SET a.active = 0
                  WHERE a.BP IN (" . implode(',', array_fill(0, count($bpIdsArray), '?')) . ")";

        $params = $bpIdsArray;
        $types = str_repeat('i', count($bpIdsArray));

        // Add filters if specified
        if (!empty($phaseFilter)) {
            $query .= " AND a.Phases = ?";
            $params[] = $phaseFilter;
            $types .= 'i';
        }

        if (!empty($programFilter)) {
            $query .= " AND a.Programme = ?";
            $params[] = $programFilter;
            $types .= 'i';
        }

        if (!empty($keywordFilter)) {
            $query .= " AND mc.nomMotsCles LIKE ?";
            $params[] = "%" . $keywordFilter . "%";
            $types .= 's';
        }

        // Prepare and execute the query
        $stmt = $conn->prepare($query);
        if ($stmt === false) {
            throw new Exception('Preparation error: ' . $conn->error);
        }

        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        // Log successful deactivation with BP IDs
        logAction('deactivateBPs.php', "BPs deactivated: " . implode(', ', $bpIdsArray));

        $message = "Best practices have been deactivated.";
    } elseif ($_POST['confirm'] === 'no') {
        $message = "Operation cancelled.";
        // Log cancellation
        logAction('deactivateBPs.php', 'Deactivation cancelled');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Deactivate BPs</title>
    <link rel="stylesheet" href="deactivateBPs.css">
    <script>
        // Function to log action and redirect to the index page
        function logAndRedirect() {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "logger_ajax.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    window.location.href = 'index.php';
                }
            };
            xhr.send("page=deactivateBPs.php&action=Back button clicked");
        }

        // Event listener for menu toggle
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
        <h1>Deactivate Best Practices</h1>
        <form method="post">
            <input type="hidden" name="bpIds" value="<?= htmlspecialchars($bpIds) ?>">
            <button type="submit" name="confirm" value="yes" style="background-color: green;">Confirm</button>
            <button type="submit" name="confirm" value="no" style="background-color: red;">Cancel</button>
        </form>
        <button onclick="logAndRedirect()" class="back-button">Back</button>
    </div>
</body>
</html>
