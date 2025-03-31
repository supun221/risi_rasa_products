<?php
// Database connection
require 'connection_db.php';

// Get item code from request
if (isset($_GET['item_code'])) {
    $item_code = $_GET['item_code'];
    $stmt = $conn->prepare("SELECT COUNT(*) FROM raw_items WHERE item_code = ?");
    $stmt->bind_param("s", $item_code);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    
    // Return response in JSON format
    echo json_encode(["exists" => $count > 0]);
}

$conn->close();
?>
