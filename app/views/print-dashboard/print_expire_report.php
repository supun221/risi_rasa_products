<?php

require_once '../../../config/databade.php';

// Get filter parameters
$startDate = isset($_GET['start_date']) ? $conn->real_escape_string($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? $conn->real_escape_string($_GET['end_date']) : '';

// SQL Query
$query = "
    SELECT 
        s.barcode, 
        s.product_name, 
        s.available_stock, 
        sup.supplier_name, 
        s.expire_date
    FROM stock_entries s
    LEFT JOIN suppliers sup ON s.supplier_id = sup.supplier_id
    WHERE 1=1
";

if (!empty($startDate) && !empty($endDate)) {
    $query .= " AND s.expire_date BETWEEN '$startDate' AND '$endDate'";
}

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expire Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .print-button { margin-bottom: 20px; padding: 10px; background: blue; color: white; border: none; cursor: pointer; }
        .print-button:hover { background: #0056b3; }
    </style>
</head>
<body>

    <h2>Expire Report</h2>
    <p><strong>Report Date Range:</strong> <?= htmlspecialchars($startDate ?: 'N/A') ?> to <?= htmlspecialchars($endDate ?: 'N/A') ?></p>

    <button class="print-button" onclick="window.print()">Print Report</button>

    <table>
        <thead>
            <tr>
                <th>Barcode</th>
                <th>Product Name</th>
                <th>Available Qty</th>
                <th>Supplier</th>
                <th>Expire Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['barcode']) ?></td>
                        <td><?= htmlspecialchars($row['product_name']) ?></td>
                        <td><?= htmlspecialchars($row['available_stock']) ?></td>
                        <td><?= htmlspecialchars($row['supplier_name']) ?></td>
                        <td><?= htmlspecialchars($row['expire_date']) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5">No expired items found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php $conn->close(); ?>

</body>
</html>
