<?php
// Start a session to track user authentication and access rights
session_start();

// Include database connection and logger files
require 'db_connection.php';
include 'logger.php';

// Log access to the page
logAction('createAccount.php', 'Page accessed');

// Check if the user has super-administrator rights
if (!isset($_SESSION['login']) || $_SESSION['droit'] != 'super-administrateur') {
    echo "<p class='error'>Access denied. You must be logged in as a super-administrator.</p>";
    logAction('createAccount.php', 'Access denied');
    exit;
}

// Fetch password requirements from the database
$reqResult = $conn->query("SELECT * FROM exigencesmdp WHERE id = 1");
$requirements = $reqResult->fetch_assoc();

// Initialize an empty error message
$errorMessage = '';

// Check if the request method is POST (form submission)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];
    $prenom = $_POST['prenom'];
    $nom = $_POST['nom'];

    // Check username requirements
    $usernameError = false;
    if (!preg_match('/^[a-zA-Z0-9]{8,30}$/', $username) || preg_match('/[àâäéèêëïîôöùûüÿç]/', $username)) {
        $usernameError = true;
    } else {
        // Check if username is unique
        $stmt = $conn->prepare("SELECT COUNT(*) FROM utilisateurs WHERE login = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        if ($count > 0) {
            $usernameError = true;
        }
    }

    // Check password requirements
    if ($usernameError ||
        !preg_match('/[0-9]{' . $requirements['nombre'] . ',}/', $password) ||
        !preg_match('/[a-z]{' . $requirements['Minuscule'] . ',}/', $password) ||
        !preg_match('/[A-Z]{' . $requirements['Majuscule'] . ',}/', $password) ||
        !preg_match('/[!"#$%&\'()*+,-.\/:;<=>?@[\\]^_\`{|}~]{' . $requirements['Caracteres_spe'] . ',}/', $password) ||
        ($requirements['username'] == 'non' && strpos($password, $username) !== false)) {
        $errorMessage = "Username or password does not meet the security requirements.";
        logAction('createAccount.php', 'Validation failed for user: ' . $username);
    } else {
        // Hash the password for secure storage
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Prepare an SQL statement to insert the new user
        $stmt = $conn->prepare("INSERT INTO utilisateurs (login, mdp, droit, prenom, nom, bloque, tentative_login, statut) VALUES (?, ?, ?, ?, ?, 0, 0, 'inactif')");
        $stmt->bind_param("sssss", $username, $hashedPassword, $role, $prenom, $nom);
        if ($stmt->execute()) {
            $errorMessage = "Account successfully created.";
            logAction('createAccount.php', 'Account created for user: ' . $username);
        } else {
            $errorMessage = "Error creating account: " . $stmt->error;
            logAction('createAccount.php', 'Account creation error for user: ' . $username . ', Error: ' . $stmt->error);
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Administrator and User Account</title>
    <link rel="stylesheet" href="createAccount.css">
    <script>
        // Log action and redirect to the index page
        function logAndRedirect() {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "logger_ajax.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    window.location.href = 'index.php';
                }
            };
            xhr.send("page=createAccount.php&action=Back button clicked");
        }

        // Validate the password based on requirements
        function validatePassword(password, username) {
            var requirements = {
                majuscule: <?php echo $requirements['Majuscule']; ?>,
                minuscule: <?php echo $requirements['Minuscule']; ?>,
                caracteresSpe: <?php echo $requirements['Caracteres_spe']; ?>,
                nombre: <?php echo $requirements['nombre']; ?>,
                username: "<?php echo $requirements['username']; ?>"
            };

            var validations = {
                majuscule: new RegExp(`[A-Z]{${requirements.majuscule},}`),
                minuscule: new RegExp(`[a-z]{${requirements.minuscule},}`),
                caracteresSpe: new RegExp(`[!"#$%&'()*+,-./:;<=>?@[\\]^_\`{|}~]{${requirements.caracteresSpe},}`),
                nombre: new RegExp(`[0-9]{${requirements.nombre},}`),
                username: new RegExp(username, 'i')
            };

            var valid = true;

            for (var key in validations) {
                if (key !== "username") {
                    if (validations[key].test(password)) {
                        document.getElementById(key).classList.add('valid');
                        document.getElementById(key).classList.remove('invalid');
                    } else {
                        document.getElementById(key).classList.add('invalid');
                        document.getElementById(key).classList.remove('valid');
                        valid = false;
                    }
                } else {
                    if (requirements.username === "non" && !validations[key].test(password)) {
                        document.getElementById(key).classList.add('valid');
                        document.getElementById(key).classList.remove('invalid');
                    } else {
                        document.getElementById(key).classList.add('invalid');
                        document.getElementById(key).classList.remove('valid');
                        valid = false;
                    }
                }
            }

            return valid;
        }

        // Validate the username based on requirements
        function validateUsername(username) {
            var usernameValid = true;

            // Check length and characters
            var lengthRegex = /^[a-zA-Z0-9]{8,30}$/;
            if (lengthRegex.test(username)) {
                document.getElementById('length').classList.add('valid');
                document.getElementById('length').classList.remove('invalid');
            } else {
                document.getElementById('length').classList.add('invalid');
                document.getElementById('length').classList.remove('valid');
                usernameValid = false;
            }

            // Check accents
            var accentsRegex = /[àâäéèêëïîôöùûüÿç]/;
            if (accentsRegex.test(username)) {
                document.getElementById('accents').classList.add('invalid');
                document.getElementById('accents').classList.remove('valid');
                usernameValid = false;
            } else {
                document.getElementById('accents').classList.add('valid');
                document.getElementById('accents').classList.remove('invalid');
            }

            // Check uniqueness via AJAX call
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "check_username.php", false); // Using synchronous request to immediately update validity status
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send("username=" + username);
            var response = JSON.parse(xhr.responseText);
            if (response.exists) {
                document.getElementById('unique').classList.add('invalid');
                document.getElementById('unique').classList.remove('valid');
                usernameValid = false;
            } else {
                document.getElementById('unique').classList.add('valid');
                document.getElementById('unique').classList.remove('invalid');
            }

            return usernameValid;
        }

        // Handle password input change
        function handlePasswordChange(event) {
            var password = event.target.value;
            var username = document.getElementById('username').value;
            validatePassword(password, username);
        }

        // Handle username input change
        function handleUsernameChange(event) {
            var username = event.target.value;
            validateUsername(username);
        }

        // Show or hide password
        function showPassword(id) {
            var passwordInput = document.getElementById(id);
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
            } else {
                passwordInput.type = "password";
            }
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
        <div class="user-info">
            <p><?php echo isset($_SESSION['login']) ? $_SESSION['login'] : 'Guest'; ?></p>
            <p><?php echo isset($_SESSION['droit']) ? $_SESSION['droit'] : 'None'; ?></p>
            <a href="logout.php" class="logout-button">Logout</a>
        </div>
    </header>
    <div class="main-content">
        <div class="requirements-container">
            <div class="password-requirements">
                <p><strong>Password Requirements:</strong></p>
                <ul>
                    <li id="majuscule" class="invalid">At least <?php echo $requirements['Majuscule']; ?> uppercase letter(s)</li>
                    <li id="minuscule" class="invalid">At least <?php echo $requirements['Minuscule']; ?> lowercase letter(s)</li>
                    <li id="caracteresSpe" class="invalid">At least <?php echo $requirements['Caracteres_spe']; ?> special character(s)</li>
                    <li id="nombre" class="invalid">At least <?php echo $requirements['nombre']; ?> number(s)</li>
                    <li id="username" class="invalid">Cannot contain the username</li>
                </ul>
            </div>
        </div>
        <div class="form-container">
            <h1>Create a New Account</h1>
            <form action="" method="post">
                <label for="prenom">First Name:</label>
                <input type="text" id="prenom" name="prenom" required>

                <label for="nom">Last Name:</label>
                <input type="text" id="nom" name="nom" required>

                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required oninput="handleUsernameChange(event)">

                <label for="password">Password:</label>
                <div class="password-container">
                    <input type="password" id="password" name="password" required oninput="handlePasswordChange(event)">
                    <span class="toggle-password" onclick="showPassword('password')">👁️</span>
                </div>

                <label for="role">Role:</label>
                <select id="role" name="role">
                    <option value="utilisateur">User</option>
                    <option value="administrateur">Administrator</option>
                </select>

                <button type="submit">Create Account</button>
                <?php if ($errorMessage): ?>
                <p class="<?php echo strpos($errorMessage, 'successfully') !== false ? 'success' : 'error'; ?>">
                    <?php echo $errorMessage; ?>
                </p>
                <?php endif; ?>
            </form>
        </div>
        <div class="requirements-container">
            <div class="username-requirements">
                <p><strong>Username Requirements:</strong></p>
                <ul>
                    <li id="length" class="invalid">Between 8 and 30 characters composed of letters and digits only</li>
                    <li id="accents" class="invalid">No accents</li>
                    <li id="unique" class="invalid">Must be unique</li>
                </ul>
            </div>
        </div>
    </div>
    <button onclick="logAndRedirect()" class="back-button">Back to Home</button>
</body>
</html>
