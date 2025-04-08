<?php
// Connect to database
require_once '../../../../config/databade.php';
session_start();

// Initialize response
$response = [
    'success' => false,
    'message' => '',
    'current_balance' => 0,
    'credit_limit' => 0,
    'available_credit' => 0
];

// Get request parameters
$customer_id = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : 0;
$credit_amount = isset($_GET['credit_amount']) ? (float)$_GET['credit_amount'] : 0;

if (!$customer_id) {
    $response['message'] = 'Customer ID is required';
    echo json_encode($response);
    exit;
}

try {
    // Get customer's credit balance and limit
    $stmt = $conn->prepare("SELECT credit_balance, credit_limit FROM customers WHERE id = ?");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $response['message'] = 'Customer not found';
        echo json_encode($response);
        exit;
    }
    
    $customer = $result->fetch_assoc();
    $current_balance = (float)$customer['credit_balance'];
    $credit_limit = (float)$customer['credit_limit'];
    $available_credit = $credit_limit - $current_balance;
    
    // Update response with customer details
    $response['current_balance'] = $current_balance;
    $response['credit_limit'] = $credit_limit;
    $response['available_credit'] = $available_credit;
    
    // Check if adding new credit would exceed limit
    if ($credit_amount > $available_credit) {
        $response['message'] = "This sale would exceed the customer's credit limit of Rs. " . 
                             number_format($credit_limit, 2) . ". Current balance: Rs. " . 
                             number_format($current_balance, 2) . ". Available credit: Rs. " .
                             number_format($available_credit, 2);
    } else {
        $response['success'] = true;
        $response['message'] = 'Credit limit check passed';
    }
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Return response
header('Content-Type: application/json');
echo json_encode($response);
?>
