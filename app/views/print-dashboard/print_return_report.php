<?php

require_once '../../../config/databade.php';

// Ensure $conn exists
if (!isset($conn)) {
    die("Database connection not found.");
}

// Sanitize input
$startDate = isset($_GET['start_date']) ? $conn->real_escape_string($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? $conn->real_escape_string($_GET['end_date']) : '';

// Query to get return records
$query = "
    SELECT 
        r.id,
        s.supplier_name,
        r.return_date,
        r.total_amount
    FROM returns r
    JOIN suppliers s ON r.supplier_id = s.supplier_id
    WHERE 1=1
";

if (!empty($startDate)) {
    $query .= " AND r.return_date >= '$startDate'";
}
if (!empty($endDate)) {
    $query .= " AND r.return_date <= '$endDate'";
}

$result = $conn->query($query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Return Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .section { margin-bottom: 20px; }
        .print-button { margin-bottom: 10px; padding: 10px 20px; font-size: 16px; cursor: pointer; background-color: blue; color: white; border: none; border-radius: 5px; display: block; margin: 0 auto; }
        .print-button:hover { background-color: #0056b3; }
        @media print {
            .print-button { display: none; }
        }
    </style>
    <script>
        function printReport() {
            window.print();
        }
    </script>
</head>
<body>
    <h4 class="report-headline" style="text-align:center; font-size:2.5em; font-weight:bold; color:#2980b9;">Eggland Super</h4>
    <h4 class="report-title" style="text-align:center; font-size:1.2em; color:#004080; font-weight:bold;">Return Report</h4>
    <button class="print-button" onclick="printReport()">Print Report</button>

    <?php if ($result && $result->num_rows > 0) {
        while ($return = $result->fetch_assoc()) {
            $returnId = $return['id']; ?>
            <div class="section">
                <h3>Return ID: <?php echo $returnId; ?></h3>
                <p><strong>Supplier:</strong> <?php echo htmlspecialchars($return['supplier_name']); ?></p>
                <p><strong>Return Date:</strong> <?php echo htmlspecialchars($return['return_date']); ?></p>
                <p><strong>Total Amount:</strong> <?php echo number_format($return['total_amount'], 2); ?></p>

                <h4>Returned Items</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Barcode</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Cost Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $itemsQuery = "
                            SELECT barcode, product_name, quantity, our_price, total
                            FROM return_items
                            WHERE return_id = $returnId
                        ";
                        $itemsResult = $conn->query($itemsQuery);
                        if ($itemsResult && $itemsResult->num_rows > 0) {
                            while ($item = $itemsResult->fetch_assoc()) {
                                echo "<tr>
                                    <td>" . htmlspecialchars($item['barcode']) . "</td>
                                    <td>" . htmlspecialchars($item['product_name']) . "</td>
                                    <td>" . htmlspecialchars($item['quantity']) . "</td>
                                    <td>" . number_format($item['our_price'], 2) . "</td>
                                    <td>" . number_format($item['total'], 2) . "</td>
                                </tr>";
                            }
                        } else {
                            echo '<tr><td colspan="5">No items found for this return.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        <?php }
    } else {
        echo '<p>No return records found.</p>';
    } ?>
</body>
</html>
<?php
$conn->close();
?>