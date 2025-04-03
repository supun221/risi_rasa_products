<?php
// Connect to database
require_once '../../../../config/databade.php';

// Initialize response array
$response = [
    'success' => false,
    'advance_amount' => 0,
    'credit_balance' => 0,
    'message' => ''
];

// Check if customer ID is provided
if (isset($_GET['customer_id']) && !empty($_GET['customer_id'])) {
    $customer_id = (int)$_GET['customer_id'];
    
    try {
        // Prepare and execute query to get customer balance
        $stmt = $conn->prepare("
            SELECT advance_amount, credit_balance 
            FROM customers 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $customer = $result->fetch_assoc();
            $response['advance_amount'] = (float)$customer['advance_amount'];
            $response['credit_balance'] = (float)$customer['credit_balance'];
            $response['success'] = true;
        } else {
            $response['message'] = 'Customer not found';
        }
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Customer ID is required';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
