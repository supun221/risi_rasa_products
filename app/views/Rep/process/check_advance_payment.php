<?php
// Connect to database
require_once '../../../../config/databade.php';

// Initialize response array
$response = [
    'success' => false,
    'has_advance' => false,
    'amount' => 0,
    'bill_number' => '',
    'message' => ''
];

// Check if customer ID is provided
if (isset($_GET['customer_id']) && !empty($_GET['customer_id'])) {
    $customer_id = (int)$_GET['customer_id'];
    
    try {
        // Prepare and execute query to check for existing advance payment
        $stmt = $conn->prepare("
            SELECT advance_bill_number, net_amount 
            FROM advance_payments 
            WHERE customer_id = ? 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            $response['has_advance'] = true;
            $response['amount'] = $row['net_amount'];
            $response['bill_number'] = $row['advance_bill_number'];
        }
        
        $response['success'] = true;
        
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
