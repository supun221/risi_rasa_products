<?php
// Connect to database
require_once '../../../../config/databade.php';
session_start();

// Initialize response
$response = [
    'success' => false,
    'message' => '',
    'amount' => 0
];

// Get return bill number from request
$return_bill_number = isset($_GET['return_bill_number']) ? trim($_GET['return_bill_number']) : '';

if (empty($return_bill_number)) {
    $response['message'] = "Return bill number is required.";
} else {
    try {
        // Check if return bill exists and is valid (not used)
        $stmt = $conn->prepare("
            SELECT id, net_amount, status 
            FROM pos_returns 
            WHERE return_bill_number = ?
        ");
        $stmt->bind_param("s", $return_bill_number);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $response['message'] = "Return bill not found.";
        } else {
            $return_bill = $result->fetch_assoc();
            
            if ($return_bill['status'] !== 'active') {
                $response['message'] = "This return bill has already been used.";
            } else {
                $response['success'] = true;
                $response['amount'] = $return_bill['net_amount'];
                $response['message'] = "Return bill verified successfully.";
            }
        }
    } catch (Exception $e) {
        $response['message'] = "Error: " . $e->getMessage();
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
