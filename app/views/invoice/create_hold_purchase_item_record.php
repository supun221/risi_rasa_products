<?php
include '../../models/Database.php';
include '../../models/POS_Product.php';
$productHandler = new POS_PRODUCT($db_conn);

session_start();
$user_name = $_SESSION['username'] ?? null;
$user_role = $_SESSION['job_role'] ?? null;
$user_branch = $_SESSION['store'] ?? null;

$billId = isset($_POST['billId']) ? $_POST['billId'] : null;
$barcode = isset($_POST['barcode']) ? $_POST['barcode'] : null;
$product_name = isset($_POST['product_name']) ? $_POST['product_name'] : null;
$unit_price = isset($_POST['unit_price']) ? $_POST['unit_price'] : null;
$our_price = isset($_POST['our_price']) ? $_POST['our_price'] : null;
$qty = isset($_POST['qty']) ? $_POST['qty'] : null;
$disc_percentage = isset($_POST['disc_percentage']) ? $_POST['disc_percentage'] : null;
$subtotal = isset($_POST['subtotal']) ? $_POST['subtotal'] : null;
$stockId = isset($_POST['stock_id']) ? (int)$_POST['stock_id'] : null;
$free = isset($_POST['free']) ? (int)$_POST['free'] : 0;

if ($billId && $barcode && $product_name && $unit_price && $qty && $disc_percentage !== null && $subtotal) {

    // Check if the item already exists
    $itemExists = $productHandler->checkIfItemExists($billId, $stockId);
        
    if ($itemExists) {
        // If the item exists, update only the relevant fields
        $result = $productHandler->updateHoldPurchaseItem($billId, $qty, $disc_percentage, $subtotal, $free, $stockId);
        if ($result) {
            echo "Purchase item updated successfully.";
        } else {
            echo "Failed to update purchase item.";
        }
    } else {
        // If the item does not exist, create a new record
        $result = $productHandler->createHoldPurchaseItemRecord($billId, $barcode, $product_name, $unit_price, $our_price, $qty, $disc_percentage, $subtotal, $stockId, $free, $user_branch);
        if ($result) {
            echo "Purchase item saved successfully.";
        } else {
            echo "Failed to create purchase item record.";
        }
    }

} else {
    echo "Error: Missing required data.";
}
?>