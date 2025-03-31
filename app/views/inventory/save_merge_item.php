<?php
require 'connection_db.php';

$data = json_decode(file_get_contents("php://input"), true);

$itemCode = $data['itemCode'];
$productName = $data['productName'];
$category = $data['category'];
$motherDes = $data['motherDes'];
$childDes = $data['childDes'];

$stmt = $conn->prepare("INSERT INTO products (item_code, product_name, sinhala_name, category, mother_item_des, child_item_des) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssss", $itemCode, $productName,$productName, $category, $motherDes, $childDes);

if ($stmt->execute()) {
    echo json_encode(['message' => 'Merge Item saved successfully!', 'productName' => $productName, 'itemCode' => $itemCode]);
} else {
    echo json_encode(['message' => 'Error saving item.']);
}
?>
