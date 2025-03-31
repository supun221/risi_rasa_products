<?php
require_once '../../../models/Database.php';

$barcode = $_POST['barcode'] ?? '';

if (empty($barcode)) {
    echo json_encode(["status" => "error", "message" => "No barcode provided"]);
    exit;
}

$stmt = $db_conn->prepare("SELECT product_name FROM products WHERE item_code = ?");
$stmt->bind_param("s", $barcode);
$stmt->execute();
$result = $stmt->get_result();

$productNames = [];
while ($row = $result->fetch_assoc()) {
    $productNames[] = $row['product_name'];
}

$stmt->close();
$db_conn->close();

echo json_encode(["status" => "success", "data" => $productNames]);
?>
