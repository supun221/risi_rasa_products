<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../../models/Database.php';
include '../../models/POS_Product.php';

// Instantiate the POS_PRODUCT class with a database connection
$productHandler = new POS_PRODUCT($db_conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stockId = $_POST['stockId'] ?? '';
    $itemId = $_POST['itemId'] ?? '';

    if (!empty($stockId) && !empty($itemId)) {
        $product = $productHandler->retrieveProduct($stockId , $itemId);

        if (is_array($product)) {
            echo json_encode(['success' => true, 'product' => $product]);
        } else {
            echo json_encode(['success' => false, 'message' => $product]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid barcode']);
    }
}
?>
