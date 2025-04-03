<?php
// Connect to database
require_once '../../../../config/databade.php';
session_start();

// Initialize response array
$response = [
    'success' => false,
    'payments' => [],
    'total' => 0,
    'message' => ''
];

try {
    // Get parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    
    // Calculate offset
    $offset = ($page - 1) * $limit;
    
    // Get rep_id from session (assuming it's stored there)
    $rep_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1; // Default to 1 for testing
    
    // Prepare search condition
    $searchCondition = '';
    $searchParams = [];
    
    if (!empty($search)) {
        $searchCondition = "AND customer_name LIKE ?";
        $searchParams[] = "%$search%";
    }
    
    // First, get total count for pagination
    $query = "SELECT COUNT(*) as total FROM advance_payments WHERE 1=1 $searchCondition";
    
    if (!empty($searchParams)) {
        $stmt = $conn->prepare($query);
        $stmt->bind_param(str_repeat('s', count($searchParams)), ...$searchParams);
    } else {
        $stmt = $conn->prepare($query);
    }
    
    $stmt->execute();
    $countResult = $stmt->get_result();
    $countRow = $countResult->fetch_assoc();
    $totalItems = $countRow['total'];
    
    // Now get the actual data
    $query = "SELECT id, customer_id, customer_name, net_amount, payment_type, advance_bill_number, created_at 
              FROM advance_payments 
              WHERE 1=1 $searchCondition
              ORDER BY created_at DESC 
              LIMIT ? OFFSET ?";
    
    // Prepare statement with proper parameters
    $stmt = $conn->prepare($query);
    
    if (!empty($searchParams)) {
        // Add limit and offset to search params
        $searchParams[] = $limit;
        $searchParams[] = $offset;
        
        // Bind all parameters
        $types = str_repeat('s', count($searchParams) - 2) . 'ii';
        $stmt->bind_param($types, ...$searchParams);
    } else {
        $stmt->bind_param("ii", $limit, $offset);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Fetch payments
    while ($row = $result->fetch_assoc()) {
        $response['payments'][] = [
            'id' => $row['id'],
            'customer_id' => $row['customer_id'],
            'customer_name' => htmlspecialchars($row['customer_name']),
            'net_amount' => (float)$row['net_amount'],
            'payment_type' => $row['payment_type'],
            'advance_bill_number' => $row['advance_bill_number'],
            'created_at' => $row['created_at']
        ];
    }
    
    $response['success'] = true;
    $response['total'] = $totalItems;
    
} catch (Exception $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
