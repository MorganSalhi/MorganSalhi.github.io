<?php
require 'db_connection.php';

$bpId = $_POST['bpId'];

// Delete existing associations
$conn->query("DELETE FROM appartenance WHERE BP = $bpId");
$conn->query("DELETE FROM association WHERE BP = $bpId");

// Insert new phases
if (!empty($_POST['phases'])) {
    foreach ($_POST['phases'] as $phaseId) {
        $conn->query("INSERT INTO appartenance (BP, Phases) VALUES ($bpId, $phaseId)");
    }
}

// Insert new programs
if (!empty($_POST['programs'])) {
    foreach ($_POST['programs'] as $programId) {
        $conn->query("INSERT INTO appartenance (BP, Programme) VALUES ($bpId, $programId)");
    }
}

// Insert new keywords
if (!empty($_POST['keywords'])) {
    foreach ($_POST['keywords'] as $keywordId) {
        $conn->query("INSERT INTO association (BP, numMC) VALUES ($bpId, $keywordId)");
    }
}

echo json_encode(['success' => true]);

$conn->close();
?>
