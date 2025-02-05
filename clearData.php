<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "";
$username = "";
$password = "";
$dbname = "";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to delete all rows from the scanner table
$sql = "TRUNCATE TABLE scanner";

if ($conn->query($sql) === TRUE) {
    echo "All data cleared successfully";
} else {
    // Provide detailed error message
    echo "Error clearing data: " . $conn->error;
}

$conn->close();
?>
