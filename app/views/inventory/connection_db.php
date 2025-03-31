<?php
//Database configuration
// $dbHost     = "localhost";
// $dbUsername = "root";
// $dbPassword = " ";
// $dbName     = "nexaraso_project001";

// // Create database connection
// $db = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

// // Check connection
// if ($db->connect_error) {
//     die("Connection failed: " . $db->connect_error);
// }

$conn = new mysqli('localhost', 'root', '', database: 'nexaraso_risi_rasa');

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed.']));
}
