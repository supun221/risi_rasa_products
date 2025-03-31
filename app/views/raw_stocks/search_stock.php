<?php
require 'connection_db.php';

// Read POST data
$data = json_decode(file_get_contents("php://input"), true);
$supplierId = $data['supplierId'];
$itemCode = $data['itemCode'];

// Query to search stock
$query = $conn->prepare("
    SELECT * FROM stock_entries 
    WHERE supplier_id = ? AND itemcode = ?
");
$query->bind_param("ii", $supplierId, $itemCode);
$query->execute();

$result = $query->get_result();
$stocks = [];

while ($row = $result->fetch_assoc()) {
    $stocks[] = [
        "stockId" => $row["stock_id"],
        "itemCode" => $row["itemcode"],
        "costPrice" => $row["cost_price"],
        "wholesalePrice" => $row["wholesale_price"],
        "maximumRetailPrice" => $row["max_retail_price"],
        "superCustomerPrice" => $row["super_customer_price"],
        "ourPrice" => $row["our_price"],
        "qty" => $row["purchase_qty"],
        "availableStock" => $row["available_stock"],
        "discount" => $row["discount_percent"],
        // "discountValue" => $row["discount_value"],
        // "totalAmount" => $row["total_amount"],
    ];
}

if (empty($stocks)) {
    http_response_code(200);
    echo json_encode([]);
} else {
    http_response_code(200);
    echo json_encode($stocks);
}

$query->close();
$conn->close();
?>
