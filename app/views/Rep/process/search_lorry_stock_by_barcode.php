<?php
// Connect to database
require_once '../../../../config/databade.php';
session_start();

// Initialize response array
$response = [
    'success' => false,
    'products' => [],
    'message' => ''
];

try {
    // Get barcode and rep_id
    $barcode = isset($_GET['barcode']) ? $_GET['barcode'] : '';
    $rep_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1; // Default to 1 for testing
    
    if (empty($barcode)) {
        throw new Exception('Barcode is required');
    }
    
    // Prepare and execute query to search lorry stock by barcode
    $stmt = $conn->prepare("
        SELECT id, product_name, quantity, unit_price, total_amount, barcode
        FROM lorry_stock
        WHERE rep_id = ? AND status = 'active' AND barcode = ? AND quantity > 0
        ORDER BY product_name
    ");
    $stmt->bind_param("is", $rep_id, $barcode);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Fetch products
    while ($row = $result->fetch_assoc()) {
        $response['products'][] = [
            'id' => $row['id'],
            'product_name' => htmlspecialchars($row['product_name']),
            'quantity' => (int)$row['quantity'],
            'unit_price' => (float)$row['unit_price'],
            'total_amount' => (float)$row['total_amount'],
            'barcode' => $row['barcode']
        ];
    }
    
    $response['success'] = true;
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
