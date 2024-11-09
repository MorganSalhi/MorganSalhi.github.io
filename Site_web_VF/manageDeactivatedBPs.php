<?php
// Start a session to manage user login state
session_start();
// Include database connection and logger files
require 'db_connection.php';
include 'logger.php';

// Log access to the page
logAction('manageDeactivatedBPs.php', 'Page accessed');

$message = '';

// Function to fetch deactivated best practices
function fetchDeactivatedBPs($conn) {
    $sql = "SELECT a.numAppart, bp.nom, bp.texte FROM appartenance a JOIN bonnespratiques bp ON a.BP = bp.numBP WHERE a.active = 0";
    $result = $conn->query($sql);
    $bps = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $bps[] = $row;
        }
    }
    return $bps;
}

// Handle form submission for reactivation or deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['reactivate']) && !empty($_POST['selectedBPs'])) {
        $numApparts = implode(',', array_map('intval', $_POST['selectedBPs']));
        $sql = "UPDATE appartenance SET active = 1 WHERE numAppart IN ($numApparts)";
        $conn->query($sql);
        logAction('manageDeactivatedBPs.php', 'Reactivated BPs: ' . $numApparts);
        $message = "The best practices have been reactivated.";
    } elseif (isset($_POST['delete']) && !empty($_POST['selectedBPs'])) {
        $numApparts = implode(',', array_map('intval', $_POST['selectedBPs']));
        $sql = "DELETE FROM appartenance WHERE numAppart IN ($numApparts)";
        $conn->query($sql);
        logAction('manageDeactivatedBPs.php', 'Permanently deleted BPs: ' . $numApparts);
        $message = "The best practices have been permanently deleted.";
    }
    // Redirect to avoid form resubmission
    header("Location: manageDeactivatedBPs.php");
    exit;
}

// Fetch deactivated best practices
$bps = fetchDeactivatedBPs($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Deactivated Best Practices</title>
    <link rel="stylesheet" href="manageDeactivatedBPs.css">
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
            xhr.send("page=manageDeactivatedBPs.php&action=Back button clicked");
        }

        // Toggle menu visibility on burger button click
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelector(".menu-burger").addEventListener("click", function() {
                var menuContent = document.querySelector("header ul.menu-content");
                menuContent.style.display = menuContent.style.display === "block" ? "none" : "block";
            });

            // Select or deselect all checkboxes
            let selectAll = false;
            document.getElementById("toggleSelectAll").addEventListener("click", function() {
                selectAll = !selectAll;
                const checkboxes = document.querySelectorAll('input[name="selectedBPs[]"]');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = selectAll;
                });
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
        <h1>Manage Deactivated Best Practices</h1>
        <form action="manageDeactivatedBPs.php" method="post">
            <button type="button" id="toggleSelectAll">Select/Deselect All</button>
            <?php if (count($bps) > 0): ?>
                <?php foreach ($bps as $bp): ?>
                    <div class="bp-item">
                        <input type="checkbox" name="selectedBPs[]" value="<?php echo $bp['numAppart']; ?>">
                        <strong><?php echo htmlspecialchars($bp['nom']); ?></strong>: <?php echo htmlspecialchars($bp['texte']); ?>
                    </div>
                <?php endforeach; ?>
                <button type="submit" name="reactivate">Reactivate Selected</button>
                <button type="submit" name="delete" class="delete-button">Permanently Delete Selected</button>
            <?php else: ?>
                <p>No deactivated best practices to display.</p>
            <?php endif; ?>
        </form>
        
    </div>
    <button onclick="logAndRedirect()" class="back-button" style="position: fixed; bottom: 20px; left: 20px;">Back to Home</button>
</body>
</html>
