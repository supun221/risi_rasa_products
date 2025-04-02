<?php
// Connect to database
require_once '../../../../config/databade.php';
// Initialize response array
$response = [
    'success' => false,
    'products' => [],
    'message' => ''
];

// Check if barcode is provided
if (isset($_GET['barcode']) && !empty($_GET['barcode'])) {
    $barcode = $_GET['barcode'];
    
    try {
        // Prepare and execute query
        $stmt = $conn->prepare("
            SELECT id, stock_id, itemcode, product_name, unit, available_stock, wholesale_price, barcode
            FROM stock_entries 
            WHERE barcode = ? AND available_stock > 0
            ORDER BY product_name
        ");
        $stmt->bind_param("s", $barcode);
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
                'wholesale_price' => (float)$row['wholesale_price'],
                'barcode' => $row['barcode']
            ];
        }
        
        $response['success'] = true;
        
        if (empty($response['products'])) {
            $response['message'] = 'No products found with this barcode.';
        }
        
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'No barcode provided';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
