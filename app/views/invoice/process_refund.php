<?php
header("Content-Type: application/json");

include '../../models/Database.php';
include '../../models/POS_Product.php';

$productHandler = new POS_PRODUCT($db_conn);
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    die(json_encode(["success" => false, "error" => "Invalid data"]));
}

// Start a transaction
$db_conn->begin_transaction();

try {
    // Prepare the refund insertion query
    $stmt = $db_conn->prepare("INSERT INTO refunded_bill_items 
        (bill_id, stock_id, product_barcode, product_name, return_quantity, refund_amount, refund_date) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())");

    // Prepare the stock update query
    $updateStockStmt = $db_conn->prepare("UPDATE stock_entries SET available_stock = available_stock + ? WHERE stock_id = ?");

    foreach ($data as $item) {
        // Insert refund record
        $stmt->bind_param("ssssdd", 
            $item["bill_id"], 
            $item["stock_id"], 
            $item["product_barcode"], 
            $item["product_name"], 
            $item["return_quantity"], 
            $item["refund_amount"]
        );

        if (!$stmt->execute()) {
            throw new Exception("Failed to insert refund record for Stock ID: " . $item["stock_id"]);
        }

        // Update stock in stock_entries table (adding back the returned quantity)
        $updateStockStmt->bind_param("is", $item["return_quantity"], $item["stock_id"]);
        if (!$updateStockStmt->execute()) {
            throw new Exception("Failed to update stock for Stock ID: " . $item["stock_id"]);
        }
    }

    // If all queries were successful, commit the transaction
    $db_conn->commit();

    echo json_encode(["success" => true]);
} catch (Exception $e) {
    // If an error occurs, roll back the transaction
    $db_conn->rollback();

    echo json_encode(["success" => false, "error" => $e->getMessage()]);
} finally {
    // Close statements and connection
    $stmt->close();
    $updateStockStmt->close();
    $db_conn->close();
}
?>
