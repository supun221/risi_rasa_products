<?php
require 'connection_db.php';

header('Content-Type: application/json');

// Fetch categories from database
$sql = "SELECT unit_name FROM unit";
$result = $conn->query($sql);

$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

echo json_encode($categories);
?>

