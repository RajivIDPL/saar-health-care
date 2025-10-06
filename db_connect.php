<?php
// No whitespace or blank lines before this tag
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

$servername = "srv1824.hstgr.io"; 
$username = "u230556943_saar_health";
$password = "A;SlDkFj@123";
$dbname = "u230556943_saar_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]);
    exit; // Stop further execution
}

// SUCCESS: No echo here! Just continue with your script.