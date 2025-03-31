<?php
date_default_timezone_set('Asia/Colombo');
include '../../models/Database.php';
include '../../models/POS_Product.php';
$productHandler = new POS_PRODUCT($db_conn);

session_start();
$user_name = $_SESSION['username'] ?? null;
$user_role = $_SESSION['job_role'] ?? null;
$user_branch = $_SESSION['store'] ?? null;


$billId = isset($_POST['billId']) ? $_POST['billId'] : null;
$billDate = date('Y-m-d');

if ($billId && $billDate) {
    // Clear existing data for this billId
    if ($productHandler->checkIfBillRecordExists($billId)) {
        // Delete existing bill record if it exists
        $productHandler->deleteHoldBillRecord($billId);
    }
    //if ($productHandler->deleteHoldBillRecord($billId)) {
        $result = $productHandler->createHoldBillRecord($billId, $billDate, $user_branch, $user_name);
        if ($result) {
            echo "Bill saved successfully.";
        } else {
            echo "Failed to save record.";
        }
    // } else {
    //     echo "Failed to delete existing hold bill record.";
    // }
} else {
    echo "Error: Missing required data.";
}
?>