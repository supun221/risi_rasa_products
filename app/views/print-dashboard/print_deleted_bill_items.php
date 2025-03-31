<?php
require_once '../../models/Database.php';

$user = isset($_GET['user']) ? $db_conn->real_escape_string($_GET['user']) : '';
$startDate = isset($_GET['start_date']) ? $db_conn->real_escape_string($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? $db_conn->real_escape_string($_GET['end_date']) : '';

// Query to fetch deleted bill items
$query = "
    SELECT 
        bill_id, 
        deleted_by, 
        deleted_date, 
        item_name, 
        barcode, 
        unit_price, 
        quantity, 
        discount, 
        total_price, 
        bill_type
    FROM deleted_bill_items
    WHERE 1=1
";

if (!empty($user)) {
    $query .= " AND deleted_by = '$user'";
}

if (!empty($startDate)) {
    $query .= " AND deleted_date >= '$startDate'";
}

if (!empty($endDate)) {
    $query .= " AND deleted_date <= '$endDate'";
}

$query .= " ORDER BY deleted_date DESC, bill_id DESC";

$result = $db_conn->query($query);

$bills = [];

// Group data by bill_id
while ($row = $result->fetch_assoc()) {
    $bill_id = $row['bill_id'];

    if (!isset($bills[$bill_id])) {
        $bills[$bill_id] = [
            'bill_id' => $row['bill_id'],
            'deleted_by' => $row['deleted_by'],
            'deleted_date' => $row['deleted_date'],
            'bill_type' => $row['bill_type'],
            'purchase_items' => []
        ];
    }

    // Append item details to the bill
    $bills[$bill_id]['purchase_items'][] = [
        'item_name' => $row['item_name'],
        'barcode' => $row['barcode'],
        'unit_price' => $row['unit_price'],
        'quantity' => $row['quantity'],
        'discount' => $row['discount'],
        'total_price' => $row['total_price']
    ];
}

// Generate HTML for the printable page
$html = '
<!DOCTYPE html>
<html>
<head>
    <title>Deleted Bill Items Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        @media print {
            body { margin: 0; padding: 0; }
            table { width: 100%; }
            th, td { padding: 6px; }
        }
    .print-button { margin: 10px; padding: 10px 20px; background-color: blue; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h2>Deleted Bill Items Report</h2>
     <button class="print-button" onclick="window.print()">Print Report</button>
    <table>
        <thead>
            <tr>
                <th>Bill ID</th>
                <th>Deleted By</th>
                <th>Deleted Date</th>
                <th>Bill Type</th>
                <th>Products</th>
                <th>Barcodes</th>
                <th>Prices</th>
                <th>Quantities</th>
                <th>Discounts</th>
                <th>Totals</th>
            </tr>
        </thead>
        <tbody>';

foreach ($bills as $bill) {
    // Group products into single row
    $productDetails = implode("<br>", array_map(fn($item) => $item['item_name'], $bill['purchase_items']));
    $barcodeDetails = implode("<br>", array_map(fn($item) => $item['barcode'], $bill['purchase_items']));
    $priceDetails = implode("<br>", array_map(fn($item) => number_format($item['unit_price'], 2), $bill['purchase_items']));
    $qtyDetails = implode("<br>", array_map(fn($item) => $item['quantity'], $bill['purchase_items']));
    $discountDetails = implode("<br>", array_map(fn($item) => number_format($item['discount'], 2), $bill['purchase_items']));
    $subtotalDetails = implode("<br>", array_map(fn($item) => number_format($item['total_price'], 2), $bill['purchase_items']));

    $html .= "
        <tr>
            <td>{$bill['bill_id']}</td>
            <td>{$bill['deleted_by']}</td>
            <td>{$bill['deleted_date']}</td>
            <td>{$bill['bill_type']}</td>
            <td>{$productDetails}</td>
            <td>{$barcodeDetails}</td>
            <td>{$priceDetails}</td>
            <td>{$qtyDetails}</td>
            <td>{$discountDetails}</td>
            <td>{$subtotalDetails}</td>
        </tr>";
}

$html .= '</tbody></table></body></html>';

// Output the HTML
echo $html;

$db_conn->close();
?>