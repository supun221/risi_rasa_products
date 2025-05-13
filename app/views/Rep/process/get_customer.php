<?php
// Connect to database
require_once '../../../../config/databade.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'customer' => null
];

// Check if customer ID is provided
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    try {
        // Fetch customer details including route_id and route_name
        $stmt = $conn->prepare("
            SELECT 
                c.*,
                COALESCE(ap.net_amount, 0) as advance_amount,
                r.name as route_name
            FROM 
                customers c
            LEFT JOIN (
                SELECT customer_id, net_amount 
                FROM advance_payments 
                WHERE id IN (
                    SELECT MAX(id) 
                    FROM advance_payments 
                    GROUP BY customer_id
                )
            ) ap ON c.id = ap.customer_id
            LEFT JOIN routes r ON c.route_id = r.id
            WHERE c.id = ?
        ");
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $customer = $result->fetch_assoc();
            
            // Explicitly include route_id in the response for debugging
            error_log("Customer #{$id} route_id: " . var_export($customer['route_id'], true));
            
            $response['success'] = true;
            $response['customer'] = $customer;
        } else {
            $response['message'] = 'Customer not found';
        }
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
        error_log("Error fetching customer: " . $e->getMessage());
    }
} else {
    $response['message'] = 'Customer ID is required';
}

// Debug output
error_log("Customer response: " . json_encode($response));

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
