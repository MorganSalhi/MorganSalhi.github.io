<?php
// Include the database connection and logger files
require 'db_connection.php';
include 'logger.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the page and action from the POST request
    $page = $_POST['page'] ?? '';
    $action = $_POST['action'] ?? '';

    // Check if both page and action are not empty
    if (!empty($page) && !empty($action)) {
        // Log the action
        logAction($page, $action);
    } 
}
?>
