<?php
// Connect to database
require_once '../../../../config/databade.php';

// Initialize response array
$response = [
    'success' => false,
    'valid' => false,
    'amount' => 0,
    'message' => '',
    'return_bill_data' => null
];

// Check if return bill number is provided
if (isset($_GET['return_bill_number']) && !empty($_GET['return_bill_number'])) {
    $return_bill_number = $_GET['return_bill_number'];
    
    try {
        // Check if return bill exists
        $stmt = $conn->prepare("
            SELECT r.*, 
                   DATE_FORMAT(r.created_at, '%Y-%m-%d') as formatted_date
            FROM return_collections r
            WHERE r.return_bill_number = ?
            LIMIT 1
        ");
        $stmt->bind_param("s", $return_bill_number);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $return_bill = $result->fetch_assoc();
            
            // Check if the return bill has already been used
            if ($return_bill['used_in_invoice']) {
                $response['message'] = 'This return bill has already been used in invoice: ' . $return_bill['used_in_invoice'];
            } else {
                // Return bill is valid and can be used
                $response['valid'] = true;
                $response['success'] = true;
                $response['amount'] = (float)$return_bill['total_amount'];
                $response['message'] = 'Valid return bill found.';
                $response['return_bill_data'] = [
                    'id' => $return_bill['id'],
                    'return_bill_number' => $return_bill['return_bill_number'],
                    'customer_id' => $return_bill['customer_id'],
                    'customer_name' => $return_bill['customer_name'],
                    'original_invoice' => $return_bill['original_invoice_number'],
                    'date' => $return_bill['formatted_date'],
                    'amount' => (float)$return_bill['total_amount']
                ];
            }
        } else {
            $response['message'] = 'Return bill not found.';
        }
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Return bill number is required';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>