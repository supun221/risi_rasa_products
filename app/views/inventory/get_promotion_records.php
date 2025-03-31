<?php
require 'connection_db.php';

// Get barcode from request
$barcode = isset($_GET['barcode']) ? $_GET['barcode'] : '';
$promo_type = 'item';

// Fetch all promotions for the selected barcode
$sql = "SELECT id, product_barcode, buy_quantity, free_quantity FROM promotions WHERE product_barcode = ? AND promo_type = ? ";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $barcode, $promo_type);
$stmt->execute();
$result = $stmt->get_result();

$promotions = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $promotions[] = $row;
    }
}

echo json_encode($promotions);

$conn->close();
?>
