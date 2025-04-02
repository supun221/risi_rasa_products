<?php
// Connect to database
require_once '../../../../config/databade.php';

// Initialize response array
$response = [
    'success' => false,
    'products' => [],
    'message' => ''
];

// Check if search term is provided
if (isset($_GET['term']) && !empty($_GET['term'])) {
    $search_term = '%' . $_GET['term'] . '%';
    
    try {
        // Prepare and execute query
        $stmt = $conn->prepare("
            SELECT id, stock_id, itemcode, product_name, unit, available_stock, wholesale_price
            FROM stock_entries 
            WHERE product_name LIKE ? AND available_stock > 0
            ORDER BY product_name
            LIMIT 10
        ");
        $stmt->bind_param("s", $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch products
        while ($row = $result->fetch_assoc()) {
            $response['products'][] = [
                'id' => $row['id'],
                'stock_id' => $row['stock_id'],
                'itemcode' => $row['itemcode'],
                'product_name' => $row['product_name'],
                'unit' => $row['unit'],
                'available_stock' => $row['available_stock'],
                'wholesale_price' => (float)$row['wholesale_price']
            ];
        }
        
        $response['success'] = true;
        
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'No search term provided';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
