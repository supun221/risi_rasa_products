<?php
require_once '../../../config/databade.php';

// Start the session to access user data
session_start();

// Get the logged-in user's branch
$user_branch = $_SESSION['store'] ?? null;

if (!$user_branch) {
    echo json_encode(['error' => 'User branch not found']);
    exit;
}

$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : null;
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : null;
$top_count = isset($_GET['top_count']) && is_numeric($_GET['top_count']) ? intval($_GET['top_count']) : 10;

// Base query to fetch fast-moving products
$query = "SELECT 
            product_barcode, 
            product_name, 
            SUM(purchase_qty) AS total_sold, 
            MAX(purchased_date) AS last_purchased_date
          FROM purchase_items 
          WHERE branch = ? "; // Filter by user's branch

$params = [$user_branch];

// Apply date filters
if (!empty($from_date) && !empty($to_date)) {
    $query .= " AND purchased_date BETWEEN ? AND ?";
    $params[] = $from_date;
    $params[] = $to_date;
}

$query .= " GROUP BY product_barcode, product_name
            ORDER BY total_sold DESC
            LIMIT ?";
$params[] = $top_count;

// Prepare and execute the query
$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>