<?php
session_start(); // Start the session to manage user authentication and permissions
require 'db_connection.php'; // Include the database connection file
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exclude Criteria</title>
    <link rel="stylesheet" href="excludeCriteria-style.css">
    <script>
        // Execute when the DOM is fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            const programContainer = document.getElementById('programContainer');
            const phaseContainer = document.getElementById('phaseContainer');
            const keywordContainer = document.getElementById('keywordContainer');
            const bpContainer = document.getElementById('bpContainer');

            // Retrieve exclusions from the session
            const excludePrograms = <?php echo json_encode($_SESSION['excludePrograms'] ?? []); ?>;
            const excludePhases = <?php echo json_encode($_SESSION['excludePhases'] ?? []); ?>;
            const excludeKeywords = <?php echo json_encode($_SESSION['excludeKeywords'] ?? []); ?>;

            // Function to load programs, phases, and keywords from the server
            function loadOptions() {
                fetch('loadFilters.php')
                .then(response => response.json())
                .then(data => {
                    // Load programs
                    data.programs.forEach(program => {
                        const programId = program.numProg;
                        const programName = program.nomProgramme;
                        const checkboxId = `programCheckbox_${programId}`;
                        const checked = excludePrograms.includes(String(programId)) ? 'checked' : '';

                        programContainer.innerHTML += `
                            <div class="option-item">
                                <input type="checkbox" id="${checkboxId}" name="excludePrograms[]" value="${programId}" ${checked}>
                                <label for="${checkboxId}">${programName}</label>
                            </div>`;
                    });

                    // Load phases
                    data.phases.forEach(phase => {
                        const phaseId = phase.numPhases;
                        const phaseName = phase.nomPhase;
                        const checkboxId = `phaseCheckbox_${phaseId}`;
                        const checked = excludePhases.includes(String(phaseId)) ? 'checked' : '';

                        phaseContainer.innerHTML += `
                            <div class="option-item">
                                <input type="checkbox" id="${checkboxId}" name="excludePhases[]" value="${phaseId}" ${checked}>
                                <label for="${checkboxId}">${phaseName}</label>
                            </div>`;
                    });

                    // Load keywords
                    data.keywords.forEach(keyword => {
                        const keywordId = keyword.numMC;
                        const keywordName = keyword.nomMotsCles;
                        const checkboxId = `keywordCheckbox_${keywordId}`;
                        const checked = excludeKeywords.includes(String(keywordId)) ? 'checked' : '';

                        keywordContainer.innerHTML += `
                            <div class="option-item">
                                <input type="checkbox" id="${checkboxId}" name="excludeKeywords[]" value="${keywordId}" ${checked}>
                                <label for="${checkboxId}">${keywordName}</label>
                            </div>`;
                    });

                    // Load best practices
                    data.bps.forEach(bp => {
                        const bpId = bp.numBP;
                        const bpText = bp.texte;
                        const checkboxId = `bpCheckbox_${bpId}`;
                        const phaseSelectId = `phaseSelect_${bpId}`;

                        bpContainer.innerHTML += `
                            <div class="bp-item">
                                <input type="checkbox" id="${checkboxId}" name="assignedBPs[${bpId}][selected]" value="${bpId}">
                                <label for="${checkboxId}">${bpText}</label>
                                <label for="${phaseSelectId}">Phase:</label>
                                <select id="${phaseSelectId}" name="assignedBPs[${bpId}][phase]" required>
                                    ${data.phases.map(phase => `<option value="${phase.numPhases}">${phase.nomPhase}</option>`).join('')}
                                </select>
                            </div>`;
                    });
                })
                .catch(error => {
                    console.error('Error loading options:', error);
                });
            }

            // Function to select all checkboxes in a container
            function selectAll(containerId) {
                const checkboxes = document.querySelectorAll(`#${containerId} input[type="checkbox"]`);
                const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
                checkboxes.forEach(checkbox => checkbox.checked = !allChecked);
            }

            // Function to delete selected items
            function deleteSelected(type) {
                const containerId = type === 'program' ? 'programContainer' : 'keywordContainer';
                const checkboxes = document.querySelectorAll(`#${containerId} input[type="checkbox"]:checked`);
                const ids = Array.from(checkboxes).map(checkbox => checkbox.value);

                if (ids.length === 0) {
                    alert(`No ${type}s selected for deletion.`);
                    return;
                }

                if (confirm(`Are you sure you want to delete the selected ${type}s?`)) {
                    fetch(`deleteSelectedItems.php`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ type, ids })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(`${type.charAt(0).toUpperCase() + type.slice(1)}s deleted successfully.`);
                            location.reload();
                        } else {
                            alert(`Failed to delete selected ${type}s.`);
                        }
                    })
                    .catch(error => {
                        console.error('Error deleting items:', error);
                    });
                }
            }

            // Function to open a modal
            function openModal(modalId) {
                document.getElementById(modalId).style.display = "block";
            }

            // Function to close a modal
            function closeModal(modalId) {
                document.getElementById(modalId).style.display = "none";
            }

            // Function to add a program
            function addProgram() {
                const programName = document.getElementById('newProgramName').value;
                const assignedBPs = {};

                document.querySelectorAll('#bpContainer .bp-item').forEach(item => {
                    const bpId = item.querySelector('input[type="checkbox"]').value;
                    const selected = item.querySelector('input[type="checkbox"]').checked;
                    const phase = item.querySelector('select').value;

                    assignedBPs[bpId] = { selected, phase };
                });

                fetch('createProgram.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ programName, assignedBPs })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Program added successfully.');
                        closeModal('programModal');
                        location.reload();
                    } else {
                        alert('Error adding program.');
                    }
                })
                .catch(error => {
                    console.error('Error adding program:', error);
                });
            }

            // Function to add a keyword
            function addKeyword() {
                const keywordName = document.getElementById('newKeywordName').value;

                fetch('createKeyword.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ keywordName })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Keyword added successfully.');
                        closeModal('keywordModal');
                        location.reload();
                    } else {
                        alert('Error adding keyword.');
                    }
                })
                .catch(error => {
                    console.error('Error adding keyword:', error);
                });
            }

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
                xhr.send("page=excludeCriteria.php&action=Back button clicked");
            }

            // Load options when the page is loaded
            loadOptions();

            // Expose functions to the global scope
            window.selectAll = selectAll;
            window.deleteSelected = deleteSelected;
            window.openModal = openModal;
            window.closeModal = closeModal;
            window.addProgram = addProgram;
            window.addKeyword = addKeyword;
            window.logAndRedirect = logAndRedirect;

            // Toggle menu visibility on burger button click
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
    <main>
        <h1>Exclude Criteria</h1>
        <form id="excludeForm" method="POST" action="processExclusions.php">
            <div class="flex-container">
                <fieldset>
                    <legend>Exclude Programs</legend>
                    <button type="button" onclick="selectAll('programContainer')">Select All Programs</button>
                    <?php if ($_SESSION['droit'] == 'administrateur' || $_SESSION['droit'] == 'super-administrateur'): ?>
                        <button type="button" onclick="openModal('programModal')">Add Program</button>
                    <?php endif; ?>
                    <div id="programContainer"></div>
                    <?php if ($_SESSION['droit'] == 'administrateur' || $_SESSION['droit'] == 'super-administrateur'): ?>
                        <button type="button" onclick="deleteSelected('program')">Delete Selected Programs</button>
                    <?php endif; ?>
                </fieldset>
                <fieldset>
                    <legend>Exclude Phases</legend>
                    <button type="button" onclick="selectAll('phaseContainer')">Select All Phases</button>
                    <div id="phaseContainer"></div>
                </fieldset>
                <fieldset>
                    <legend>Exclude Keywords</legend>
                    <button type="button" onclick="selectAll('keywordContainer')">Select All Keywords</button>
                    <?php if ($_SESSION['droit'] == 'administrateur' || $_SESSION['droit'] == 'super-administrateur'): ?>
                        <button type="button" onclick="openModal('keywordModal')">Add Keyword</button>
                    <?php endif; ?>
                    <div id="keywordContainer"></div>
                    <?php if ($_SESSION['droit'] == 'administrateur' || $_SESSION['droit'] == 'super-administrateur'): ?>
                        <button type="button" onclick="deleteSelected('keyword')">Delete Selected Keywords</button>
                    <?php endif; ?>
                </fieldset>
            </div>
            <button type="submit">Apply Exclusions</button>
        </form>
        <button onclick="logAndRedirect()" class="back-button">Back to Home</button>
    </main>

    <!-- Program Modal -->
    <div id="programModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('programModal')">&times;</span>
            <h2>Add a Program</h2>
            <label for="newProgramName">Program Name:</label>
            <input type="text" id="newProgramName" name="newProgramName" required>
            <div id="bpContainer">
                <label>Assign Best Practices:</label>
                <!-- BP items will be added here by JavaScript -->
            </div>
            <button type="button" onclick="addProgram()">Add Program</button>
        </div>
    </div>

    <!-- Keyword Modal -->
    <div id="keywordModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('keywordModal')">&times;</span>
            <h2>Add a Keyword</h2>
            <label for="newKeywordName">Keyword Name:</label>
            <input type="text" id="newKeywordName" name="newKeywordName" required>
            <button type="button" onclick="addKeyword()">Add Keyword</button>
        </div>
    </div>
</body>
</html>
