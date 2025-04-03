<?php
// Connect to database
require_once '../../../../config/databade.php';

// Initialize response array
$response = [
    'success' => false,
    'customers' => [],
    'message' => ''
];

// Check if phone number is provided
if (isset($_GET['phone']) && !empty($_GET['phone'])) {
    $phone = $_GET['phone'];
    
    // Add wildcard search to find partial matches
    $searchPhone = '%' . $phone . '%';
    
    try {
        // Prepare and execute query
        $stmt = $conn->prepare("
            SELECT id, name, telephone, nic, address, whatsapp, credit_limit, branch
            FROM customers 
            WHERE telephone LIKE ? OR whatsapp LIKE ?
            ORDER BY name
            LIMIT 5
        ");
        $stmt->bind_param("ss", $searchPhone, $searchPhone);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch customers
        while ($row = $result->fetch_assoc()) {
            $response['customers'][] = [
                'id' => $row['id'],
                'name' => htmlspecialchars($row['name']),
                'telephone' => htmlspecialchars($row['telephone']),
                'nic' => htmlspecialchars($row['nic']),
                'address' => htmlspecialchars($row['address']),
                'whatsapp' => htmlspecialchars($row['whatsapp']),
                'credit_limit' => htmlspecialchars($row['credit_limit']),
                'branch' => htmlspecialchars($row['branch'] ?? '')
            ];
        }
        
        $response['success'] = true;
        
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Phone number is required';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
