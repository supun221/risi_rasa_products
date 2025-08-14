<?php
require_once '../../../models/Database.php';
$production_order_id = $_POST['production_order_id'] ?? '';
$produced_date = $_POST['produced_date'] ?? '';
$input_amount = floatval($_POST['input_amount'] ?? 0);
$output_amount = floatval($_POST['output_amount'] ?? 0);
$number_item_variations = intval($_POST['number_item_variations'] ?? 0);

if (empty($production_order_id) || empty($produced_date)) {
    echo json_encode(["status" => "error", "message" => "Required fields are missing"]);
    $db_conn->close();
    exit;
}

$stmt = $db_conn->prepare("INSERT INTO production_orders (production_order_id, produced_date, input_amount, output_amount, number_item_variations) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("ssddi", $production_order_id, $produced_date, $input_amount, $output_amount, $number_item_variations);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Production order created successfully", "id" => $db_conn->insert_id]);
} else {
    echo json_encode(["status" => "error", "message" => "Error: " . $stmt->error]);
}

$stmt->close();
$db_conn->close();
?>