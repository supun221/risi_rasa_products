<?php
require_once '../../../vendor/autoload.php';
require_once '../../models/Database.php';

$startDate = isset($_GET['start_date']) ? $db_conn->real_escape_string($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? $db_conn->real_escape_string($_GET['end_date']) : '';

// Base SQL query
$query = "
    SELECT 
        b.bill_id, 
        b.customer_id, 
        b.gross_amount, 
        b.net_amount, 
        b.discount_amount, 
        b.bill_date,
        p.product_name, 
        p.purchase_qty, 
        p.price, 
        p.discount_percentage, 
        p.subtotal
    FROM 
        bill_records AS b
    INNER JOIN 
        purchase_items AS p 
    ON 
        b.bill_id = p.bill_id
    WHERE 1=1
";

// Apply date filtering only if a date is selected
if (!empty($startDate) && !empty($endDate)) {
    $query .= " AND b.bill_date BETWEEN '$startDate' AND '$endDate'";
}

// Order results by bill_id
$query .= " ORDER BY b.bill_id DESC";

$result = $db_conn->query($query);

$bills = [];

// Group data by bill_id
while ($row = $result->fetch_assoc()) {
    $bill_id = $row['bill_id'];

    if (!isset($bills[$bill_id])) {
        $bills[$bill_id] = [
            'bill_id' => $row['bill_id'],
            'customer_id' => $row['customer_id'],
            'gross_amount' => $row['gross_amount'],
            'net_amount' => $row['net_amount'],
            'discount_amount' => $row['discount_amount'],
            'bill_date' => $row['bill_date'],
            'purchase_items' => []
        ];
    }

    // Append product details
    $bills[$bill_id]['purchase_items'][] = [
        'product_name' => $row['product_name'],
        'purchase_qty' => $row['purchase_qty'],
        'price' => $row['price'],
        'discount_percentage' => $row['discount_percentage'],
        'subtotal' => $row['subtotal']
    ];
}

// Generate Printable HTML
echo '<html><head>
    <title>Sales Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .print-button { margin: 10px; padding: 10px 20px; background-color: blue; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h2>Sales Report</h2>
    <button class="print-button" onclick="window.print()">Print Report</button>
    <table>
        <thead>
            <tr>
                <th>Bill ID</th>
                <th>Customer ID</th>
                <th>Bill Date</th>
                <th>Products</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Discount %</th>
                <th>Subtotal</th>
                <th>Gross Amount</th>
                <th>Net Amount</th>
            </tr>
        </thead>
        <tbody>';

foreach ($bills as $bill) {
    $productDetails = implode("<br>", array_map(fn($item) => $item['product_name'], $bill['purchase_items']));
    $priceDetails = implode("<br>", array_map(fn($item) => $item['price'], $bill['purchase_items']));
    $qtyDetails = implode("<br>", array_map(fn($item) => $item['purchase_qty'], $bill['purchase_items']));
    $discountDetails = implode("<br>", array_map(fn($item) => $item['discount_percentage'] . '%', $bill['purchase_items']));
    $subtotalDetails = implode("<br>", array_map(fn($item) => $item['subtotal'], $bill['purchase_items']));

    echo "
        <tr>
            <td>{$bill['bill_id']}</td>
            <td>{$bill['customer_id']}</td>
            <td>{$bill['bill_date']}</td>
            <td>{$productDetails}</td>
            <td>{$priceDetails}</td>
            <td>{$qtyDetails}</td>
            <td>{$discountDetails}</td>
            <td>{$subtotalDetails}</td>
            <td>{$bill['gross_amount']}</td>
            <td>{$bill['net_amount']}</td>
        </tr>";
}

echo '</tbody></table></body></html>';

$db_conn->close();
?>