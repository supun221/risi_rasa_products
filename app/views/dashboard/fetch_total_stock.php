<?php
require_once '../../../config/databade.php';

$supplier = isset($_GET['supplier']) ? $conn->real_escape_string($_GET['supplier']) : '';
$category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';
$searchQuery = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$startDate = isset($_GET['start_date']) ? $conn->real_escape_string($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? $conn->real_escape_string($_GET['end_date']) : '';

$query = "
    SELECT 
        s.product_name, 
        s.available_stock, 
        s.cost_price, 
        s.wholesale_price, 
        s.max_retail_price, 
        s.barcode, 
        sp.supplier_name
    FROM stock_entries s
    JOIN suppliers sp ON s.supplier_id = sp.supplier_id
    WHERE 1=1
";

if (!empty($supplier)) {
    $query .= " AND s.supplier_id = '$supplier'";
}

if (!empty($category)) {
    $query .= " AND s.product_name IN (SELECT product_name FROM products WHERE category = '$category')";
}

if (!empty($searchQuery)) {
    $query .= " AND (s.product_name LIKE '%$searchQuery%' OR s.barcode LIKE '%$searchQuery%')";
}

if (!empty($startDate)) {
    $query .= " AND s.created_at >= '$startDate'";
}

if (!empty($endDate)) {
    $query .= " AND s.created_at <= '$endDate'";
}

$result = $conn->query($query);
$response = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $response[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
?>
