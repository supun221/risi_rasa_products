<?php
include '../../models/Database.php';
include '../../models/POS_Product.php';
$productHandler = new POS_PRODUCT($db_conn);

$billId = isset($_POST['billId']) ? $_POST['billId'] : null;
$paymentStatus = isset($_POST['paymentStatus']) ? $_POST['paymentStatus'] : 'unpaid';

if ($billId) {
    // Get the bill_record_id from the bill_records table
    $stmt = $db_conn->prepare("SELECT id FROM bill_records WHERE bill_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param('s', $billId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $billRecordId = $row['id'];
        
        // Create a record in the payment_tracker table
        $result = $productHandler->createPaymentStatusRecord($billId, $paymentStatus, $billRecordId);
        
        if ($result) {
            echo "Payment tracker record created successfully.";
        } else {
            echo "Failed to create payment tracker record.";
        }
    } else {
        echo "Error: Bill record not found.";
    }
} else {
    echo "Error: Missing required data.";
}
?>