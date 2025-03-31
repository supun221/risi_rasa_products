<?php
require 'connection_db.php'; 

// Retrieve POST data
$barcode = $_POST['barcode'];
$stockId = $_POST['stockId'];

if (!empty($barcode) && !empty($stockId)) {
    // Update barcode in the stock_entries table
    $stmt = $conn->prepare("UPDATE stock_entries SET barcode = ? WHERE stock_id = ?");
    $stmt->bind_param("si", $barcode, $stockId);

    if ($stmt->execute()) {
        echo "Barcode updated successfully.";
    } else {
        echo "Error updating barcode: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Barcode and stock ID cannot be empty.";
}

$conn->close();
?>
