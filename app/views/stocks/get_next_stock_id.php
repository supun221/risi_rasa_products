<?php
include '../../models/Database.php';
include '../../models/POS_Product.php';

// Get the latest stock_id and increment it
$sql = "SELECT MAX(stock_id) AS max_id FROM stock_entries";
$result = $db_conn->query($sql);
$row = $result->fetch_assoc();

$nextStockId = ($row['max_id'] ?? 10000) + 1;

echo json_encode(["status" => "success", "next_stock_id" => $nextStockId]);
?>
