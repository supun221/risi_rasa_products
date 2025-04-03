<?php
// Connect to database
require_once '../../../../config/databade.php';

// Initialize response array
$response = [
    'success' => false,
    'customer' => [],
    'message' => ''
];

// Check if customer ID is provided
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $customer_id = (int)$_GET['id'];
    
    try {
        // Prepare and execute query with advance payment information
        $stmt = $conn->prepare("
            SELECT c.id, c.name, c.telephone, c.nic, c.address, c.whatsapp, c.credit_limit, c.branch,
                   COALESCE(ap.net_amount, 0) as advance_amount,
                   ap.advance_bill_number
            FROM customers c
            LEFT JOIN (
                SELECT customer_id, net_amount, advance_bill_number
                FROM advance_payments
                WHERE id = (
                    SELECT MAX(id) FROM advance_payments WHERE customer_id = ?
                )
            ) ap ON c.id = ap.customer_id
            WHERE c.id = ?
        ");
        $stmt->bind_param("ii", $customer_id, $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch customer
        if ($result->num_rows > 0) {
            $customer = $result->fetch_assoc();
            
            $response['customer'] = [
                'id' => $customer['id'],
                'name' => htmlspecialchars($customer['name']),
                'telephone' => htmlspecialchars($customer['telephone']),
                'nic' => htmlspecialchars($customer['nic']),
                'address' => htmlspecialchars($customer['address']),
                'whatsapp' => htmlspecialchars($customer['whatsapp']),
                'credit_limit' => htmlspecialchars($customer['credit_limit']),
                'branch' => htmlspecialchars($customer['branch'] ?? ''),
                'advance_amount' => (float)$customer['advance_amount'],
                'advance_bill_number' => $customer['advance_bill_number']
            ];
            
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
