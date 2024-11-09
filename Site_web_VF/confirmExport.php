<?php
session_start();

if (!isset($_GET['file']) || empty($_GET['file'])) {
    die('No file specified.');
}

$file = $_GET['file'];
$format = $_GET['format'] ?? 'pdf';
$downloadPath = '/home/rt/Downloads/' . basename($file);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Export Confirmation</title>
    <link rel="stylesheet" href="confirmExport.css">
    <script type="text/javascript">
        window.onload = function() {
            var url = "<?php echo htmlspecialchars($file); ?>";
            var link = document.createElement('a');
            link.href = url;
            link.download = url.split('/').pop();
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        };

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
            if (isset($_SESSION['droit'])) {
                echo '<li><a href="createBP.php">Create a BP</a></li>';
                echo '<li><a href="view_users.php">Manage accounts</a></li>';
                if ($_SESSION['droit'] == 'administrateur' || $_SESSION['droit'] == 'super-administrateur') {
                    echo '<li><a href="manageDeactivatedBPs.php">Manage Deactivated BPs</a></li>';
                    echo '<li><a href="view_logs.php">Logs</a></li>';
                    echo '<li><a href="modifexigence.php">Modify Password Requirements</a></li>';
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
        <h1>Export Confirmation</h1>
        <p>The CSV file is being downloaded...</p>
        <form action="executePython.php" method="post">
            <input type="hidden" name="file" value="<?php echo htmlspecialchars($file); ?>">
            <input type="hidden" name="format" value="<?php echo htmlspecialchars($format); ?>">
            <input type="submit" value="Confirm Export">
        </form>
        <br>
        <a href="index.php" class="back-link">Back</a>
    </div>
</body>
</html>
