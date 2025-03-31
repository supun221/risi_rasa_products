<?php
require 'connection_db.php';

$supplierId = isset($_GET['supplier_id']) ? $_GET['supplier_id'] : '';

if ($supplierId) {
    $sql = "SELECT stock_id, created_at, itemcode, product_name, purchase_qty, discount_percent, cost_price FROM stock_entries WHERE supplier_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $supplierId);
    $stmt->execute();
    $result = $stmt->get_result();

    $stocks = [];
    while ($row = $result->fetch_assoc()) {
        $stocks[] = $row;
    }

    echo json_encode($stocks);
} else {
    echo json_encode([]);
}
?>
