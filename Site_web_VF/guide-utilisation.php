<?php
// Start the session
session_start();

// Include database connection and logger functions
require 'db_connection.php';
include 'logger.php';

// Log the access to the page
logAction('guide-utilisation.php', 'Page accessed');

// Check user access rights
if (!isset($_SESSION['login']) || !isset($_SESSION['droit'])) {
    $message = "Access denied. You must be logged in to access this page.";
    logAction('guide-utilisation.php', 'Access denied - user not logged in');
    exit;
}

// Get the user role from the session
$role = $_SESSION['droit'];
$message = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Guide</title>
    <link rel="stylesheet" href="guide.css">
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
            xhr.send("page=guide-utilisation.php&action=Back button clicked");
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
        <h1>User Guide</h1>
        <section id="user">
            <h2>User Section</h2>
            <p>This section describes the features accessible to users.</p>
            <ul>
                <li><strong>Selecting Programs:</strong> Users can select a specific program to view all associated best practices. The program selection helps filter the best practices relevant to the selected program.</li>
                <li><strong>Selecting Phases:</strong> Users can select a specific phase within a program to see all best practices linked to that phase.</li>
                <li><strong>Searching by Keywords:</strong> The application allows users to search for best practices using specific keywords. Users can enter keywords to list corresponding best practices.</li>
                <li><strong>Excluding Programs and Phases:</strong> Users can exclude one or more programs or phases to refine the list of displayed best practices. This feature helps in narrowing down the practices to the most relevant ones for the user's current focus.</li>
                <li><strong>Deactivating Best Practices:</strong> Users can deactivate best practices that are no longer applicable or needed. Deactivated best practices can be reactivated by administrators.</li>
                <li><strong>Changing Password:</strong> Users can change their own passwords for their accounts to ensure security and privacy.</li>
            </ul>
        </section>
        
        <?php if ($role == 'administrateur' || $role == 'super-administrateur'): ?>
            <section id="admin">
                <h2>Admin Section</h2>
                <p>This section describes the features specific to administrators, including user management.</p>
                <ul>
                    <li><strong>User Account Management:</strong> Administrators can create, modify, and delete user accounts. They have the authority to reset passwords for users whose accounts are locked. Administrators can also change their own passwords.</li>
                    <li><strong>Program and Keyword Management:</strong> Administrators can create, modify, and delete programs and keywords within the application. They are responsible for maintaining the structure and organization of programs and keywords.</li>
                    <li><strong>Viewing Logs and User Activity:</strong> Administrators can access the history of user interactions and actions taken within the application. This feature is crucial for monitoring and auditing user activities to ensure compliance and security.</li>
                    <li><strong>Setting Password Requirements:</strong> Administrators can configure password policies, including the number of characters, use of uppercase and lowercase letters, and special characters.</li>
                    <li><strong>Managing Deactivated Best Practices:</strong> Administrators can view deactivated best practices and either reactivate or permanently delete them. This helps in maintaining the relevancy and accuracy of the best practices list.</li>
                </ul>
            </section>
        <?php endif; ?>
        
        <?php if ($role == 'super-administrateur'): ?>
            <section id="superadmin">
                <h2>Super-Admin Section</h2>
                <p>This section covers the exclusive features for super-administrators, such as advanced system settings and access rights management.</p>
                <ul>
                    <li><strong>Creating Administrator Accounts:</strong> Super-administrators can create, modify, and delete administrator accounts. They oversee the administrative roles and ensure that administrators have the necessary permissions to perform their duties.</li>
                    <li><strong>Changing All Passwords:</strong> Super-administrators can change passwords for all accounts within the system.</li>
                </ul>
            </section>
        <?php endif; ?>
    </div>
    <button onclick="logAndRedirect()" class="back-button">Back</button>
</body>
</html>
