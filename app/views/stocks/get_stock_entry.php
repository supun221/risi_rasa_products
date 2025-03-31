<?php
include '../../models/Database.php';
include '../../models/POS_Product.php';;

$stock_id = $_GET['stock_id'] ?? null;
if (!$stock_id) {
    echo json_encode(["status" => "error", "message" => "Stock ID is required."]);
    exit();
}

$sql = "SELECT * FROM stock_entries WHERE stock_id = ?";
$stmt = $db_conn->prepare($sql);
$stmt->bind_param("s", $stock_id);
$stmt->execute();
$result = $stmt->get_result();
$stockEntry = $result->fetch_assoc();

if ($stockEntry) {
    echo json_encode(["status" => "success", "stock_entry" => $stockEntry]);
} else {
    echo json_encode(["status" => "error", "message" => "Stock entry not found."]);
}
?>
