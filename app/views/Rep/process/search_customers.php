<?php
// Connect to database
require_once '../../../../config/databade.php';

// Initialize response array
$response = [
    'success' => false,
    'customers' => [],
    'message' => ''
];

try {
    // Get search term, or use empty string to get all customers
    $search_term = isset($_GET['term']) ? '%' . $_GET['term'] . '%' : '%';
    
    // Prepare and execute query
    $stmt = $conn->prepare("
        SELECT id, name, telephone, nic, address, whatsapp, credit_limit, branch
        FROM customers 
        WHERE name LIKE ? OR telephone LIKE ? OR nic LIKE ?
        ORDER BY name
        LIMIT 50
    ");
    $stmt->bind_param("sss", $search_term, $search_term, $search_term);
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

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
