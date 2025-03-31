<?php
require_once '../../../models/Database.php';

$latest_stock_id = 1;
$query = "SELECT stock_id FROM stock_entries ORDER BY stock_id DESC LIMIT 1";
$result = $db_conn->query($query);
if ($result && $row = $result->fetch_assoc()) {
    $latest_stock_id = (int) $row['stock_id'] + 1;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $barcode = $_POST['barcode'];
    // $stock_id = $_POST['stock_id'];
    $product_name = $_POST['product_name'];
    $available_stock = $_POST['available_stock'];
    $our_price = $_POST['our_price'];
    $cost_price = $_POST['cost_price'];
    $wholesale_price = $_POST['wholesale_price'];
    $mr_price = $_POST['mr_price'];
    $sc_price = $_POST['sc_price'];

    if (!empty($barcode) && !empty($product_name) && isset($available_stock) && isset($our_price)) {

        $supplier_id = "self_001";
        $branch = "main store";
        $created_at = date("Y-m-d");
        $expire_date = "0000-00-00";
        $purchase_qty = 0;
        $unit = 'packet';

        $stmt = $db_conn->prepare("INSERT INTO stock_entries 
    (supplier_id, stock_id, itemcode, product_name, available_stock, our_price, 
    cost_price, wholesale_price, max_retail_price, super_customer_price, branch, 
    created_at, expire_date, purchase_qty, unit, barcode) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("sissddddddsissis", 
    $supplier_id, $latest_stock_id, $barcode, $product_name, $available_stock, 
    $our_price, $cost_price, $wholesale_price, $mr_price, 
    $sc_price, $branch, $created_at, $expire_date, $purchase_qty, $unit, $barcode);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Stock created"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Insert failed"]);
        }

        $stmt->close();
        $db_conn->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid parameters"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}
?>
