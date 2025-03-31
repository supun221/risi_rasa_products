<?php
include '../../models/Database.php';
include '../../models/POS_Stock.php';

header('Content-Type: application/json');

$posStock = new POS_STOCK($db_conn);

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['stock_transfer_id'])) {
    $stockTransferId = $_GET['stock_transfer_id'];
    
    $items = $posStock->getStockTransferItemsByStockTransferId($stockTransferId);

    if ($items !== false) {
        echo json_encode(['status' => 'success', 'data' => $items]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to fetch stock transfer items']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>
