<?php
include '../../models/Database.php';
include '../../models/POS_Product.php';
$productHandler = new POS_PRODUCT($db_conn);

$billNo = isset($_GET['billNo']) ? $_GET['billNo'] : null;

if ($billNo) {
    $query = "SELECT stock_id, product_barcode, product_name, product_mrp, our_price, purchase_qty, discount_percentage, free, subtotal FROM hold_purchase_items WHERE bill_id = ?";
    $stmt = $db_conn->prepare($query);
    $stmt->bind_param('s', $billNo);  // Bind the parameter
    $stmt->execute();
    $result = $stmt->get_result();

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    echo json_encode($items);
} else {
    echo json_encode(["error" => "Invalid Bill Number"]);
}
?>