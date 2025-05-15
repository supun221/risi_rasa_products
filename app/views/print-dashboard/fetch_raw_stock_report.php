<?php
// Database connection
require_once '../../../config/databade.php';

// Set JSON content type header
header('Content-Type: application/json');

// Initialize the response array
$response = [
    'success' => false,
    'data' => [],
    'message' => ''
];

try {
    // Get filter parameters
    $supplier = isset($_GET['supplier']) ? $conn->real_escape_string($_GET['supplier']) : '';
    $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
    $startDate = isset($_GET['start_date']) ? $conn->real_escape_string($_GET['start_date']) : '';
    $endDate = isset($_GET['end_date']) ? $conn->real_escape_string($_GET['end_date']) : '';
    $branch = isset($_GET['branch']) ? $conn->real_escape_string($_GET['branch']) : '';
    
    // Build the SQL query
    $query = "SELECT * FROM stock_entries_raw WHERE 1=1";
    
    // Add filters
    $params = [];
    $types = "";
    
    if (!empty($supplier)) {
        $query .= " AND supplier_id = ?";
        $params[] = $supplier;
        $types .= "s";
    }
    
    if (!empty($search)) {
        $query .= " AND (product_name LIKE ? OR barcode LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $types .= "ss";
    }
    
    if (!empty($startDate)) {
        $query .= " AND DATE(created_at) >= ?";
        $params[] = $startDate;
        $types .= "s";
    }
    
    if (!empty($endDate)) {
        $query .= " AND DATE(created_at) <= ?";
        $params[] = $endDate;
        $types .= "s";
    }
    
    if (!empty($branch)) {
        $query .= " AND branch = ?";
        $params[] = $branch;
        $types .= "s";
    }
    
    // Order by created_at (newest first)
    $query .= " ORDER BY created_at DESC";
    
    // Prepare and execute the statement
    $stmt = $conn->prepare($query);
    
    // Bind parameters if there are any
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $rawStockData = [];
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // If supplier_id exists, use it as supplier_name for display
            $row['supplier_name'] = $row['supplier_id'];
            $rawStockData[] = $row;
        }
    }
    
    // Set success response
    $response['success'] = true;
    $response['data'] = $rawStockData;
    $stmt->close();
    
} catch (Exception $e) {
    $response['message'] = "Database error: " . $e->getMessage();
    // Log error
    error_log("Error fetching raw stock report: " . $e->getMessage());
}

// Return JSON response
echo json_encode($response);
?>
