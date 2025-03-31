<?php
session_start();
$branch = strtolower($_SESSION['store']);
include '../../models/Database.php';
include '../../models/POS_Product.php';
$productHandler = new POS_PRODUCT($db_conn);

$inputData = file_get_contents('php://input');
$products = json_decode($inputData, true);

if (is_array($products) && count($products) > 0) {
    foreach ($products as $product) {
        $newRemainingStock = $product['newRemainingStock'] ?? null;
        $productRealId = $product['productRealId'] ?? null;

        if ($newRemainingStock && $productRealId) {
            $result = $productHandler->updateStockEntries($productRealId, $newRemainingStock, $branch);
            if (!$result) {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update stock for product ID: ' . $productRealId]);
                exit;
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid product data.']);
            exit;
        }
    }

    http_response_code(200);
    echo json_encode(['message' => 'All stock entries updated successfully.']);
} else {
    http_response_code(400);
    echo var_dump($products);
    echo json_encode(['error' => 'Invalid input data.']);
}
?>
