<?php
session_start();
header("Content-Type: application/json");

include '../../models/Database.php';
include '../../models/POS_Product.php';

if (!isset($db_conn) || !$db_conn) {
    die(json_encode(["success" => false, "error" => "Database connection failed"]));
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    die(json_encode(["success" => false, "error" => "Invalid JSON data"]));
}

$db_conn->begin_transaction();

try {
    $stmt = $db_conn->prepare("INSERT INTO refunded_bill_items 
        (bill_id, stock_id, product_barcode, product_name, return_quantity, refund_amount, refund_date, refunded_by) 
        VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)");
    
    if (!$stmt) {
        throw new Exception("Prepare failed for refunded_bill_items: " . $db_conn->error);
    }
        
    $updateStockStmt = $db_conn->prepare("UPDATE stock_entries SET available_stock = available_stock + ? WHERE stock_id = ?");
    
    if (!$updateStockStmt) {
        throw new Exception("Prepare failed for stock_entries: " . $db_conn->error);
    }
    
    $updatePurchaseItemsStmt = $db_conn->prepare("UPDATE purchase_items 
        SET purchase_qty = purchase_qty - ?,
            subtotal = subtotal - ?
        WHERE bill_id = ? AND stock_id = ? AND product_barcode = ?");
    
    if (!$updatePurchaseItemsStmt) {
        throw new Exception("Prepare failed for purchase_items: " . $db_conn->error);
    }
    
    $billRefunds = [];
    
    foreach ($data as $item) {
        
        if (empty($item["bill_id"]) || empty($item["stock_id"]) || empty($item["product_barcode"])) {
            throw new Exception("Missing required fields in item data");
        }
        
        
        $return_quantity = (int)$item["return_quantity"];
        $refund_amount = (float)$item["refund_amount"];

        $stmt->bind_param("ssssdds", 
            $item["bill_id"], 
            $item["stock_id"], 
            $item["product_barcode"], 
            $item["product_name"], 
            $return_quantity, 
            $refund_amount,
            $_SESSION['username']
        );

        if (!$stmt->execute()) {
            throw new Exception("Failed to insert refund record for Stock ID: " . $item["stock_id"] . " - " . $stmt->error);
        }

        $updateStockStmt->bind_param("is", $return_quantity, $item["stock_id"]);
        if (!$updateStockStmt->execute()) {
            throw new Exception("Failed to update stock for Stock ID: " . $item["stock_id"] . " - " . $updateStockStmt->error);
        }
        
        $updatePurchaseItemsStmt->bind_param("ddsss", 
            $return_quantity,
            $refund_amount,
            $item["bill_id"], 
            $item["stock_id"], 
            $item["product_barcode"]
        );
        if (!$updatePurchaseItemsStmt->execute()) {
            throw new Exception("Failed to update purchase_items for Stock ID: " . $item["stock_id"] . " - " . $updatePurchaseItemsStmt->error);
        }
        
        if (!isset($billRefunds[$item["bill_id"]])) {
            $billRefunds[$item["bill_id"]] = 0;
        }
        $billRefunds[$item["bill_id"]] += $refund_amount;
    }
    
    if (!empty($billRefunds)) {
        $updateBillRecordsStmt = $db_conn->prepare("UPDATE bill_records 
            SET gross_amount = gross_amount - ?, 
                net_amount = net_amount - ? 
            WHERE bill_id = ?");
        
        if (!$updateBillRecordsStmt) {
            throw new Exception("Prepare failed for bill_records: " . $db_conn->error);
        }
        
        foreach ($billRefunds as $billId => $refundAmount) {
            $updateBillRecordsStmt->bind_param("dds", $refundAmount, $refundAmount, $billId);
            if (!$updateBillRecordsStmt->execute()) {
                throw new Exception("Failed to update bill_records for Bill ID: " . $billId . " - " . $updateBillRecordsStmt->error);
            }
        }
    }

    $db_conn->commit();

    echo json_encode(["success" => true]);
} catch (Exception $e) {
    $db_conn->rollback();
    error_log("Refund Error: " . $e->getMessage()); // Log the error for debugging
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($updateStockStmt)) $updateStockStmt->close();
    if (isset($updatePurchaseItemsStmt)) $updatePurchaseItemsStmt->close();
    if (isset($updateBillRecordsStmt)) $updateBillRecordsStmt->close();
    $db_conn->close();
}
?>