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
    
    // Prepare and execute query - directly use advance_amount from customers table
    $stmt = $conn->prepare("
        SELECT c.id, c.name, c.telephone, c.nic, c.address, c.whatsapp, c.credit_limit, c.branch,
               c.advance_amount, c.credit_balance
        FROM customers c
        WHERE c.name LIKE ? OR c.telephone LIKE ? OR c.nic LIKE ?
        ORDER BY c.name
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
            'branch' => htmlspecialchars($row['branch'] ?? ''),
            'advance_amount' => (float)$row['advance_amount'],
            'credit_balance' => (float)$row['credit_balance']
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
