<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home</title>
    <link rel="stylesheet" href="index-style.css">
    <style>
        /* Additional styles */
        .centered {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .button-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 10px;
        }

        .modify-button {
            background-color: #06668C; /* Blue */
            border: none;
            color: white;
            padding: 10px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 5px;
        }

        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgb(0,0,0); /* Fallback color */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .modal h2 {
            color: #06668C;
        }

        .select-all-btn {
            margin: 10px 0;
            padding: 10px;
            background-color: #06668C;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .select-all-btn:hover {
            background-color: #0056a3;
        }
    </style>
</head>
<body>
    <!-- Header section with navigation and user information -->
    <header>
        <!-- Menu button for mobile view -->
        <button class="menu-burger">☰ Menu</button>
        <ul class="menu-content">
            <li><a href="guide-utilisation.php">User Guide</a></li>
            <?php
            session_start(); // Start session to manage user authentication
            if (isset($_SESSION['droit'])) {
                echo '<li><a href="createBP.php">Create a BP</a></li>';
                
                echo '<li><a href="view_users.php">Manage accounts</a></li>';
                // Show admin-specific options
                if ($_SESSION['droit'] == 'administrateur' || $_SESSION['droit'] == 'super-administrateur') {
                    echo '<li><a href="manageDeactivatedBPs.php">Manage Deactivated BPs</a></li>';
                    echo '<li><a href="modifexigence.php">Modify Password Requirements</a></li>';
                    echo '<li><a href="view_logs.php">Logs</a></li>';
                }
            }
            ?>
        </ul>
        <!-- Filters for phase, program, and keyword search -->
        <select id="phaseFilter">
            <option value="">All Phases</option>
        </select>
        <select id="programFilter">
            <option value="">All Programs</option>
        </select>
        <input type="text" id="keywordFilter" placeholder="Search by keywords...">
        <!-- User information and logout button -->
        <?php if (isset($_SESSION['login'])): ?>
            <div class="user-info">
                <p><?php echo $_SESSION['login']; ?></p>
                <p><?php echo $_SESSION['droit']; ?></p>
                <a href="logout.php" class="logout-button">Logout</a>
            </div>
        <?php endif; ?>
    </header>
    <!-- Main container for best practices and logs -->
    <div id="mainContainer" class="<?php echo ($_SESSION['droit'] == 'utilisateur') ? 'centered' : ''; ?>"> 
        <div id="bpContainer">
            <div id="bpList">Please select criteria to display best practices.</div>
        </div>
        <?php if (isset($_SESSION['droit']) && ($_SESSION['droit'] == 'administrateur' || $_SESSION['droit'] == 'super-administrateur')): ?>
            <div id="logsContainer" onclick="redirectToLogsPage()">
                <div id="logsList"></div>
            </div>
        <?php endif; ?>
    </div>
    <!-- Form for exporting and managing best practices -->
    <form action="exportData.php" method="post" class="<?php echo ($_SESSION['droit'] == 'utilisateur') ? 'button-container' : ''; ?>">
        <input type="hidden" name="action" value="export">
        <input type="hidden" name="bpIds" id="exportBpIds"> <!-- IDs will be injected by JS -->
        <select name="format">
            <option value="excel">Excel</option>
            <option value="pdf">PDF</option>
        </select>
        <button type="submit">Export</button>
        <button type="button" onclick="redirectToDuplicationPage()">Duplicate Selected BPs</button>
        <button type="button" onclick="redirectToDeactivatePage()">Deactivate Selected BPs</button>
        <?php if (isset($_SESSION['droit']) && ($_SESSION['droit'] == 'administrateur' || $_SESSION['droit'] == 'super-administrateur')): ?>
            <button type="button" onclick="redirectToDeletePage()" style="background-color: red;">Permanently Delete Selected BPs</button>
        <?php endif; ?>
        <button type="button" onclick="toggleSelectAll()" style="background-color: green;">Select All</button>
        <button type="button" onclick="openExclusionPage()" >Exclude Programs/Phases/Keywords</button>
    </form>
    
    <!-- Modal for modifying a best practice -->
    <div id="modifyBpModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModifyBpModal()">&times;</span>
            <h2>Modify Best Practice</h2>
            <form id="modifyBpForm">
                <input type="hidden" id="modalBpId" name="bpId">
                <div id="modifyPhaseContainer"></div>
                <div id="modifyProgramContainer"></div>
                <div id="modifyKeywordContainer"></div>
                <button type="submit">Save Changes</button>
            </form>
        </div>
    </div>

    <script src="scripts.js"></script>
    <script>
        // Function to toggle the selection of all checkboxes
        function toggleSelectAll() {
            const checkboxes = document.querySelectorAll('input[name="bpCheckbox"]');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            checkboxes.forEach(cb => {
                cb.checked = !allChecked;
            });
        }

        // Function to open the exclusion criteria page
        function openExclusionPage() {
            window.location.href = 'excludeCriteria.php';
        }

        // Function to redirect to the duplication page with selected BPs
        function redirectToDuplicationPage() {
            const checkedBoxes = document.querySelectorAll('input[name="bpCheckbox"]:checked');
            const bpIds = Array.from(checkedBoxes).map(cb => cb.value).join(',');
            if (bpIds) {
                const phaseFilter = document.getElementById('phaseFilter').value;
                const programFilter = document.getElementById('programFilter').value;
                const keywordFilter = document.getElementById('keywordFilter').value;
                window.location.href = `selectTargetForDuplication.php?bpIds=${encodeURIComponent(bpIds)}&phase=${encodeURIComponent(phaseFilter)}&program=${encodeURIComponent(programFilter)}&keyword=${encodeURIComponent(keywordFilter)}`;
            } else {
                alert('Please select at least one best practice to duplicate.');
            }
        }

        // Function to redirect to the deactivation page with selected BPs
        function redirectToDeactivatePage() {
            const checkedBoxes = document.querySelectorAll('input[name="bpCheckbox"]:checked');
            const bpIds = Array.from(checkedBoxes).map(cb => cb.value).join(',');
            if (bpIds) {
                const phaseFilter = document.getElementById('phaseFilter').value;
                const programFilter = document.getElementById('programFilter').value;
                const keywordFilter = document.getElementById('keywordFilter').value;
                window.location.href = `deactivateBPs.php?bpIds=${encodeURIComponent(bpIds)}&phase=${encodeURIComponent(phaseFilter)}&program=${encodeURIComponent(programFilter)}&keyword=${encodeURIComponent(keywordFilter)}`;
            } else {
                alert('Please select at least one best practice to deactivate.');
            }
        }

        // Function to redirect to the deletion page with selected BPs
        function redirectToDeletePage() {
            const checkedBoxes = document.querySelectorAll('input[name="bpCheckbox"]:checked');
            const bpIds = Array.from(checkedBoxes).map(cb => cb.value).join(',');
            if (bpIds) {
                const phaseFilter = document.getElementById('phaseFilter').value;
                const programFilter = document.getElementById('programFilter').value;
                const keywordFilter = document.getElementById('keywordFilter').value;
                window.location.href = `deleteBPs.php?bpIds=${encodeURIComponent(bpIds)}&phase=${encodeURIComponent(phaseFilter)}&program=${encodeURIComponent(programFilter)}&keyword=${encodeURIComponent(keywordFilter)}`;
            } else {
                alert('Please select at least one best practice to permanently delete.');
            }
        }

        // Function to redirect to the logs page
        function redirectToLogsPage() {
            window.location.href = 'view_logs.php';
        }

        // Event listener for form submission to inject selected BP IDs
        document.querySelector('form').addEventListener('submit', function(e) {
            const checkedBoxes = document.querySelectorAll('input[name="bpCheckbox"]:checked');
            const bpIds = Array.from(checkedBoxes).map(cb => cb.value).join(',');
            document.getElementById('exportBpIds').value = bpIds;
        });

        // Function to open the modal for modifying a best practice
        function openModifyBpModal(bpId) {
            const modal = document.getElementById('modifyBpModal');
            document.getElementById('modalBpId').value = bpId;
            modal.style.display = 'block';
            fetch(`loadBpDetails.php?bpId=${bpId}`)
            .then(response => response.json())
            .then(data => {
                const modifyPhaseContainer = document.getElementById('modifyPhaseContainer');
                const modifyProgramContainer = document.getElementById('modifyProgramContainer');
                const modifyKeywordContainer = document.getElementById('modifyKeywordContainer');

                modifyPhaseContainer.innerHTML = '<h3>Phases</h3><button type="button" class="select-all-btn" onclick="selectAll(\'phases[]\')">Select All Phases</button>';
                modifyProgramContainer.innerHTML = '<h3>Programs</h3><button type="button" class="select-all-btn" onclick="selectAll(\'programs[]\')">Select All Programs</button>';
                modifyKeywordContainer.innerHTML = '<h3>Keywords</h3><button type="button" class="select-all-btn" onclick="selectAll(\'keywords[]\')">Select All Keywords</button>';

                const phases = new Set();
                const programs = new Set();
                const keywords = new Set();

                data.phases.forEach(phase => {
                    if (!phases.has(phase.id)) {
                        const isChecked = phase.assigned ? 'checked' : '';
                        modifyPhaseContainer.innerHTML += `
                            <div>
                                <input type="checkbox" id="phase_${phase.id}" name="phases[]" value="${phase.id}" ${isChecked}>
                                <label for="phase_${phase.id}">${phase.name}</label>
                            </div>`;
                        phases.add(phase.id);
                    }
                });

                data.programs.forEach(program => {
                    if (!programs.has(program.id)) {
                        const isChecked = program.assigned ? 'checked' : '';
                        modifyProgramContainer.innerHTML += `
                            <div>
                                <input type="checkbox" id="program_${program.id}" name="programs[]" value="${program.id}" ${isChecked}>
                                <label for="program_${program.id}">${program.name}</label>
                            </div>`;
                        programs.add(program.id);
                    }
                });

                data.keywords.forEach(keyword => {
                    if (!keywords.has(keyword.id)) {
                        const isChecked = keyword.assigned ? 'checked' : '';
                        modifyKeywordContainer.innerHTML += `
                            <div>
                                <input type="checkbox" id="keyword_${keyword.id}" name="keywords[]" value="${keyword.id}" ${isChecked}>
                                <label for="keyword_${keyword.id}">${keyword.name}</label>
                            </div>`;
                        keywords.add(keyword.id);
                    }
                });
            })
            .catch(error => {
                console.error('Error loading BP details:', error);
            });
        }

        // Function to select/deselect all checkboxes for phases, programs, or keywords
        function selectAll(name) {
            const checkboxes = document.querySelectorAll(`input[name="${name}"]`);
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            checkboxes.forEach(cb => {
                cb.checked = !allChecked;
            });
        }

        // Function to close the modal for modifying a best practice
        function closeModifyBpModal() {
            const modal = document.getElementById('modifyBpModal');
            modal.style.display = 'none';
        }

        // Event listener for the modify BP form submission
        document.getElementById('modifyBpForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch('modifyBp.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Best practice modified successfully');
                    closeModifyBpModal();
                    location.reload();
                } else {
                    alert('Error modifying the best practice');
                }
            })
            .catch(error => {
                console.error('Error modifying best practice:', error);
            });
        });

        // Event listener to close the modal when the close button is clicked
        document.querySelector('.close').addEventListener('click', closeModifyBpModal);

        // Event listener to close the modal when clicking outside of it
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('modifyBpModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const phaseFilter = document.getElementById('phaseFilter');
            const programFilter = document.getElementById('programFilter');
            const keywordFilter = document.getElementById('keywordFilter');
            const bpList = document.getElementById('bpList');
            const logsList = document.getElementById('logsList');
            const menuBurger = document.querySelector('.menu-burger');
            const menuContent = document.querySelector('.menu-content');

            // Function to load filter options for phases and programs
            function loadFilterOptions() {
                fetch('loadFilters.php')
                    .then(response => response.json())
                    .then(data => {
                        phaseFilter.innerHTML = '<option value="">All Phases</option>';
                        programFilter.innerHTML = '<option value="">All Programs</option>';
                        const phaseSet = new Set();
                        const programSet = new Set();
                        data.phases.forEach(phase => {
                            if (!phaseSet.has(phase.numPhases)) {
                                phaseFilter.innerHTML += `<option value="${phase.numPhases}">${phase.nomPhase}</option>`;
                                phaseSet.add(phase.numPhases);
                            }
                        });
                        data.programs.forEach(program => {
                            if (!programSet.has(program.numProg)) {
                                programFilter.innerHTML += `<option value="${program.numProg}">${program.nomProgramme}</option>`;
                                programSet.add(program.numProg);
                            }
                        });
                    })
                    .catch(error => {
                        console.error('Error loading filter options:', error);
                    });
            }

            // Function to load best practices based on selected filters
            function loadBPs() {
                fetch('loadData.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `phase=${phaseFilter.value}&program=${programFilter.value}&keyword=${keywordFilter.value}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        bpList.innerHTML = `Error loading: ${data.error}`;
                        console.error(data.error);
                        return;
                    }
                    if (data) {
                        const excludePrograms = <?php echo json_encode($_SESSION['excludePrograms'] ?? []); ?>;
                        const excludePhases = <?php echo json_encode($_SESSION['excludePhases'] ?? []); ?>;
                        const excludeKeywords = <?php echo json_encode($_SESSION['excludeKeywords'] ?? []); ?>;

                        const filteredData = data.filter(bp => {
                            if (excludePrograms.includes(bp.programmes)) {
                                return false;
                            }
                            if (excludePhases.includes(bp.phases)) {
                                return false;
                            }
                            const keywords = bp.keywords.split(', ');
                            for (const keyword of excludeKeywords) {
                                if (keywords.includes(keyword)) {
                                    return false;
                                }
                            }
                            return true;
                        });

                        bpList.innerHTML = filteredData.map(bp => {
                            const numBP = bp.numBP || "Not defined";
                            const texte = bp.texte || "Not defined";
                            const phases = bp.phases || "None";
                            const programmes = bp.programmes || "None";
                            const keywords = bp.keywords || "None";
                            return `
                                <div class="bp-card">
                                    <div class="bp-row">
                                        <div class="bp-column"><strong>Text</strong><p>${texte}</p></div>
                                        <div class="bp-column"><strong>Phase</strong><p>${phases}</p></div>
                                        <div class="bp-column"><strong>Program</strong><p>${programmes}</p></div>
                                        <div class="bp-column"><strong>Keywords</strong><p>${keywords}</p></div>
                                    </div>
                                    <div class="checkbox-container">
                                        <input type="checkbox" name="bpCheckbox" value="${numBP}">
                                        <?php if ($_SESSION['droit'] == 'administrateur' || $_SESSION['droit'] == 'super-administrateur'): ?>
                                            <button class="modify-button" onclick="openModifyBpModal(${numBP})">Modify</button>
                                        <?php endif; ?>
                                    </div>
                                </div>`;
                        }).join('');
                    } else {
                        bpList.innerHTML = 'No matching best practices.';
                    }
                })
                .catch(error => {
                    console.error('Error loading the BPs:', error);
                    bpList.innerHTML = 'Loading error.';
                });
            }

            // Function to load logs data
            function loadLogs() {
                fetch('loadLogs.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            logsList.innerHTML = `Loading error: ${data.error}`;
                            console.error(data.error);
                            return;
                        }
                        if (data) {
                            logsList.innerHTML = `<table><thead><tr><th>ID</th><th>Page</th><th>Action</th><th>Timestamp</th></tr></thead><tbody>`;
                            data.forEach(log => {
                                logsList.innerHTML += `<tr><td>${log.id}</td><td>${log.page}</td><td>${log.action}</td><td>${log.timestamp}</td></tr>`;
                            });
                            logsList.innerHTML += `</tbody></table>`;
                        } else {
                            logsList.innerHTML = 'No logs found.';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading the logs:', error);
                        logsList.innerHTML = 'Loading error.';
                    });
            }

            // Load initial filter options, best practices, and logs
            loadFilterOptions();
            loadBPs();
            loadLogs();

            // Event listeners to reload best practices when filters change
            phaseFilter.addEventListener('change', loadBPs);
            programFilter.addEventListener('change', loadBPs);
            keywordFilter.addEventListener('keyup', loadBPs);

            // Toggle menu visibility on burger button click
            menuBurger.addEventListener('click', function() {
                menuContent.style.display = menuContent.style.display === 'none' ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>
