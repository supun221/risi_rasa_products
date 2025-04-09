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
        // Get customer advance amount from customers table
        $stmt = $conn->prepare("
            SELECT advance_amount 
            FROM customers 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $customer = $result->fetch_assoc();
            
            // Now get the latest bill number for reference (optional)
            $stmt = $conn->prepare("
                SELECT advance_bill_number 
                FROM advance_payments 
                WHERE customer_id = ? 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->bind_param("i", $customer_id);
            $stmt->execute();
            $bill_result = $stmt->get_result();
            $bill_number = '';
            
            if ($bill_result->num_rows > 0) {
                $bill_row = $bill_result->fetch_assoc();
                $bill_number = $bill_row['advance_bill_number'];
            }
            
            $response['has_advance'] = $customer['advance_amount'] > 0;
            $response['amount'] = $customer['advance_amount'];
            $response['bill_number'] = $bill_number;
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
