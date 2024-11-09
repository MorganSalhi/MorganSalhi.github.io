<?php
// Start session
session_start();
require 'db_connection.php';
include 'logger.php';

// Log access
logAction('exportData.php', 'Page accessed');

// Get selected BP IDs and format
$bpIds = $_POST['bpIds'] ?? '';
$format = $_POST['format'] ?? 'pdf';

// Validate input
if (empty($bpIds)) {
    logAction('exportData.php', 'No best practices selected.');
    die("No best practices selected.");
}

$bpIdsArray = explode(',', $bpIds);
$placeholders = implode(',', array_fill(0, count($bpIdsArray), '?'));

// Fetch numAppart and numAssoc associated with selected BPs
$query = "SELECT DISTINCT appartenance.numAppart, association.numAssoc
          FROM appartenance
          LEFT JOIN association ON appartenance.BP = association.BP
          WHERE appartenance.BP IN ($placeholders)";
$stmt = $conn->prepare($query);
$stmt->bind_param(str_repeat('i', count($bpIdsArray)), ...$bpIdsArray);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

if (empty($data)) {
    logAction('exportData.php', 'No appart numbers or associations found.');
    die("No appart numbers or associations found.");
}

// Create CSV file
$csvFile = '/var/www/html/export_data.csv';
$handle = fopen($csvFile, 'w');
fputcsv($handle, ['numAppart', 'numAssoc']);
foreach ($data as $row) {
    fputcsv($handle, [$row['numAppart'], $row['numAssoc']]);
}
fclose($handle);

logAction('exportData.php', 'CSV file created: ' . $csvFile);

// Redirect to confirmation page
header("Location: confirmExport.php?file=" . urlencode($csvFile) . "&format=" . urlencode($format));
exit;
?>
