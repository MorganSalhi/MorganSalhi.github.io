<?php
session_start(); // Start the session

// Collect excluded programs, phases, and keywords from the POST request
$excludePrograms = isset($_POST['excludePrograms']) ? $_POST['excludePrograms'] : [];
$excludePhases = isset($_POST['excludePhases']) ? $_POST['excludePhases'] : [];
$excludeKeywords = isset($_POST['excludeKeywords']) ? $_POST['excludeKeywords'] : [];

// Store the exclusions in the session
$_SESSION['excludePrograms'] = $excludePrograms;
$_SESSION['excludePhases'] = $excludePhases;
$_SESSION['excludeKeywords'] = $excludeKeywords;

// Redirect to the main page
header('Location: index.php');
exit();
?>
