<?php
// Connect to database
require_once '../../../../config/databade.php';

// Initialize or continue session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'bill_number' => '',
    'new_amount' => 0
];

// Check if form data is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $customer_id = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : 0;
    $customer_name = isset($_POST['customer_name']) ? $_POST['customer_name'] : '';
    $payment_amount = isset($_POST['payment_amount']) ? (float)$_POST['payment_amount'] : 0;
    $payment_type = isset($_POST['payment_type']) ? $_POST['payment_type'] : '';
    $reason = isset($_POST['reason']) ? $_POST['reason'] : '';
    $print_bill = isset($_POST['print_bill']) ? (int)$_POST['print_bill'] : 0;
    $advance_bill_number = isset($_POST['advance_bill_number']) ? $_POST['advance_bill_number'] : '';
    
    // Get branch from session, with explicit default value
    $branch = "main"; // Default branch value
    
    // Log session variables for debugging
    error_log("SESSION: " . print_r($_SESSION, true));
    
    // Only override default if a session value exists
    if (isset($_SESSION['store']) && !empty($_SESSION['store'])) {
        $branch = $_SESSION['store'];
    } else if (isset($_SESSION['branch']) && !empty($_SESSION['branch'])) {
        $branch = $_SESSION['branch'];
    } else if (isset($_SESSION['branch_name']) && !empty($_SESSION['branch_name'])) {
        $branch = $_SESSION['branch_name'];
    }
    
    // Log the branch value being used
    error_log("Branch value being used: " . $branch);
    
    // Validate inputs
    if ($customer_id <= 0 || empty($customer_name) || $payment_amount <= 0 || empty($payment_type)) {
        $response['message'] = 'Invalid input data';
        echo json_encode($response);
        exit;
    }
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // First check if this customer has an existing advance payment
        $stmt = $conn->prepare("
            SELECT id, advance_bill_number, net_amount 
            FROM advance_payments 
            WHERE customer_id = ?
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Customer has existing advance payment - get current amount for response
            $existing = $result->fetch_assoc();
            $new_amount = $existing['net_amount'] + $payment_amount;
            
            // IMPORTANT: Always use the new bill number from the form
            // Insert a new record with the NEW bill number
            $stmt = $conn->prepare("
                INSERT INTO advance_payments 
                (customer_id, customer_name, payment_type, reason, net_amount, print_bill, advance_bill_number, branch)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("isssdiss", $customer_id, $customer_name, $payment_type, $reason, $payment_amount, $print_bill, $advance_bill_number, $branch);
            $stmt->execute();
            
            $response['message'] = 'Advance payment saved successfully';
            $response['bill_number'] = $advance_bill_number; // Use the new bill number
            $response['new_amount'] = $new_amount;
        } else {
            // Insert new advance payment
            $stmt = $conn->prepare("
                INSERT INTO advance_payments 
                (customer_id, customer_name, payment_type, reason, net_amount, print_bill, advance_bill_number, branch)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("isssdiss", $customer_id, $customer_name, $payment_type, $reason, $payment_amount, $print_bill, $advance_bill_number, $branch);
            $stmt->execute();
            
            $response['message'] = 'Advance payment saved successfully';
            $response['bill_number'] = $advance_bill_number;
            $response['new_amount'] = $payment_amount;
        }
        
        // IMPORTANT: Update the customer's advance_amount in the customers table
        $stmt = $conn->prepare("
            UPDATE customers 
            SET advance_amount = advance_amount + ?
            WHERE id = ?
        ");
        $stmt->bind_param("di", $payment_amount, $customer_id);
        $stmt->execute();
        
        // Add branch information to response for debugging
        $response['branch'] = $branch;
        
        // Commit transaction
        $conn->commit();
        
        $response['success'] = true;
        
        // Add refresh flag to indicate the page should be refreshed
        $response['refresh'] = true;
        
    } catch (Exception $e) {
        // Roll back transaction on error
        $conn->rollback();
        $response['message'] = 'Error: ' . $e->getMessage();
        error_log("Database error in save_advance_payment.php: " . $e->getMessage());
    }
} else {
    $response['message'] = 'Invalid request method';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
