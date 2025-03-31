<?php
include '../../models/Database.php';
include '../../models/POS_Checkout.php';

$checkoutHandler = new POS_CHECKOUT($db_conn);

function getCurrentDate() {
    return date('Y-m-d');
}

$currentDate = getCurrentDate();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bill_id = $_POST['bill_id'] ?? '';
    $reason = $_POST['reason'] ?? '';
    $cancelled_by = $_POST['cancelled_by'] ?? 'Unknown Staff';
    $bill_amount = $_POST['bill_amount'] ?? 0.00;

    if (!empty($bill_id) && !empty($reason)) {
        $result = $checkoutHandler->createBillDeletionRecord($bill_id, $reason, $cancelled_by, $bill_amount, $currentDate);

        if (strpos($result, 'successfully') !== false) {
            echo json_encode(['success' => true, 'message' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => $result]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    }
}
?>
