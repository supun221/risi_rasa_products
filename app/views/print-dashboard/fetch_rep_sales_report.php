<?php
// Connect to database
require_once '../../../config/databade.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'data' => []
];

// Get query parameters
$repId = isset($_GET['rep_id']) ? $_GET['rep_id'] : '';
$routeId = isset($_GET['route_id']) ? $_GET['route_id'] : '';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$barcode = isset($_GET['barcode']) ? $_GET['barcode'] : '';

try {
    // Build the base query with improved routing filter logic
    // Join with customers table to get route information
    $query = "
        SELECT 
            psi.id,
            ps.invoice_number,
            psi.product_name,
            p.item_code AS barcode,
            psi.quantity,
            psi.free_quantity,
            psi.unit_price,
            psi.discount_percent,
            psi.discount_amount,
            psi.subtotal,
            ps.customer_name,
            c.route_id,
            r.name AS route_name,
            s.username AS rep_name,
            ps.sale_date
        FROM 
            pos_sales ps
        INNER JOIN 
            pos_sale_items psi ON ps.id = psi.sale_id
        LEFT JOIN 
            customers c ON ps.customer_id = c.id
        LEFT JOIN 
            routes r ON c.route_id = r.id
        LEFT JOIN 
            products p ON psi.product_name = p.product_name
        LEFT JOIN 
            signup s ON ps.rep_id = s.id
        WHERE 1=1
    ";
    
    // Add filters based on parameters
    $params = [];
    $types = "";
    
    if (!empty($repId)) {
        $query .= " AND ps.rep_id = ?";
        $params[] = $repId;
        $types .= "i";
    }
    
    if (!empty($routeId)) {
        $query .= " AND c.route_id = ?";
        $params[] = $routeId;
        $types .= "i";
    }
    
    if (!empty($startDate)) {
        $query .= " AND DATE(ps.sale_date) >= ?";
        $params[] = $startDate;
        $types .= "s";
    }
    
    if (!empty($endDate)) {
        $query .= " AND DATE(ps.sale_date) <= ?";
        $params[] = $endDate;
        $types .= "s";
    }
    
    // Update barcode filter
    if (!empty($barcode)) {
        $query .= " AND (p.item_code = ? OR p.barcode = ?)";
        $params[] = $barcode;
        $types .= "s";
        $params[] = $barcode;
        $types .= "s";
    }
    
    // Add order by
    $query .= " ORDER BY ps.sale_date DESC, ps.invoice_number";
    
    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Fetch all data
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    $response['success'] = true;
    $response['data'] = $data;
    
    // Add debug query log
    error_log("Rep Sales Query: " . $query);
    error_log("Rep Sales Params: " . print_r($params, true));
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Error in rep sales report: " . $e->getMessage());
}

// Return the response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
