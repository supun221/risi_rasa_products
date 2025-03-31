<?php
include '../../models/Database.php';
include '../../models/POS_Product.php';

header("Content-Type: application/json");

if (!isset($_GET['query']) || !isset($_GET['branch'])) {
    echo json_encode(["error" => "Invalid request"]);
    exit;
}

$searchTerm = $_GET['query'];
$branch = $_GET['branch'];

$productHandler = new POS_PRODUCT($db_conn);

$barcodes = $productHandler->getSuggestedBarcodes($branch, $searchTerm);

echo json_encode($barcodes);
?>
