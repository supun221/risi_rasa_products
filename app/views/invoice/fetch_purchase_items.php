
<!-- fetch_purchase_items.php -->
<?php
ob_clean(); // Clean any previous output
header("Content-Type: application/json");

include '../../models/Database.php';
include '../../models/POS_Product.php';
$productHandler = new POS_PRODUCT($db_conn);

if (isset($_GET['bill_id'])) {
    $bill_id = $db_conn->real_escape_string($_GET['bill_id']);

    $query = "SELECT stock_id, product_barcode, product_name, price, purchase_qty, discount_percentage, subtotal FROM purchase_items WHERE bill_id = '$bill_id'";
    $result = $db_conn->query($query);

    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }

    echo json_encode($items);
} else {
    echo json_encode(["error" => "No Bill ID provided"]);
}

$db_conn->close();
?>
