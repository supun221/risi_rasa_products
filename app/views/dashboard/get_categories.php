<?php
require_once '../../../config/databade.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "SELECT DISTINCT category FROM products";
$result = $conn->query($query);
$categories = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}

header('Content-Type: application/json');
echo json_encode($categories);

$conn->close();
?>