<?php
require 'connection_db.php';
header('Content-Type: application/json');

try {
    // Get pagination parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;
    
    // Count total records
    $countSql = "SELECT COUNT(*) as total FROM stock_entries_raw";
    $countResult = $conn->query($countSql);
    $totalRecords = $countResult->fetch_assoc()['total'];
    
    // Query to fetch paginated stocks with supplier name, ordered by latest first
    $sql = "SELECT 
                se.stock_id,
                se.itemcode,
                se.product_name,
                se.our_price,
                se.cost_price,
                se.wholesale_price,
                se.max_retail_price,
                se.super_customer_price,
                se.purchase_qty,
                se.available_stock,
                se.supplier_id,
                se.created_at,
                s.supplier_name
            FROM stock_entries_raw se
            LEFT JOIN suppliers s ON se.supplier_id = s.supplier_id
            ORDER BY se.stock_id DESC
            LIMIT ? OFFSET ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result) {
        $stocks = array();
        while($row = $result->fetch_assoc()) {
            $stocks[] = $row;
        }
        echo json_encode(array(
            "status" => "success", 
            "data" => $stocks,
            "total" => $totalRecords,
            "page" => $page,
            "totalPages" => ceil($totalRecords / $limit)
        ));
    } else {
        throw new Exception("Error fetching stocks: " . $conn->error);
    }
} catch (Exception $e) {
    echo json_encode(array("status" => "error", "message" => $e->getMessage()));
}

$conn->close();
?>