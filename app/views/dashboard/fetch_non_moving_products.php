<?php
require_once '../../../config/databade.php'; // Ensure correct path to your DB connection file

// Start the session to access user data
session_start();

// Get the logged-in user's branch
$user_branch = $_SESSION['store'] ?? null;

if (!$user_branch) {
    echo json_encode(['error' => 'User branch not found']);
    exit;
}

header('Content-Type: application/json');

$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : '';
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : '';
$top_count = isset($_GET['top_count']) ? intval($_GET['top_count']) : 10;

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

// Base query to fetch non-moving products
$query = "
    SELECT 
        se.barcode AS product_barcode,
        se.product_name,
        se.available_stock,
        COALESCE(MAX(pi.purchased_date), 'Never Sold') AS last_sold_date
    FROM stock_entries se
    LEFT JOIN purchase_items pi ON se.barcode = pi.product_barcode
    WHERE se.available_stock > 0
    AND se.branch = ?"; // Filter by user's branch

$params = [$user_branch];

// Apply date filters
if (!empty($from_date) && !empty($to_date)) {
    $query .= " AND (pi.purchased_date BETWEEN ? AND ? OR pi.purchased_date IS NULL)";
    $params[] = $from_date;
    $params[] = $to_date;
}

$query .= "
    GROUP BY se.barcode, se.product_name, se.available_stock
    ORDER BY last_sold_date ASC
    LIMIT ?";
$params[] = $top_count;

// Prepare and execute the query
$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    echo json_encode(['error' => 'Query execution failed']);
    exit;
}

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = [
        'product_barcode' => $row['product_barcode'],
        'product_name' => $row['product_name'],
        'available_stock' => $row['available_stock'],
        'last_sold_date' => $row['last_sold_date'],
    ];
}

echo json_encode($products);
$conn->close();
?>