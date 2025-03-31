<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
$branch = $_SESSION['store'];
include '../../models/Database.php';
include '../../models/POS_Product.php';

// Instantiate the POS_PRODUCT class with a database connection
$productHandler = new POS_PRODUCT($db_conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $barcode = $_POST['barcode'] ?? '';

    if (!empty($barcode)) {
        $products = $productHandler->retrieveStaticProduct2($barcode,$branch);

        if (is_array($products) && count($products) > 0) {
            echo json_encode(['success' => true, 'products' => $products]);
        } else {
            echo json_encode(['success' => false, 'message' => $products]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid barcode']);
    }
}

?>
