<?php
require_once '../../../config/databade.php';
// Start session to get user's branch
session_start();
$user_branch = $_SESSION['store'] ?? null;

if (!$user_branch) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'User branch not found. Please log in.']);
    exit;
}
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Securely get input parameters
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

// Base query with branch filter
$query = "
    SELECT 
        stock_entries.product_name, 
        stock_entries.low_stock, 
        stock_entries.available_stock, 
        stock_entries.barcode, 
        suppliers.supplier_name 
    FROM 
        stock_entries
    JOIN 
        suppliers ON stock_entries.supplier_id = suppliers.supplier_id
    JOIN 
        products ON stock_entries.itemcode = products.item_code
    WHERE 
        stock_entries.low_stock > stock_entries.available_stock
        AND stock_entries.branch = ?
";

// Filter parameters
$params = [$user_branch]; // Start with branch parameter
$types = "s"; // Start with string type for branch


if (!empty($category)) {
    $query .= " AND products.category = ?";
    $params[] = $category;
    $types .= "s";
}

// Append search filter if provided
if (!empty($searchQuery)) {
    $query .= " AND (stock_entries.product_name LIKE ? OR stock_entries.barcode LIKE ?)";
    $searchPattern = "%$searchQuery%";
    $params[] = $searchPattern;
    $params[] = $searchPattern;
    $types .= "ss";
}

// Prepare statement
$stmt = $conn->prepare($query);

if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $response = [];
    while ($row = $result->fetch_assoc()) {
        $response[] = $row;
    }

    $stmt->close();
} else {
    $response = ["error" => "Query preparation failed: " . $conn->error];
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
?>
