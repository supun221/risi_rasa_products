<?php
session_start();
$branch = strtolower($_SESSION['store']);
include '../../models/Database.php';
include '../../models/POS_Product.php';
$productHandler = new POS_PRODUCT($db_conn);

$billId = isset($_POST['billId']) ? $_POST['billId'] : null;
$stockId = isset($_POST['stockId']) ? $_POST['stockId'] : null;
$barcode = isset($_POST['barcode']) ? $_POST['barcode'] : null;
$product_name = isset($_POST['product_name']) ? $_POST['product_name'] : null;
$price = isset($_POST['price']) ? $_POST['price'] : null;
$qty = isset($_POST['qty']) ? $_POST['qty'] : null;
$disc_percentage = isset($_POST['disc_percentage']) ? $_POST['disc_percentage'] : null;
$subtotal = isset($_POST['subtotal']) ? $_POST['subtotal'] : null;
$date = isset($_POST['date']) ? $_POST['date'] : null;
$sellPrice = isset($_POST['sellPrice']) ? $_POST['sellPrice'] : null;

if ($billId && $barcode && $product_name && $price && $qty && $disc_percentage != null && $subtotal && $date) {
    $result = $productHandler->createPurchaseItemRecord2($billId, $stockId, $barcode, $product_name, $sellPrice, $qty, $disc_percentage, $subtotal, $date, $branch);
    if ($result) {
        echo "Purchase item record created successfully.";
    } else {
        echo "Failed to create purchase item record.";
    }
} else {
    echo "Error: Missing required data.";
}
?>
