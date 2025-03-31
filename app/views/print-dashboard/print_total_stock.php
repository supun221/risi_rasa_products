<?php

require_once '../../../config/databade.php'; // Ensure correct filename

// Get filters from URL parameters
$supplier = isset($_GET['supplier']) ? $conn->real_escape_string($_GET['supplier']) : '';
$category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';
$searchQuery = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$startDate = isset($_GET['start_date']) ? $conn->real_escape_string($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? $conn->real_escape_string($_GET['end_date']) : '';

// Get Supplier Name
$supplierName = "All"; // Default value if no supplier is selected
if (!empty($supplier)) {
    $supplierQuery = "SELECT supplier_name FROM suppliers WHERE supplier_id = '$supplier' LIMIT 1";
    $supplierResult = $conn->query($supplierQuery);
    if ($supplierResult && $supplierResult->num_rows > 0) {
        $supplierRow = $supplierResult->fetch_assoc();
        $supplierName = $supplierRow['supplier_name'];
    }
}

// Construct the SQL query for stock data
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

// Apply filters dynamically
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

echo '<!DOCTYPE html>
<html>
<head>
    <title>Total Stock Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .print-button { margin: 10px; padding: 10px 20px; background-color: blue; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h2>Total Stock Report</h2>
    <button class="print-button" onclick="window.print()">Print Report</button>
    <p>Supplier: ' . $supplierName . '</p>
    <p>Category: ' . ($category ? $category : "All") . '</p>
    <p>Search: ' . ($searchQuery ? $searchQuery : "None") . '</p>
    <p>Date Range: ' . ($startDate ? $startDate : "Start N/A") . ' to ' . ($endDate ? $endDate : "End N/A") . '</p>
    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Available Stock</th>
                <th>Cost Price</th>
                <th>Wholesale Price</th>
                <th>Retail Price</th>
                <th>Barcode</th>
                <th>Supplier</th>
            </tr>
        </thead>
        <tbody>';

while ($row = $result->fetch_assoc()) {
    echo "
        <tr>
            <td>{$row['product_name']}</td>
            <td>{$row['available_stock']}</td>
            <td>{$row['cost_price']}</td>
            <td>{$row['wholesale_price']}</td>
            <td>{$row['max_retail_price']}</td>
            <td>{$row['barcode']}</td>
            <td>{$row['supplier_name']}</td>
        </tr>";
}

echo '</tbody></table></body></html>';

$conn->close();
?>