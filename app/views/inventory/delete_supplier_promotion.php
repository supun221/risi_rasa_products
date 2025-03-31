<?php
require 'connection_db.php';

$supplier_id = $_POST['supplier_id'];

$response = [];

// Start transaction
$conn->begin_transaction();

try {
    // Update stock entries to set deal_price, start_date, and end_date to NULL
    $update_stock_sql = "UPDATE stock_entries 
                         SET deal_price = NULL, start_date = NULL, end_date = NULL 
                         WHERE supplier_id = ?";
    $update_stock_stmt = $conn->prepare($update_stock_sql);
    $update_stock_stmt->bind_param("i", $supplier_id);

    if (!$update_stock_stmt->execute()) {
        throw new Exception("Error updating stock entries: " . $conn->error);
    }

    // Delete the promotion from supplier_promotions table
    $delete_promo_sql = "DELETE FROM supplier_promotions WHERE supplier_id = ?";
    $delete_promo_stmt = $conn->prepare($delete_promo_sql);
    $delete_promo_stmt->bind_param("i", $supplier_id);

    if (!$delete_promo_stmt->execute()) {
        throw new Exception("Error deleting promotion: " . $conn->error);
    }

    // Commit transaction if both queries are successful
    $conn->commit();

    $response["success"] = true;
    $response["message"] = "Promotion deleted successfully and stock entries updated!";
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    $response["success"] = false;
    $response["message"] = $e->getMessage();
}

echo json_encode($response);

// Close statements
$update_stock_stmt->close();
$delete_promo_stmt->close();
$conn->close();
?>
