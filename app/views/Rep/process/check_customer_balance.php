<?php
// Connect to database
require_once '../../../../config/databade.php';

// Initialize response array
$response = [
    'success' => false,
    'advance_amount' => 0,
    'credit_balance' => 0,
    'credit_limit' => 0,
    'message' => ''
];

// Check if customer ID is provided
if (isset($_GET['customer_id']) && !empty($_GET['customer_id'])) {
    $customer_id = (int)$_GET['customer_id'];
    
    try {
        // Get real-time advance amount directly from the customers table to ensure accuracy
        $stmt = $conn->prepare("
            SELECT advance_amount, credit_balance, credit_limit 
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
            $response['credit_limit'] = (float)$customer['credit_limit'];
            $response['success'] = true;
            
            // Get the advance payment information with details
            $stmt = $conn->prepare("
                SELECT ap.id, ap.advance_bill_number, ap.net_amount, ap.created_at
                FROM advance_payments ap
                WHERE ap.customer_id = ?
                ORDER BY ap.created_at DESC
                LIMIT 1
            ");
            $stmt->bind_param("i", $customer_id);
            $stmt->execute();
            $advance_result = $stmt->get_result();
            
            if ($advance_result->num_rows > 0) {
                $advance = $advance_result->fetch_assoc();
                $response['advance_details'] = [
                    'id' => $advance['id'],
                    'bill_number' => $advance['advance_bill_number'],
                    'amount' => (float)$advance['net_amount'],
                    'date' => $advance['created_at']
                ];
                
                // Log any discrepancies between tables for debugging
                if (abs($customer['advance_amount'] - $advance['net_amount']) > 0.01) {
                    error_log("Discrepancy for customer #$customer_id: customers.advance_amount={$customer['advance_amount']}, advance_payments.net_amount={$advance['net_amount']}");
                    
                    // Option to auto-correct the discrepancy (uncomment to enable)
                    // $stmt = $conn->prepare("UPDATE customers SET advance_amount = ? WHERE id = ?");
                    // $stmt->bind_param("di", $advance['net_amount'], $customer_id);
                    // $stmt->execute();
                    // $response['advance_amount'] = (float)$advance['net_amount'];
                }
            }
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
