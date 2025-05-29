<?php
// Connect to database
require_once '../../../../config/databade.php';

// Initialize response
$response = [
    'success' => true,
    'message' => '',
    'customers' => []
];

try {
    // Get search parameters
    $searchTerm = isset($_GET['term']) ? $_GET['term'] : '';
    $routeId = isset($_GET['route']) ? $_GET['route'] : '';
    
    // Prepare SQL query
    $query = "
        SELECT c.id, c.name, c.telephone, c.nic, c.address, c.whatsapp, c.credit_limit, c.credit_balance,
               COALESCE(ap.net_amount, 0) as advance_amount, r.name as route_name
        FROM customers c
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
        WHERE 1=1
    ";
    
    $params = [];
    $types = "";
    
    // Add search term condition if provided
    if (!empty($searchTerm)) {
        $searchParam = "%$searchTerm%";
        $query .= " AND (c.name LIKE ? OR c.telephone LIKE ? OR c.nic LIKE ?)";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= "sss";
    }
    
    // Add route filter condition if provided
    if (!empty($routeId)) {
        $query .= " AND c.route_id = ?";
        $params[] = $routeId;
        $types .= "i";
    }
    
    // Limit results and order by name
    $query .= " ORDER BY c.name LIMIT 50";
    
    // Prepare and execute statement
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Fetch and format results
    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'telephone' => $row['telephone'],
            'nic' => $row['nic'],
            'address' => $row['address'],
            'whatsapp' => $row['whatsapp'],
            'credit_limit' => number_format((float)$row['credit_limit'], 2),
            'credit_balance' => number_format((float)$row['credit_balance'], 2),
            'advance_amount' => (float)$row['advance_amount'],
            'route_name' => $row['route_name']
        ];
    }
    
    $response['customers'] = $customers;
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
