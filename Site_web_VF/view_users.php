<?php
session_start();
require 'db_connection.php';
include 'logger.php';

// Log access to the page
logAction('view_users.php', 'Page accessed');

// Check access rights
if (!isset($_SESSION['login'])) {
    echo "<p class='error'>Access denied. You must be logged in.</p>";
    logAction('view_users.php', 'Access denied');
    exit;
}

$role = $_SESSION['droit']; // Get the role of the logged-in user

// Fetch password requirements from the database
$reqResult = $conn->query("SELECT * FROM exigencesmdp WHERE id = 1");
$requirements = $reqResult->fetch_assoc();

// Retrieve all users from the database
$users = [];
$query = "SELECT login, nom, prenom, droit, bloque, tentative_login FROM utilisateurs";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        if ($role == 'utilisateur') {
            $row['droit'] = '*****'; // Mask role for non-admins
        }
        $users[] = $row;
    }
}

// Close the database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>View Users</title>
    <link rel="stylesheet" href="view_users.css">
    <script>
        // Function to show/hide password
        function showPassword(id) {
            var passwordInput = document.getElementById(id);
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
            } else {
                passwordInput.type = "password";
            }
        }

        // Function to validate password based on requirements
        function validatePassword(password, username) {
            var requirements = {
                majuscule: <?php echo $requirements['Majuscule']; ?>,
                minuscule: <?php echo $requirements['Minuscule']; ?>,
                caracteresSpe: <?php echo $requirements['Caracteres_spe']; ?>,
                nombre: <?php echo $requirements['nombre']; ?>,
                username: "<?php echo $requirements['username']; ?>"
            };

            var validations = {
                majuscule: new RegExp(`(?=(.*[A-Z]){${requirements.majuscule},})`),
                minuscule: new RegExp(`(?=(.*[a-z]){${requirements.minuscule},})`),
                caracteresSpe: new RegExp(`[!"#$%&'()*+,-./:;<=>?@[\\]^_\`{|}~]{${requirements.caracteresSpe},}`),
                nombre: new RegExp(`(?=(.*[0-9]){${requirements.nombre},})`),
                username: new RegExp(username)
            };

            var valid = true;

            // Validate each requirement
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

        // Handle password input change
        function handlePasswordChange(event, username) {
            var password = event.target.value;
            validatePassword(password, username);
        }

        // Open password modal
        function openPasswordModal(username) {
            document.getElementById('passwordModal').style.display = "block";
            document.getElementById('modalUsername').value = username;
        }

        // Close password modal
        function closePasswordModal() {
            document.getElementById('passwordModal').style.display = "none";
        }

        // Handle password form submission
        function handlePasswordSubmit(event) {
            event.preventDefault();
            var username = document.getElementById('modalUsername').value;
            var newPassword = document.getElementById('newPassword').value;
            var confirmPassword = document.getElementById('confirmPassword').value;

            if (newPassword !== confirmPassword) {
                alert('Passwords do not match!');
                return;
            }

            if (!validatePassword(newPassword, username)) {
                alert('Password does not meet requirements!');
                return;
            }

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "change_password.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var response = JSON.parse(xhr.responseText);
                    alert(response.message);
                    if (response.success) {
                        closePasswordModal();
                        location.reload();
                    }
                }
            };
            xhr.send("username=" + username + "&newPassword=" + newPassword);
        }

        // Open role modal
        function openRoleModal(username, currentRole) {
            document.getElementById('roleModal').style.display = "block";
            document.getElementById('roleModalUsername').value = username;
            document.getElementById('currentRole').textContent = currentRole;
        }

        // Close role modal
        function closeRoleModal() {
            document.getElementById('roleModal').style.display = "none";
        }

        // Handle role form submission
        function handleRoleSubmit(event) {
            event.preventDefault();
            var username = document.getElementById('roleModalUsername').value;
            var newRole = document.getElementById('newRole').value;

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "modify_role.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var response = JSON.parse(xhr.responseText);
                    alert(response.message);
                    if (response.success) {
                        closeRoleModal();
                        location.reload();
                    }
                }
            };
            xhr.send("username=" + username + "&newRole=" + newRole);
        }

        // Delete user
        function deleteUser(username) {
            if (confirm("Are you sure you want to delete this account?")) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "delete_account.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        var response = JSON.parse(xhr.responseText);
                        alert(response.message);
                        if (response.success) {
                            location.reload();
                        }
                    }
                };
                xhr.send("username=" + username);
            }
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
            xhr.send("page=view_users.php&action=Back button clicked");
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
                <p><?php echo ($_SESSION['droit'] == 'utilisateur') ? '*****' : $_SESSION['droit']; ?></p>
                <a href="logout.php" class="logout-button">Logout</a>
            </div>
        <?php endif; ?>
    </header>
    <div class="container">
        <h1>User Management</h1>
        <table>
            <thead>
                <tr>
                    <th>Login</th>
                    <th>Name</th>
                    <th>First Name</th>
                    <th>Role</th>
                    <th>Blocked</th>
                    <th>Login Attempts</th>
                    <?php if ($role != 'utilisateur') echo '<th>Actions</th>'; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['login']); ?></td>
                        <td><?php echo htmlspecialchars($user['nom']); ?></td>
                        <td><?php echo htmlspecialchars($user['prenom']); ?></td>
                        <td><?php echo htmlspecialchars($user['droit']); ?></td>
                        <td><?php echo htmlspecialchars($user['bloque']); ?></td>
                        <td><?php echo htmlspecialchars($user['tentative_login']); ?></td>
                        <?php if ($role != 'utilisateur' && $user['droit'] != 'super-administrateur'): ?>
                            <td class="button-container">
                                <button onclick="openPasswordModal('<?php echo $user['login']; ?>')">Modify Password</button>
                                <?php if ($role == 'super-administrateur'): ?>
                                    <button onclick="openRoleModal('<?php echo $user['login']; ?>', '<?php echo $user['droit']; ?>')">Modify Role</button>
                                    <button onclick="deleteUser('<?php echo $user['login']; ?>')">Delete Account</button>
                                <?php endif; ?>
                            </td>
                        <?php elseif ($role == 'utilisateur' && $user['login'] == $_SESSION['login']): ?>
                            <td class="button-container">
                                <button onclick="openPasswordModal('<?php echo $user['login']; ?>')">Modify Password</button>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if ($_SESSION['droit'] == 'administrateur' || $_SESSION['droit'] == 'super-administrateur'): ?>
            <button onclick="window.location.href='createAccount.php'">Create Account</button>
        <?php endif; ?>
        <button onclick="logAndRedirect()" class="back-button">Back to Home</button>
    </div>

    <!-- Password Modal -->
    <div id="passwordModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closePasswordModal()">&times;</span>
            <h2>Change Password</h2>
            <form onsubmit="handlePasswordSubmit(event)">
                <input type="hidden" id="modalUsername" name="username">
                <div>
                    <label for="newPassword">New Password:</label>
                    <input type="password" id="newPassword" name="newPassword" oninput="handlePasswordChange(event, document.getElementById('modalUsername').value)" required>
                    <span onclick="showPassword('newPassword')">👁️</span>
                </div>
                <div>
                    <label for="confirmPassword">Confirm Password:</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                    <span onclick="showPassword('confirmPassword')">👁️</span>
                </div>
                <ul class="password-requirements">
                    <li id="majuscule" class="invalid">At least <?php echo $requirements['Majuscule']; ?> uppercase letter(s)</li>
                    <li id="minuscule" class="invalid">At least <?php echo $requirements['Minuscule']; ?> lowercase letter(s)</li>
                    <li id="caracteresSpe" class="invalid">At least <?php echo $requirements['Caracteres_spe']; ?> special character(s)</li>
                    <li id="nombre" class="invalid">At least <?php echo $requirements['nombre']; ?> number(s)</li>
                    <li id="username" class="invalid">Cannot contain the username</li>
                </ul>
                <button type="submit">Change Password</button>
                <button type="button" onclick="closePasswordModal()">Cancel</button>
            </form>
        </div>
    </div>

    <!-- Role Modal -->
    <div id="roleModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeRoleModal()">&times;</span>
            <h2>Change User Role</h2>
            <form onsubmit="handleRoleSubmit(event)">
                <input type="hidden" id="roleModalUsername" name="username">
                <p>Current Role: <span id="currentRole"></span></p>
                <label for="newRole">New Role:</label>
                <select id="newRole" name="newRole">
                    <option value="utilisateur">User</option>
                    <option value="administrateur">Administrator</option>
                </select>
                <button type="submit">Change Role</button>
                <button type="button" onclick="closeRoleModal()">Cancel</button>
            </form>
        </div>
    </div>
</body>
</html>
