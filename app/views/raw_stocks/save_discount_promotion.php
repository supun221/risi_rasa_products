<?php
require 'connection_db.php';

// Get POST data
$product_barcode = $_POST['product_barcode'];
$buy_quantity = $_POST['buy_quantity'];
$discount_type = $_POST['discountType'];
$discount_percentage = ($discount_type === 'percentage') ? $_POST['discount_percentage'] : NULL;
$discount_value = ($discount_type === 'value') ? $_POST['discount_value'] : NULL;
$promo_type = "discount"; // Default promo type

$response = [];

// Insert data into promotions table
$sql = "INSERT INTO promotions (product_barcode, buy_quantity, discount_percentage, discount_amount, promo_type) 
        VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sidds", $product_barcode, $buy_quantity, $discount_percentage, $discount_value, $promo_type);

if ($stmt->execute()) {
    $response["success"] = true;
    $response["message"] = "Promotion saved successfully!";
} else {
    $response["success"] = false;
    $response["message"] = "Error: " . $conn->error;
}

echo json_encode($response);
$stmt->close();
$conn->close();
?>
