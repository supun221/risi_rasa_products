<?php
// Connect to database
require_once '../../../../config/databade.php';
session_start();

// Initialize response array
$response = [
    'success' => false,
    'product' => null,
    'message' => ''
];

try {
    // Get product ID and rep_id
    $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $rep_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1; // Default to 1 for testing
    
    if ($product_id <= 0) {
        throw new Exception('Product ID is required');
    }
    
    // Prepare and execute query to get product details
    $stmt = $conn->prepare("
        SELECT id, product_name, quantity, unit_price, total_amount, barcode
        FROM lorry_stock
        WHERE id = ? AND rep_id = ? AND status = 'active' AND quantity > 0
    ");
    $stmt->bind_param("ii", $product_id, $rep_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Product not found or out of stock');
    }
    
    // Fetch product
    $product = $result->fetch_assoc();
    
    $response['product'] = [
        'id' => $product['id'],
        'product_name' => htmlspecialchars($product['product_name']),
        'quantity' => (int)$product['quantity'],
        'unit_price' => (float)$product['unit_price'],
        'total_amount' => (float)$product['total_amount'],
        'barcode' => $product['barcode']
    ];
    
    $response['success'] = true;
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
