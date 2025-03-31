<?php
require_once '../../../config/databade.php'; // Fixed incorrect filename

// Get category securely
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

// SQL query with proper join
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
";

// Add category filter only if it's provided
$params = [];
$types = "";

if (!empty($category)) {
    $query .= " AND products.category = ?";
    $params[] = $category;
    $types .= "s";
}

// Prepare statement
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Generate HTML content
$html = '
<!DOCTYPE html>
<html>
<head>
    <title>Low Stock Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
     .print-button { margin: 10px; padding: 10px 20px; background-color: blue; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h2>Low Stock Report</h2>
       <button class="print-button" onclick="window.print()">Print Report</button>
    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Low Stock</th>
                <th>Available Stock</th>
                <th>Barcode</th>
                <th>Supplier</th>
            </tr>
        </thead>
        <tbody>';

// Append rows from query results
while ($row = $result->fetch_assoc()) {
    $html .= "
        <tr>
            <td>{$row['product_name']}</td>
            <td>{$row['low_stock']}</td>
            <td>{$row['available_stock']}</td>
            <td>{$row['barcode']}</td>
            <td>{$row['supplier_name']}</td>
        </tr>";
}

$html .= '
        </tbody>
    </table>
</body>
</html>';

// Output the HTML content
echo $html;

// Close resources
$stmt->close();
$conn->close();
