<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = ""; // Use the appropriate password for your MySQL
$dbname = "crosscountry";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Clear all records from the beacon table
$sql = "DELETE FROM beacon";

if ($conn->query($sql) === TRUE) {
    // Reset auto-increment after clearing
    $sql = "ALTER TABLE beacon AUTO_INCREMENT = 1";
    $conn->query($sql);
    echo "All data cleared successfully";
} else {
    echo "Error clearing data: " . $conn->error . " (SQL: $sql)";
}

$conn->close();
?>
