document.addEventListener('DOMContentLoaded', function() {
    const phaseFilter = document.getElementById('phaseFilter'); // Dropdown for phase filter
    const programFilter = document.getElementById('programFilter'); // Dropdown for program filter
    const keywordFilter = document.getElementById('keywordFilter'); // Input field for keyword filter
    const bpList = document.getElementById('bpList'); // Container for displaying best practices
    const logsList = document.getElementById('logsList'); // Container for displaying logs

    // Function to load filter options from the server
    function loadFilterOptions() {
        fetch('loadFilters.php')
            .then(response => response.json())
            .then(data => {
                // Clear current options
                phaseFilter.innerHTML = '<option value="">All Phases</option>';
                programFilter.innerHTML = '<option value="">All Programs</option>';

                // Use a Set to ensure uniqueness
                const phaseSet = new Set();
                const programSet = new Set();

                // Populate phase filter options
                data.phases.forEach(phase => {
                    if (!phaseSet.has(phase.numPhases)) {
                        phaseFilter.innerHTML += `<option value="${phase.numPhases}">${phase.nomPhase}</option>`;
                        phaseSet.add(phase.numPhases);
                    }
                });

                // Populate program filter options
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

            // Populate the best practices list
            if (data.length > 0) {
                bpList.innerHTML = data.map(bp => {
                    const numBP = bp.numBP || "Not defined";
                    const texte = bp.texte || "Not defined";
                    const phases = bp.phases !== 'None' ? bp.phases : 'None';
                    const programmes = bp.programmes !== 'None' ? bp.programmes : 'None';
                    const keywords = bp.keywords !== 'None' ? bp.keywords : 'None';

                    return `
                        <div class="bp-card">
                            <div class="bp-row">
                                <div class="bp-column"><strong>Text</strong><p>${texte}</p></div>
                                <div class="bp-column"><strong>Phases</strong><p>${phases}</p></div>
                                <div class="bp-column"><strong>Programs</strong><p>${programmes}</p></div>
                                <div class="bp-column"><strong>Keywords</strong><p>${keywords}</p></div>
                            </div>
                            <div class="checkbox-container">
                                <input type="checkbox" name="bpCheckbox" value="${numBP}">
                            </div>
                        </div>`;
                }).join('');
            } else {
                bpList.innerHTML = 'No matching best practices.';
            }
        })
        .catch(error => {
            console.error('Error loading the best practices:', error);
            bpList.innerHTML = 'Error loading.';
        });
    }

    // Function to load logs from the server
    function loadLogs() {
        fetch('loadLogs.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                logsList.innerHTML = `Error loading: ${data.error}`;
                console.error(data.error);
                return;
            }

            // Populate the logs list
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
            logsList.innerHTML = 'Error loading.';
        });
    }

    // Load filter options, best practices, and logs on page load
    loadFilterOptions();
    loadBPs();
    loadLogs();

    // Add event listeners to filters
    phaseFilter.addEventListener('change', loadBPs); // Reload best practices when phase filter changes
    programFilter.addEventListener('change', loadBPs); // Reload best practices when program filter changes
    keywordFilter.addEventListener('keyup', loadBPs); // Reload best practices when keyword filter changes
});
