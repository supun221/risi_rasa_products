<?php
include '../../models/Database.php';
include '../../models/POS_Stock.php';

$posStock = new POS_STOCK($db_conn);

session_start();

if (!isset($_SESSION['store']) || !isset($_SESSION['username'])) {
    echo json_encode(['status' => 'error', 'message' => 'User session not set. Please log in.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['stock_transfer_id'], $data['transferred_branch'], $data['items'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing or invalid JSON data.']);
        exit;
    }

    $stockTransferId = $data['stock_transfer_id'];
    $transferringBranch = $_SESSION['store'];
    $transferredBranch = $data['transferred_branch'];
    $issuerName = $_SESSION['username'];
    $items = $data['items'];

    $numberOfItems = count($items);

    // Start Transaction
    $db_conn->begin_transaction();

    try {
        // Insert stock transfer record
        $recordInserted = $posStock->createStockTransferRecord(
            $stockTransferId, 
            $transferringBranch, 
            $transferredBranch, 
            $issuerName, 
            $numberOfItems
        );

        if (!$recordInserted) {
            throw new Exception("Failed to create stock transfer record.");
        }

        // Process each item
        foreach ($items as $item) {
            $itemName = $item['item_name'];
            $itemBarcode = $item['item_barcode'];
            $numOfQty = (int)$item['num_of_qty'];
            $stockId = $item['stock_id'];

            // Insert into stock_transfer_items
            if (!$posStock->addStockTransferItem($stockTransferId, $itemName, $itemBarcode, $numOfQty, $stockId)) {
                throw new Exception("Failed to add stock transfer item.");
            }

            // Decrease available_stock in stock_entries
            $updateStockQuery = "UPDATE stock_entries SET available_stock = available_stock - ? WHERE stock_id = ?";
            $stmt = $db_conn->prepare($updateStockQuery);
            if (!$stmt) {
                throw new Exception("Stock update query failed to prepare.");
            }

            $stmt->bind_param("is", $numOfQty, $stockId);
            if (!$stmt->execute()) {
                throw new Exception("Failed to update available stock.");
            }
        }

        // Commit transaction if everything is successful
        $db_conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Stock transfer and items processed successfully!']);
    } catch (Exception $e) {
        // Rollback on any error
        $db_conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

?>
