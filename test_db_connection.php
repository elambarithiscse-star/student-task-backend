<?php
// Test script to verify database connection
// Run with: c:\xampp\php\php.exe test_db_connection.php

require_once 'config/database.php';

echo "Testing connection...\n";

$database = new Database();
// This will either return a connection or exit with JSON error
$db = $database->getConnection();

if ($db) {
    echo json_encode(["success" => true, "message" => "Connection successful"]);
}
?>
