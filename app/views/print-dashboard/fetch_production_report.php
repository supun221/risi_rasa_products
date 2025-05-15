<?php
// Database connection
require_once '../../../config/databade.php'; // Make sure the path is correct

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
    $search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
    $startDate = isset($_GET['start_date']) ? $conn->real_escape_string($_GET['start_date']) : '';
    $endDate = isset($_GET['end_date']) ? $conn->real_escape_string($_GET['end_date']) : '';
    $branch = isset($_GET['branch']) ? $conn->real_escape_string($_GET['branch']) : '';
    
    // Build the SQL query - filter for supplier_id = 'self_001' to get production items
    $query = "SELECT * FROM stock_entries 
              WHERE supplier_id = 'self_001'";
    
    // Add filters
    $params = [];
    $types = "";
    
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
    
    $productionData = [];
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $productionData[] = $row;
        }
    }
    
    // Set success response
    $response['success'] = true;
    $response['data'] = $productionData;
    $stmt->close();
    
} catch (Exception $e) {
    $response['message'] = "Database error: " . $e->getMessage();
    // Log error
    error_log("Error fetching production report: " . $e->getMessage());
}

// Return JSON response
echo json_encode($response);
?>
