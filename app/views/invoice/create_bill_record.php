<?php
include '../../models/Database.php';
include '../../models/POS_Product.php';
$productHandler = new POS_PRODUCT($db_conn);

session_start();
$branch = strtolower($_SESSION['store']);
$user_name = $_SESSION['username'];

$billId = isset($_POST['billId']) ? $_POST['billId'] : null;
$customerID = isset($_POST['customer_id']) ? $_POST['customer_id'] : null;
$grossAmount = isset($_POST['gross_amount']) ? $_POST['gross_amount'] : null;
$netAmount = isset($_POST['net_amount']) ? $_POST['net_amount'] : null;
$discountAmount = isset($_POST['discount_amount']) ? $_POST['discount_amount'] : null;
$numOfProducts = isset($_POST['num_of_products']) ? $_POST['num_of_products'] : null;
$paymentType = isset($_POST['payment_type']) ? $_POST['payment_type'] : null;
$balance = isset($_POST['balance']) ? $_POST['balance'] : null;
$billDate = isset($_POST['bill_date']) ? $_POST['bill_date'] : null;
$customerNo = isset($_POST['customerNo']) ? $_POST['customerNo'] : null;
$orderWeight = isset($_POST['orderWeight']) ? $_POST['orderWeight'] : null;
$transportFee = isset($_POST['transportFee']) ? $_POST['transportFee'] : null;
$remark = isset($_POST['remark']) ? $_POST['remark'] : null;
$paymentStatus = isset($_POST['paymentStatus']) ? $_POST['paymentStatus'] : 'unpaid';
$payments = isset($_POST['payments']) ? $_POST['payments'] : null;

// Debug logging
error_log("Processed variables:");
error_log("billId: $billId");
error_log("payments: $payments");

// Validate that payments is valid JSON
if ($payments) {
    $decodedPayments = json_decode($payments);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("JSON Error: " . json_last_error_msg());
        echo "Error: Invalid payments data format";
        exit;
    }
}

if ($billId && $customerID && $grossAmount !== null && $netAmount !== null && $discountAmount !== null && $numOfProducts !== null && $paymentType && $balance !== null) {
    $result = $productHandler->createBillRecord($billId, $customerID, $grossAmount, $netAmount, $discountAmount, $numOfProducts, $paymentType, $balance, $billDate, $branch, $user_name, $customerNo, $orderWeight, $transportFee, $remark, $payments);
    if ($result) {
        echo "Bill record created successfully.";
    } else {
        echo "Failed to create bill record.";
    }
} else {
    echo "Error: Missing required data.";
}
?>