<?php
// Start session to access session variables
session_start();
// Database connection
require_once '../../../config/databade.php';

// Initialize response array
$response = [
    'success' => false,
    'data' => [],
    'message' => ''
];

// Get parameters from request
// $category = isset($_GET['category']) ? $_GET['category'] : '';
$barcode = isset($_GET['barcode']) ? $_GET['barcode'] : '';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';

try {
    // Base query to get profit information with correct column references
    $sql = "
        SELECT 
            psi.product_name,
            ls.barcode AS barcode,
            '' AS category, -- Using empty string as category is not available
            SUM(psi.quantity) AS total_qty,
            AVG(se.cost_price) AS cost_price, -- Using price from purchase_items
            AVG(psi.unit_price) AS selling_price,
            AVG(psi.discount_percent) AS avg_discount,
            SUM(psi.subtotal) AS total_revenue,
            SUM(psi.quantity * se.cost_price) AS total_cost -- Calculate cost using price
        FROM 
            pos_sale_items psi
        JOIN 
            pos_sales ps ON psi.sale_id = ps.id
        LEFT JOIN 
            lorry_stock ls ON ls.id = psi.lorry_stock_id
        LEFT JOIN 
            stock_entries se ON se.id = ls.stock_entry_id
        WHERE 1=1
    ";
    
    $params = [];
    
    if (!empty($barcode)) {
        $sql .= " AND ls.barcode = ?";
        $params[] = $barcode;
    }
    
    if (!empty($startDate)) {
        $sql .= " AND DATE(ps.sale_date) >= ?";
        $params[] = $startDate;
    }
    
    if (!empty($endDate)) {
        $sql .= " AND DATE(ps.sale_date) <= ?";
        $params[] = $endDate;
    }
    
    // Group by product to get per-product profit
    $sql .= " GROUP BY psi.product_name";
    
    // Prepare the statement
    $stmt = $conn->prepare($sql);
    
    // Bind parameters for mysqli
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    // Execute the statement
    $stmt->execute();
    
    // Get the result
    $result = $stmt->get_result();
    $data = [];
    
    // Fetch data using mysqli methods
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    if (count($data) > 0) {
        $response['success'] = true;
        $response['data'] = $data;
    } else {
        $response['message'] = "No profit data found for the specified criteria.";
    }
    
} catch (Exception $e) {
    $response['message'] = "Database error: " . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
