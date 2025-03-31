<?php
session_start();
include '../../models/Database.php';

// Check if the bill number is provided
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bill_no = $_POST['bill_no'] ?? '';

    if (!empty($bill_no)) {
        // Start a transaction to ensure atomicity
        $db_conn->begin_transaction();

        try {
            // Delete the bill from hold_bill_records table
            $query_delete_bill = "DELETE FROM hold_bill_records WHERE bill_id = ?";
            $stmt_delete_bill = $db_conn->prepare($query_delete_bill);
            $stmt_delete_bill->bind_param('s', $bill_no);

            // Delete associated purchase items from hold_purchase_items table
            $query_delete_items = "DELETE FROM hold_purchase_items WHERE bill_id = ?";
            $stmt_delete_items = $db_conn->prepare($query_delete_items);
            $stmt_delete_items->bind_param('s', $bill_no);

            if ($stmt_delete_bill->execute() && $stmt_delete_items->execute()) {
                $db_conn->commit();
                echo json_encode(['success' => true]);
            } else {
                $db_conn->rollback();
                echo json_encode(['success' => false, 'message' => 'Failed to delete the invoice and its items']);
            }
        } catch (Exception $e) {
            $db_conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Error occurred: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid bill number']);
    }
}
?>