<?php
// Connect to database
require_once '../../../../config/databade.php';

// Initialize response
$response = [
    'success' => false,
    'message' => '',
    'customers' => []
];

// Get search term
$searchTerm = isset($_GET['term']) ? trim($_GET['term']) : '';

if (!empty($searchTerm)) {
    try {
        // Search for customers by name, telephone, or NIC
        $searchPattern = '%' . $searchTerm . '%';
        $query = "
            SELECT c.id, c.name, c.telephone, c.nic, c.credit_balance, c.address, 
                   r.name as route_name, 
                   COALESCE(ap.net_amount, 0) as advance_amount
            FROM customers c
            LEFT JOIN routes r ON c.route_id = r.id
            LEFT JOIN (
                SELECT customer_id, net_amount 
                FROM advance_payments 
                WHERE id IN (
                    SELECT MAX(id) 
                    FROM advance_payments 
                    GROUP BY customer_id
                )
            ) ap ON c.id = ap.customer_id
            WHERE c.name LIKE ? OR c.telephone LIKE ? OR c.nic LIKE ?
            ORDER BY c.name
            LIMIT 10
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $searchPattern, $searchPattern, $searchPattern);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $response['customers'][] = $row;
            }
            $response['success'] = true;
        } else {
            $response['message'] = 'No customers found matching your search.';
        }
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Please provide a search term.';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
