<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "crosscountry";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get counts and first timestamp for each minor
$sql = "SELECT 
            minor, 
            COUNT(*) as occurrence,
            MIN(timestamp) as first_timestamp 
        FROM beacon 
        GROUP BY minor";
$result = $conn->query($sql);

$players = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $players[] = [
            'minor' => $row["minor"],
            'count' => $row["occurrence"],
            'first_timestamp' => $row["first_timestamp"]
        ];
    }
}

// Get ALL scanned beacons in reverse chronological order
$sql_recent = "SELECT minor FROM beacon ORDER BY timestamp DESC";
$result_recent = $conn->query($sql_recent);
$recent_beacons = [];
if ($result_recent->num_rows > 0) {
    while($row = $result_recent->fetch_assoc()) {
        $recent_beacons[] = $row["minor"];
    }
}

// Return player data, latest beacon, and all recent beacons
$response = [
    "players" => $players,
    "latest" => $recent_beacons[0] ?? null,
    "recent_beacons" => $recent_beacons
];

echo json_encode($response);

$conn->close();
?>