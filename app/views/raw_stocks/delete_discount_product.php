<?php
require 'connection_db.php';

// Get barcode from POST request
$barcode = $_POST['barcode'] ?? '';
$promo_type = 'discount';

if (empty($barcode)) {
    echo json_encode(["success" => false, "message" => "Invalid barcode"]);
    exit;
}

// Delete from the database
$stmt = $conn->prepare("DELETE FROM promotions WHERE product_barcode = ? AND promo_type = ? ");
$stmt->bind_param("ss", $barcode, $promo_type);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Database error: " . $stmt->error]);
}

// Close connections
$stmt->close();
$conn->close();
?>
