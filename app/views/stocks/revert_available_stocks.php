<?php
include '../../models/Database.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['item_barcode'], $data['stock_id'], $data['quantity'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing or invalid input data.']);
        exit;
    }

    $itemBarcode = $data['item_barcode'];
    $stockId = (int) $data['stock_id'];
    $quantityToAdd = (int) $data['quantity'];

    if ($quantityToAdd <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid quantity value.']);
        exit;
    }

    $db_conn->begin_transaction();

    try {
        $fetchStockQuery = "SELECT available_stock FROM stock_entries WHERE barcode = ? AND stock_id = ?";
        $stmt = $db_conn->prepare($fetchStockQuery);
        
        if (!$stmt) {
            throw new Exception("Failed to prepare fetch query.");
        }

        $stmt->bind_param("si", $itemBarcode, $stockId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Stock entry not found for the given barcode and stock ID.");
        }

        $row = $result->fetch_assoc();
        $currentStock = (int) $row['available_stock'];

        $newStock = $currentStock + $quantityToAdd;

        $updateStockQuery = "UPDATE stock_entries SET available_stock = ? WHERE barcode = ? AND stock_id = ?";
        $stmt = $db_conn->prepare($updateStockQuery);
        
        if (!$stmt) {
            throw new Exception("Failed to prepare update query.");
        }

        $stmt->bind_param("isi", $newStock, $itemBarcode, $stockId);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update available stock.");
        }

        $db_conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Stock updated successfully.', 'new_stock' => $newStock]);
    } catch (Exception $e) {
        $db_conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>
