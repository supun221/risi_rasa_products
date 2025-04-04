<?php
require_once '../../../../config/databade.php';  // Keeping the typo as it's in the original code
session_start();

// Get rep_id from session
$rep_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;

// Get request parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$product = isset($_GET['product']) ? $_GET['product'] : '';

// Items per page
$items_per_page = 20;
$offset = ($page - 1) * $items_per_page;

// Build the SQL query - removed lt.notes which doesn't exist
$sql = "
    SELECT lt.id, lt.transaction_type, lt.quantity, lt.reason,
           lt.customer_name, lt.price, lt.total_amount, lt.transaction_date,
           lt.barcode, se.product_name, se.itemcode
    FROM lorry_transactions lt
    LEFT JOIN stock_entries se ON lt.stock_entry_id = se.id
    WHERE lt.rep_id = ?
";

$params = [$rep_id];
$param_types = "i";

// Add date filters
if ($start_date && $end_date) {
    $sql .= " AND lt.transaction_date BETWEEN ? AND DATE_ADD(?, INTERVAL 1 DAY)";
    $params[] = $start_date;
    $params[] = $end_date;
    $param_types .= "ss";
}

// Add transaction type filter
if ($type) {
    $sql .= " AND lt.transaction_type = ?";
    $params[] = $type;
    $param_types .= "s";
}

// Add product filter
if ($product) {
    $sql .= " AND (se.product_name LIKE ? OR se.itemcode LIKE ?)";
    $params[] = "%$product%";
    $params[] = "%$product%";
    $param_types .= "ss";
}

// Get total count for pagination
$count_sql = str_replace("SELECT lt.id, lt.transaction_type, lt.quantity, lt.reason,
           lt.customer_name, lt.price, lt.total_amount, lt.transaction_date,
           lt.barcode, se.product_name, se.itemcode", "SELECT COUNT(*) as total", $sql);

try {
    // Get total count
    $stmt = $conn->prepare($count_sql);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $total_count = $result->fetch_assoc()['total'];
    
    // Add order and limit for paginated data
    $sql .= " ORDER BY lt.transaction_date DESC LIMIT ? OFFSET ?";
    $params[] = $items_per_page;
    $params[] = $offset;
    $param_types .= "ii";
    
    // Get paginated data
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Fetch all rows
    $movements = [];
    while ($row = $result->fetch_assoc()) {
        $movements[] = $row;
    }
    
    // Calculate pagination data
    $total_pages = ceil($total_count / $items_per_page);
    $from = ($total_count > 0) ? $offset + 1 : 0;
    $to = min($offset + $items_per_page, $total_count);
    
    // Prepare response
    $response = [
        'success' => true,
        'movements' => $movements,
        'pagination' => [
            'total' => $total_count,
            'per_page' => $items_per_page,
            'current_page' => $page,
            'last_page' => $total_pages,
            'from' => $from,
            'to' => $to
        ]
    ];
    
    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    
} catch (Exception $e) {
    // Handle error
    $response = [
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ];
    
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>
