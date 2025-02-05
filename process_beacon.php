<?php
// Database connection settings
$DB_HOST = '';
$DB_USER = '';
$DB_PASSWORD = '';
$DB_NAME = '';

// Connect to the database
$connection = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME);

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Retrieve POST data
$minor = isset($_POST['minor']) ? intval($_POST['minor']) : null;
$timestamp = isset($_POST['timestamp']) ? floatval($_POST['timestamp']) : null;

if ($minor === null || $timestamp === null) {
    echo "Invalid data.";
    exit;
}

// Convert timestamp to a MySQL DATETIME format
$datetime = date('Y-m-d H:i:s', $timestamp);

// Check if the minor already exists in the database
$sql = "SELECT * FROM `scanner` WHERE `minor` = ? ORDER BY `addedOn` DESC LIMIT 1";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $minor);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Existing record found
    $row = $result->fetch_assoc();
    $last_time = strtotime($row['addedOn']);  // Convert the stored timestamp to UNIX time
    $time_difference = $timestamp - $last_time;

    if ($time_difference < 10) {
        // If the time difference is less than 10 seconds, update the existing record
        $update_sql = "UPDATE `scanner` SET `addedOn` = ? WHERE `rid` = ?";
        $update_stmt = $connection->prepare($update_sql);
        $update_stmt->bind_param("si", $datetime, $row['rid']);
        $update_stmt->execute();
        echo "Record updated for minor $minor.";
    } else {
        // If the time difference is 10 seconds or more, insert a new record
        $insert_sql = "INSERT INTO `scanner` (`minor`, `addedOn`) VALUES (?, ?)";
        $insert_stmt = $connection->prepare($insert_sql);
        $insert_stmt->bind_param("is", $minor, $datetime);
        $insert_stmt->execute();
        echo "New record added for minor $minor.";
    }
} else {
    // No existing record found, insert a new record
    $insert_sql = "INSERT INTO `scanner` (`minor`, `addedOn`) VALUES (?, ?)";
    $insert_stmt = $connection->prepare($insert_sql);
    $insert_stmt->bind_param("is", $minor, $datetime);
    $insert_stmt->execute();
    echo "New record added for minor $minor.";
}

$connection->close();
?>
