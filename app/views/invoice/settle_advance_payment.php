<?php

include '../../models/Database.php';
include '../../models/POS_Checkout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['record_id'])) {
    $recordId = intval($_POST['record_id']);

    $checkoutHandler = new POS_CHECKOUT($db_conn);
    $result = $checkoutHandler->deleteAdvancedPaymentById($recordId);

    if (strpos($result, 'successfully') !== false) {
        echo "Advance payment settled!.";
    } else {
        echo "Failed to update advance payment records!: " . $result;
    }
} else {
    echo "Invalid request.";
}

?>
