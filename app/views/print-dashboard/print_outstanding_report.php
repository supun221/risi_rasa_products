<?php

require_once '../../../config/databade.php';

// Get filter parameters
$startDate = isset($_GET['start_date']) ? $conn->real_escape_string($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? $conn->real_escape_string($_GET['end_date']) : '';

// SQL Query to Fetch Outstanding Balances within Date Range
$query = "
    SELECT supplier_name, telephone_no, company, credit_balance, created_at
    FROM suppliers
    WHERE credit_balance > 0.00
";

if (!empty($startDate) && !empty($endDate)) {
    $query .= " AND created_at BETWEEN '$startDate' AND '$endDate'";
}

$result = $conn->query($query);

// Calculate Total Outstanding Amount
$totalOutstanding = 0;

?>
<!DOCTYPE html>
<html>
<head>
    <title>Outstanding Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { font-weight: bold; background: #ddd; }
        .print-button { margin-bottom: 10px; padding: 10px 20px; font-size: 16px; cursor: pointer; background-color:blue; color: white; border: none; border-radius: 5px; display: block; margin: 0 auto; }
        .print-button:hover { background-color: #0056b3; }
        @media print {
            .print-button { display: none; }
        }
    </style>
    <script>
        function printReport() {
            document.querySelector('.print-button').style.display = 'none';
            window.print();
            document.querySelector('.print-button').style.display = 'block';
        }
    </script>
</head>
<body>
    <h2>Outstanding Report</h2>
    <p><strong>Report Date Range:</strong> <?php echo htmlspecialchars($startDate) ?: 'N/A'; ?> to <?php echo htmlspecialchars($endDate) ?: 'N/A'; ?></p>
    <button class="print-button" onclick="printReport()">Print Report</button>
    <table>
        <thead>
            <tr>
                <th>Supplier Name</th>
                <th>Phone</th>
                <th>Company</th>
                <th>Credit Balance</th>
                <th>Created Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>" . htmlspecialchars($row['supplier_name']) . "</td>
                        <td>" . htmlspecialchars($row['telephone_no']) . "</td>
                        <td>" . htmlspecialchars($row['company']) . "</td>
                        <td>" . number_format($row['credit_balance'], 2) . "</td>
                        <td>" . htmlspecialchars($row['created_at']) . "</td>
                    </tr>";
                    $totalOutstanding += $row['credit_balance'];
                }
            } else {
                echo '<tr><td colspan="5">No outstanding balances found.</td></tr>';
            } ?>
        </tbody>
        <tfoot>
            <tr class="total">
                <td colspan="3">Total Outstanding</td>
                <td colspan="2"><?php echo number_format($totalOutstanding, 2); ?></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
<?php
$conn->close();
?>
