<?php
require_once '../../../models/Database.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $barcode = $_POST['barcode'];
    $price = $_POST['our_price'];
    $new_stock = $_POST['new_stock'];

    if (!empty($barcode) && !empty($price) && isset($new_stock)) {

        $stmt = $db_conn->prepare("UPDATE stock_entries SET available_stock = ? WHERE itemcode = ? AND our_price = ?");
        $stmt->bind_param("iss", $new_stock, $barcode, $price);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Stock updated"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Update failed"]);
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
