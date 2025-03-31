<?php
include '../../models/Database.php';
include '../../models/POS_Product.php';

// Check if stock_transfer_id is provided
if (!isset($_GET['stock_transfer_id'])) {
    echo json_encode(["status" => "error", "message" => "Stock transfer ID is required"]);
    exit;
}

$stockTransferId = $_GET['stock_transfer_id'];

// Fetch stock transfer record
$sql = "SELECT stock_transfer_id, transferring_branch, transferred_branch, issue_date, issuer_name, number_of_items 
        FROM stock_transfer_records 
        WHERE stock_transfer_id = ?";
$stmt = $db_conn->prepare($sql);
$stmt->bind_param("s", $stockTransferId);
$stmt->execute();
$result = $stmt->get_result();
$stock_transfer = $result->fetch_assoc();

if (!$stock_transfer) {
    echo json_encode(["status" => "error", "message" => "Stock transfer not found"]);
    exit;
}

// Fetch stock transfer items
$sql = "SELECT stock_transfer_id, stock_id, item_name, item_barcode, num_of_qty, state 
        FROM stock_transfer_items 
        WHERE stock_transfer_id = ?";
$stmt = $db_conn->prepare($sql);
$stmt->bind_param("s", $stockTransferId);
$stmt->execute();
$result = $stmt->get_result();

$items = [];
while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode(["status" => "success", "data" => ["stock_transfer" => $stock_transfer, "items" => $items]]);
?>
