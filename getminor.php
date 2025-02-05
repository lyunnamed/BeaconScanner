<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "";
$username = "";
$password = "";
$dbname = "";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get all minors and their occurrences for the lap count
$sql = "SELECT minor, COUNT(*) as occurrence FROM scanner GROUP BY minor";
$result = $conn->query($sql);

$data = [
    'counts' => [],
    'scans' => []
];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $data['counts'][$row["minor"]] = $row["occurrence"];
    }
}

// Get all scans in chronological order for the scanned section
$sql_scans = "SELECT minor FROM scanner ORDER BY addedOn DESC";
$result_scans = $conn->query($sql_scans);

if ($result_scans->num_rows > 0) {
    while($row = $result_scans->fetch_assoc()) {
        $data['scans'][] = $row["minor"];
    }
}

echo json_encode($data);

$conn->close();
?>
