<?php
require 'db_connection.php';

$bpId = $_GET['bpId'];

// Fetch phases
$phases = [];
$phaseQuery = "SELECT p.numPhases AS id, p.nomPhase AS name, 
                      IF(a.BP IS NOT NULL, 1, 0) AS assigned
               FROM phases p
               LEFT JOIN appartenance a ON p.numPhases = a.Phases AND a.BP = ?";
$stmt = $conn->prepare($phaseQuery);
$stmt->bind_param("i", $bpId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $phases[] = $row;
}
$stmt->close();

// Fetch programs
$programs = [];
$programQuery = "SELECT p.numProg AS id, p.nomProgramme AS name, 
                        IF(a.BP IS NOT NULL, 1, 0) AS assigned
                 FROM programmes p
                 LEFT JOIN appartenance a ON p.numProg = a.Programme AND a.BP = ?";
$stmt = $conn->prepare($programQuery);
$stmt->bind_param("i", $bpId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $programs[] = $row;
}
$stmt->close();

// Fetch keywords
$keywords = [];
$keywordQuery = "SELECT k.numMC AS id, k.nomMotsCles AS name, 
                        IF(a.BP IS NOT NULL, 1, 0) AS assigned
                 FROM motscles k
                 LEFT JOIN association a ON k.numMC = a.numMC AND a.BP = ?";
$stmt = $conn->prepare($keywordQuery);
$stmt->bind_param("i", $bpId);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $keywords[] = $row;
}
$stmt->close();

$response = [
    'phases' => $phases,
    'programs' => $programs,
    'keywords' => $keywords,
];

echo json_encode($response);

$conn->close();
?>
