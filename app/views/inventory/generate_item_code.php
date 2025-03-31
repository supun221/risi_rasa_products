<?php
require 'connection_db.php';

// Query to get the maximum item_code from the products table
$result = $conn->query("SELECT MAX(item_code) AS max_code FROM products");

// Fetch the result
$row = $result->fetch_assoc();

// Determine the next item_code
if (!$row['max_code'] || $row['max_code'] < 100001) {
    $itemCode = 100001; // Default to 100001 if max_code is less than 100001 or no codes exist
} else {
    $itemCode = $row['max_code'] + 1; // Increment the max_code by 1
}

echo json_encode(['itemCode' => $itemCode]);
?>
