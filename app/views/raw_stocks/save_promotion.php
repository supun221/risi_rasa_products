<?php
require 'connection_db.php';

// Get form data
$barcode = $_POST['barcode'] ?? '';
$buy_amount = $_POST['buy_amount'] ?? 0;
$free_amount = $_POST['free_amount'] ?? 0;
$promo_type= 'item';

if (empty($barcode) || $buy_amount <= 0 || $free_amount < 0) {
    echo json_encode(["success" => false, "message" => "Invalid input values"]);
    exit;
}

// Prepare and execute the SQL query
$stmt = $conn->prepare("INSERT INTO promotions (product_barcode, buy_quantity, free_quantity, promo_type) VALUES (?, ?, ?, ?)");
$stmt->bind_param("siis", $barcode, $buy_amount, $free_amount, $promo_type);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Database error: " . $stmt->error]);
}

// Close connections
$stmt->close();
$conn->close();
?>
