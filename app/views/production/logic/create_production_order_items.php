<?php
require_once '../../../models/Database.php';

$production_order_id = $_POST['production_order_id'] ?? '';
$item_name = $_POST['item_name'] ?? '';
$barcode = $_POST['barcode'] ?? '';
$quantity = intval($_POST['quantity'] ?? 0);
$unit_weight = floatval($_POST['unit_weight'] ?? 0);
$price = floatval($_POST['price'] ?? 0);
$total_weight = floatval($_POST['total_weight'] ?? 0);

if (empty($production_order_id) || empty($item_name)) {
    echo json_encode(["status" => "error", "message" => "Required fields are missing or invalid"]);
    $db_conn->close();
    exit;
}

$stmt = $db_conn->prepare("INSERT INTO production_order_items (production_order_id, item_name, barcode, quantity, unit_weight, price, total_weight) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssiddd", $production_order_id, $item_name, $barcode, $quantity, $unit_weight, $price, $total_weight);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Production order item created successfully", "id" => $db_conn->insert_id]);
} else {
    echo json_encode(["status" => "error", "message" => "Error: " . $stmt->error]);
}

$stmt->close();
$db_conn->close();
?>