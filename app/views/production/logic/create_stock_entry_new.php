<?php
require_once '../../../models/Database.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $barcode = $_POST['barcode'];
    // $stock_id = $_POST['stock_id'];
    $product_name = $_POST['product_name'];
    $available_stock = $_POST['available_stock'];

    if (!empty($barcode) && !empty($product_name) && isset($available_stock)) {

        $stmt = $db_conn->prepare("INSERT INTO production_bulks
    (itemcode, product_name, available_stock) 
    VALUES (?, ?, ?)");

    $stmt->bind_param("ssd", $barcode, $product_name, $available_stock);

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
