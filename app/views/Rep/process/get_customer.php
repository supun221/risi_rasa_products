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
        // Prepare and execute query
        $stmt = $conn->prepare("
            SELECT id, name, telephone, nic, address, whatsapp, credit_limit, branch
            FROM customers 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $customer_id);
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
                'branch' => htmlspecialchars($customer['branch'] ?? '')
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
