<?php
// Start the session
session_start();
// Include the logger
include 'logger.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the format and CSV file path from the form
    $format = $_POST['format']; // The format (pdf or excel) passed from confirmExport.php
    $csvFile = $_POST['file']; // The path to the CSV file

    // Full path to the Python executable
    $pythonPath = '/usr/bin/python3'; // Path found via which python3

    // Full path to your Python script
    $scriptPath = '/var/www/html/export_script.py'; // Ensure this path is correct

    // Build the command to call the Python script with the format and CSV file
    $command = escapeshellcmd("$pythonPath $scriptPath $format \"$csvFile\"") . " 2>&1";
    $output = shell_exec($command);

    // Log the execution of the Python script
    logAction('executePythonScript.php', "Command executed: $command");

    // Redirect to index.php immediately
    header("Location: index.php");
    exit;
} else {
    // Log the absence of specified action
    logAction('executePythonScript.php', 'No action specified');

    echo "No action specified.<br>";
    echo "<a href='confirmExport.php'>Back</a>";
}
?>
