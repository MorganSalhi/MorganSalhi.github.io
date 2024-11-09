<?php
// Start the session
session_start();

// Initialize message variable
$message = "";

// Connect to the database
$server = "localhost";
$user = "root";
$password = "root";
$database = "thales11";
$connection = new mysqli($server, $user, $password, $database);

// Check the connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Prepare and execute the update query
    $sql = "UPDATE exigencesmdp SET nombre = ?, Majuscule = ?, Minuscule = ?, Caracteres_spe = ?, username = ? WHERE id = 1";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("sssss", $_POST['nombre'], $_POST['maj'], $_POST['min'], $_POST['special'], $_POST['username']);
    if ($stmt->execute()) {
        $message = "Requirements updated successfully!";
    } else {
        $message = "Failed to update requirements: " . $stmt->error;
    }
    $stmt->close();
}

// Retrieve the current requirements
$sql = "SELECT * FROM exigencesmdp WHERE id = 1";
$result = $connection->query($sql);
$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Password Requirements</title>
    <link rel="stylesheet" href="modifexigence.css">
    <script>
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
        <div class="user-info">
            <p><?php echo isset($_SESSION['login']) ? $_SESSION['login'] : 'Guest'; ?></p>
            <p><?php echo isset($_SESSION['droit']) ? $_SESSION['droit'] : 'None'; ?></p>
            <a href="logout.php" class="logout-button">Logout</a>
        </div>
    </header>
    <div class="container">
        <?php if (!empty($message)): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>
        <form method="post">
            <label for="nombre">Number (Digits):</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($row['nombre']); ?>">

            <label for="maj">Uppercase Letters:</label>
            <input type="text" id="maj" name="maj" value="<?php echo htmlspecialchars($row['Majuscule']); ?>">

            <label for="min">Lowercase Letters:</label>
            <input type="text" id="min" name="min" value="<?php echo htmlspecialchars($row['Minuscule']); ?>">

            <label for="special">Special Characters:</label>
            <input type="text" id="special" name="special" value="<?php echo htmlspecialchars($row['Caracteres_spe']); ?>">

            <label for="username">Exclude Username:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($row['username']); ?>">

            <input type="submit" value="Update Requirements">
        </form>
        <button onclick="window.location.href='index.php'" class="back-button">Back to Home</button>
    </div>
</body>
</html>

<?php
// Close the database connection
$connection->close();
?>
