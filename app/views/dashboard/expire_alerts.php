<?php
// Database connection
require_once '../../../config/databade.php';

header('Content-Type: application/json');

// Check connection
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}
// Start session to retrieve user's branch
session_start();
$user_branch = $_SESSION['store'] ?? null;

if (!$user_branch) {
    echo json_encode(['error' => 'User branch not found']);
    exit;
}

// Get filters
$search = $_GET['search'] ?? '';
$fromDate = $_GET['from_date'] ?? null;
$toDate = $_GET['to_date'] ?? null;

// Base SQL query with JOIN to get supplier_name and branch filter
$query = "
    SELECT 
        s.barcode, 
        s.product_name, 
        s.available_stock, 
        sup.supplier_name, 
        s.expire_date
    FROM stock_entries s
    LEFT JOIN suppliers sup ON s.supplier_id = sup.supplier_id
    WHERE s.branch = ?";

$params = [$user_branch]; // Initialize params with the user's branch

// Apply search filter
if (!empty($search)) {
    $query .= " AND (LOWER(s.barcode) LIKE ? OR LOWER(s.product_name) LIKE ?)";
    $searchTerm = "%" . strtolower($search) . "%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// Apply date filters
if (!empty($fromDate) && !empty($toDate)) {
    $query .= " AND s.expire_date BETWEEN ? AND ?";
    $params[] = $fromDate;
    $params[] = $toDate;
}

// Prepare and execute query
$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Fetch results
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Output results
echo json_encode($data);

$conn->close();
