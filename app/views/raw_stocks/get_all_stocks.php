<?php
require 'connection_db.php';
// Set header to return JSON
header('Content-Type: application/json');

try {
    // Query to fetch all stocks
    $sql = "SELECT 
                stock_id,
                itemcode,
                product_name,
                our_price,
                cost_price,
                wholesale_price,
                max_retail_price,
                super_customer_price,
                purchase_qty,
                available_stock 
            FROM stock_entries";
    
    $result = $conn->query($sql);
    
    if ($result) {
        $stocks = array();
        while($row = $result->fetch_assoc()) {
            $stocks[] = $row;
        }
        echo json_encode(array("status" => "success", "data" => $stocks));
    } else {
        throw new Exception("Error fetching stocks: " . $conn->error);
    }
} catch (Exception $e) {
    echo json_encode(array("status" => "error", "message" => $e->getMessage()));
}

$conn->close();
?>