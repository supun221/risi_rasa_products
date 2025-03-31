<?php
include '../../models/Database.php';
include '../../models/POS_Product.php';
$productHandler = new POS_PRODUCT($db_conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $barcode = $_POST['barcode'] ?? '';

    if (!empty($barcode)) {
        // Fetch promotion details
        $promotions = $productHandler->retrievePromotions($barcode);

        if (is_array($promotions) && count($promotions) > 0) {
            echo json_encode([
                'success' => true,
                'promotions' => $promotions
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No promotions found for this product.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid barcode']);
    }
}
?>