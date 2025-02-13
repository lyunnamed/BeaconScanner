<?php
// Database connection settings
$DB_HOST = 'localhost';
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

try {
    // Start transaction
    $connection->begin_transaction();

    // Try to insert a new record if the last record is older than 10 seconds
    $insert_sql = "INSERT INTO scanner (minor)
                   SELECT ?
                   WHERE (
                       SELECT TIMESTAMPDIFF(SECOND, MAX(addedOn), ?) > 10
                       FROM scanner
                       WHERE minor = ?
                   ) OR NOT EXISTS (
                       SELECT 1
                       FROM scanner
                       WHERE minor = ?
                   )";
    
    $insert_stmt = $connection->prepare($insert_sql);
    $insert_stmt->bind_param("isis", $minor, $datetime, $minor, $minor);
    $insert_stmt->execute();
    
    if ($insert_stmt->affected_rows == 0) {
        // No new record was inserted, update the latest record's timestamp
        $update_sql = "UPDATE scanner 
                      SET addedOn = ?
                      WHERE rid = (
                          SELECT rid FROM (
                              SELECT rid
                              FROM scanner
                              WHERE minor = ?
                              ORDER BY addedOn DESC
                              LIMIT 1
                          ) as latest
                      )";
        
        $update_stmt = $connection->prepare($update_sql);
        $update_stmt->bind_param("si", $datetime, $minor);
        $update_stmt->execute();
        echo "Updated timestamp for minor $minor.";
    } else {
        echo "New record added for minor $minor.";
    }

    // Commit transaction
    $connection->commit();

} catch (Exception $e) {
    // Rollback on error
    $connection->rollback();
    echo "Error: " . $e->getMessage();
} finally {
    $connection->close();
}
?>
