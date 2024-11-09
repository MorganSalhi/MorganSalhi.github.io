<?php
// Start the session
session_start();

// Include database connection and logger functions
require 'db_connection.php';
include 'logger.php';

// Log the access to the page
logAction('createBP.php', 'Page accessed');

// Check user access rights
if (!isset($_SESSION['login']) || !isset($_SESSION['droit'])) {
    $message = "Access denied. You must be logged in to access this page.";
    logAction('createBP.php', 'Access denied - user not logged in');
    exit;
}

// Get the user role from the session
$role = $_SESSION['droit'];

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Connect to the database
    $connection = new mysqli('localhost', 'root', 'root', 'thales11');
    
    // Check the connection
    if ($connection->connect_error) {
        die("Database connection failed: " . $connection->connect_error);
    }
    
    // Retrieve form data
    $nomBP = $_POST['nom_bp'];
    $texteBP = $_POST['texte_bp'];
    $phases = $_POST['phases'] ?? [];
    $programs = $_POST['programs'] ?? [];
    $keywords = $_POST['keywords'] ?? [];

    // Query to get the last BP number and increment it
    $result = $connection->query("SELECT MAX(numBP) AS max_num FROM bonnespratiques");

    // Check if the query was successful
    if ($result === false) {
        $message = "Error executing query: " . $connection->error;
        logAction('createBP.php', 'Error executing query to retrieve the last BP number');
    } else {
        $row = $result->fetch_assoc();
        $numBP = $row['max_num'] + 1;

        // Query to insert the new BP into the database
        $insert_query = $connection->prepare("INSERT INTO bonnespratiques (numBP, nom, texte) VALUES (?, ?, ?)");
        $insert_query->bind_param("iss", $numBP, $nomBP, $texteBP);

        if ($insert_query->execute() === TRUE) {
            $message = "Best Practice successfully registered!";
            logAction('createBP.php', 'Best Practice successfully registered: ' . $nomBP);

            // Insert into Appartenance table
            foreach ($phases as $phase) {
                foreach ($programs as $program) {
                    $appart_query = $connection->prepare("INSERT INTO appartenance (BP, Programme, Phases) VALUES (?, ?, ?)");
                    $appart_query->bind_param("iii", $numBP, $program, $phase);
                    $appart_query->execute();
                    $appart_query->close();
                }
            }

            // Insert into Association table
            foreach ($keywords as $keyword) {
                $assoc_query = $connection->prepare("INSERT INTO association (BP, numMC) VALUES (?, ?)");
                $assoc_query->bind_param("ii", $numBP, $keyword);
                $assoc_query->execute();
                $assoc_query->close();
            }
        } else {
            $message = "Error registering the Best Practice: " . $insert_query->error;
            logAction('createBP.php', 'Error registering the Best Practice: ' . $insert_query->error);
        }
        $insert_query->close();
    }

    // Close the database connection
    $connection->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Best Practice</title>
    <link rel="stylesheet" href="createBP.css">
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
            xhr.send("page=createBP.php&action=Back button clicked");
        }

        // Toggle menu visibility on burger button click
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelector(".menu-burger").addEventListener("click", function() {
                var menuContent = document.querySelector("header ul.menu-content");
                menuContent.style.display = menuContent.style.display === "block" ? "none" : "block";
            });

            // Load options for phases, programs, and keywords from the server
            loadOptions();
        });

        function loadOptions() {
            fetch('loadFilters.php')
            .then(response => response.json())
            .then(data => {
                const phaseContainer = document.getElementById('phaseContainer');
                const programContainer = document.getElementById('programContainer');
                const keywordContainer = document.getElementById('keywordContainer');

                data.phases.forEach(phase => {
                    const optionItem = document.createElement('div');
                    optionItem.classList.add('option-item');
                    
                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.id = `phase_${phase.numPhases}`;
                    checkbox.name = 'phases[]';
                    checkbox.value = phase.numPhases;
                    
                    const label = document.createElement('label');
                    label.htmlFor = checkbox.id;
                    label.textContent = phase.nomPhase;

                    optionItem.appendChild(checkbox);
                    optionItem.appendChild(label);
                    phaseContainer.appendChild(optionItem);
                });

                data.programs.forEach(program => {
                    const optionItem = document.createElement('div');
                    optionItem.classList.add('option-item');
                    
                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.id = `program_${program.numProg}`;
                    checkbox.name = 'programs[]';
                    checkbox.value = program.numProg;
                    
                    const label = document.createElement('label');
                    label.htmlFor = checkbox.id;
                    label.textContent = program.nomProgramme;

                    optionItem.appendChild(checkbox);
                    optionItem.appendChild(label);
                    programContainer.appendChild(optionItem);
                });

                data.keywords.forEach(keyword => {
                    const optionItem = document.createElement('div');
                    optionItem.classList.add('option-item');
                    
                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.id = `keyword_${keyword.numMC}`;
                    checkbox.name = 'keywords[]';
                    checkbox.value = keyword.numMC;
                    
                    const label = document.createElement('label');
                    label.htmlFor = checkbox.id;
                    label.textContent = keyword.nomMotsCles;

                    optionItem.appendChild(checkbox);
                    optionItem.appendChild(label);
                    keywordContainer.appendChild(optionItem);
                });
            })
            .catch(error => {
                console.error('Error loading options:', error);
            });
        }

        // Function to toggle the selection of checkboxes
        function toggleSelectAll(containerId) {
            const container = document.getElementById(containerId);
            const checkboxes = container.querySelectorAll('input[type="checkbox"]');
            const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
            checkboxes.forEach(checkbox => checkbox.checked = !allChecked);
        }
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
        <form action="createBP.php" method="post">
            <label for="nom_bp">Best Practice Name:</label><br>
            <input type="text" id="nom_bp" name="nom_bp" required><br><br>

            <label for="texte_bp">Best Practice Text:</label><br>
            <textarea id="texte_bp" name="texte_bp" rows="4" required></textarea><br><br>

            <div class="inline-fields">
                <fieldset>
                    <legend>Select Phases:</legend>
                    <button type="button" onclick="toggleSelectAll('phaseContainer')">Select All Phases</button>
                    <div id="phaseContainer"></div>
                </fieldset>
                <fieldset>
                    <legend>Select Programs:</legend>
                    <button type="button" onclick="toggleSelectAll('programContainer')">Select All Programs</button>
                    <div id="programContainer"></div>
                </fieldset>
                <fieldset>
                    <legend>Select Keywords:</legend>
                    <button type="button" onclick="toggleSelectAll('keywordContainer')">Select All Keywords</button>
                    <div id="keywordContainer"></div>
                </fieldset>
            </div>

            <input type="submit" value="Register Best Practice">
        </form>
        <button onclick="logAndRedirect()" class="back-button">Back to Home</button>
    </div>
</body>
</html>

