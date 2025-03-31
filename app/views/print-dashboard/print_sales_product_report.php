<!-- print_sales_product_report.php -->
<?php

require_once '../../models/Database.php';

$category = isset($_GET['category']) ? $db_conn->real_escape_string($_GET['category']) : '';
$barcode = isset($_GET['barcode']) ? $db_conn->real_escape_string($_GET['barcode']) : '';
$startDate = isset($_GET['start_date']) ? $db_conn->real_escape_string($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? $db_conn->real_escape_string($_GET['end_date']) : '';
$issuer = isset($_GET['issuer']) ? $db_conn->real_escape_string($_GET['issuer']) : '';

$query = "
    SELECT 
        p.product_name, 
        p.category, 
        pi.product_barcode, 
        pi.price, 
        SUM(pi.purchase_qty) AS total_qty, 
        AVG(pi.discount_percentage) AS discount_percentage, 
        SUM(pi.subtotal) AS total_subtotal,
        MIN(pi.purchased_date) AS min_date,
        MAX(pi.purchased_date) AS max_date,
        br.issuer AS user,
        br.payment_type
    FROM purchase_items pi
    JOIN products p ON pi.product_name = p.product_name
    JOIN bill_records br ON pi.bill_id = br.bill_id
    WHERE 1=1
";

if (!empty($category)) {
    $query .= " AND p.category = '$category'";
}

if (!empty($barcode)) {
    $query .= " AND pi.product_barcode = '$barcode'";
}

if (!empty($startDate)) {
    $query .= " AND pi.purchased_date >= '$startDate'";
}

if (!empty($endDate)) {
    $query .= " AND pi.purchased_date <= '$endDate'";
}

if (!empty($issuer)) {
    $query .= " AND br.issuer = '$issuer'";
}

$query .= " GROUP BY pi.product_barcode, pi.price, br.issuer, br.payment_type ORDER BY max_date DESC";

$result = $db_conn->query($query);

// Initialize totals
$totalCash = 0;
$totalCard = 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Report (Product Wise)</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .print-button { margin-bottom: 20px; padding: 10px; background: blue; color: white; border: none; cursor: pointer; }
        .print-button:hover { background: #0056b3; }
    </style>
</head>
<body>

    <h2>Sales Report (Product Wise)</h2>
    <p><strong>Report Date Range:</strong> <?= htmlspecialchars($startDate ?: 'N/A') ?> to <?= htmlspecialchars($endDate ?: 'N/A') ?></p>

    <button class="print-button" onclick="window.print()">Print Report</button>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Barcode</th>
                <th>Category</th>
                <th>Price</th>
                <th>Total Qty</th>
                <th>Discount (%)</th>
                <th>Total Subtotal</th>
                <th>Purchased Date</th>
                <th>Issuer</th>
                <!-- <th>Payment Type</th> -->
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                        // Convert subtotal to float to prevent string concatenation issues
                        $subtotal = (float) $row['total_subtotal'];

                        // Calculate cash and card totals
                        if (strtolower($row['payment_type']) === 'cash_payment') {
                            $totalCash += $subtotal;
                        } elseif (strtolower($row['payment_type']) === 'card_payment') {
                            $totalCard += $subtotal;
                        }
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['product_name']) ?></td>
                        <td><?= htmlspecialchars($row['product_barcode']) ?></td>
                        <td><?= htmlspecialchars($row['category']) ?></td>
                        <td><?= htmlspecialchars($row['price']) ?></td>
                        <td><?= htmlspecialchars($row['total_qty']) ?></td>
                        <td><?= htmlspecialchars(number_format($row['discount_percentage'], 2)) ?></td>
                        <td><?= htmlspecialchars(number_format($row['total_subtotal'], 2)) ?></td>
                        <td><?= htmlspecialchars($row['min_date']) ?> to <?= htmlspecialchars($row['max_date']) ?></td>
                        <td><?= htmlspecialchars($row['user'] ?: 'N/A') ?></td>
                        <!-- <td><?= htmlspecialchars($row['payment_type']) ?></td> -->
                    </tr>
                <?php endwhile; ?>

                <!-- Display total cash & card sales -->
                <tr style="font-weight: bold; background: #f0f0f0;">
                    <td colspan="6" style="text-align:right;">Total Cash Sales:</td>
                    <td><?= number_format($totalCash, 2) ?></td>
                    <td colspan="3"></td>
                </tr>
                <tr style="font-weight: bold; background: #f0f0f0;">
                    <td colspan="6" style="text-align:right;">Total Card Sales:</td>
                    <td><?= number_format($totalCard, 2) ?></td>
                    <td colspan="3"></td>
                </tr>

            <?php else: ?>
                <tr><td colspan="10">No sales records found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php $db_conn->close(); ?>

</body>
</html>
