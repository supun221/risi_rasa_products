<?php
// Connect to database
require_once '../../../../config/databade.php';
session_start();

// Initialize response array
$response = [
    'success' => false,
    'additions' => [],
    'message' => ''
];

try {
    // Get rep_id from session (assuming it's stored there)
    $rep_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1; // Default to 1 for testing
    
    // Prepare and execute query to get recent additions
    $stmt = $conn->prepare("
        SELECT product_name, quantity, unit_price, total_amount, date_added
        FROM lorry_stock 
        WHERE rep_id = ? AND status = 'active'
        ORDER BY date_added DESC
        LIMIT 5
    ");
    $stmt->bind_param("i", $rep_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Fetch recent additions
    while ($row = $result->fetch_assoc()) {
        $response['additions'][] = [
            'product_name' => $row['product_name'],
            'quantity' => $row['quantity'],
            'unit_price' => (float)$row['unit_price'],
            'total_amount' => (float)$row['total_amount'],
            'date_added' => $row['date_added']
        ];
    }
    
    $response['success'] = true;
    
} catch (Exception $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
