<?php

session_start();
$branch = strtolower($_SESSION['store']);
include '../../models/Database.php';
include '../../models/POS_Product.php';
$productHandler = new POS_PRODUCT($db_conn);

$products = $productHandler->getAllProducts($branch);

if (is_array($products)) {
    echo json_encode($products);
} else {
    echo json_encode(["error" => "No products available"]);
}
?>
