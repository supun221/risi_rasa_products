<?php 
    include '../../../models/Database.php';
    $sql = "SELECT id, our_price, available_stock, barcode, created_at FROM stock_entries";
    $result = $db_conn->query($sql);
    $stock_entries = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $stock_entries[] = $row;
        }
    }
    echo json_encode($stock_entries);
    $db_conn->close();
?>