<?php
// Initialize session and database connection
require_once '../../../../config/databade.php';
session_start();

// Initialize response
$response = [
    'success' => false,
    'message' => '',
    'amount' => 0,
    'return_data' => null
];

try {
    // Get the return bill number from request
    $return_bill_number = isset($_GET['return_bill_number']) ? trim($_GET['return_bill_number']) : '';
    
    // Validate return bill number
    if (empty($return_bill_number)) {
        throw new Exception('Return bill number is required');
    }
    
    // Get rep_id from session
    $rep_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    
    // Query to check if return bill exists and is valid
    // Modified to include both 'approved' and 'pending' statuses
    $stmt = $conn->prepare("
        SELECT id, return_bill_number, original_invoice_number, customer_id, customer_name, 
               total_amount, used_in_invoice, used_date, approval_status
        FROM return_collections
        WHERE return_bill_number = ? AND rep_id = ? 
        AND approval_status IN ('approved', 'pending') 
        AND used_in_invoice IS NULL
    ");
    $stmt->bind_param("si", $return_bill_number, $rep_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if return bill was found
    if ($result->num_rows === 0) {
        throw new Exception("Invalid or already used return bill number. Please check and try again.");
    }
    
    // Get return bill data
    $return_data = $result->fetch_assoc();
    
    // Set success response
    $response['success'] = true;
    $response['amount'] = $return_data['total_amount'];
    $response['return_data'] = [
        'id' => $return_data['id'],
        'return_bill_number' => $return_data['return_bill_number'],
        'original_invoice' => $return_data['original_invoice_number'],
        'customer_id' => $return_data['customer_id'],
        'customer_name' => $return_data['customer_name'],
        'approval_status' => $return_data['approval_status']
    ];
    
    // Include a notice if the status is pending
    if ($return_data['approval_status'] === 'pending') {
        $response['message'] = "Note: This return bill is pending approval but can be used.";
    }
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>