<?php
require_once '../../../config/database.php';

// Get filter parameters
$repId = isset($_GET['rep_id']) ? $conn->real_escape_string($_GET['rep_id']) : '';
$startDate = isset($_GET['start_date']) ? $conn->real_escape_string($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? $conn->real_escape_string($_GET['end_date']) : '';

// Base query for rep payments
$query = "
    SELECT rp.invoice_number, rp.customer_name, rp.amount, rp.payment_method, 
           rp.cheque_num, rp.branch, rp.payment_date, rp.notes,
           s.username as rep_name
    FROM rep_payments rp
    LEFT JOIN signup s ON rp.rep_id = s.id
    WHERE 1=1
";

// Add filters if provided
$params = [];
$types = "";

if (!empty($repId)) {
    $query .= " AND rp.rep_id = ?";
    $params[] = $repId;
    $types .= "i";
}

if (!empty($startDate) && !empty($endDate)) {
    $query .= " AND DATE(rp.payment_date) BETWEEN ? AND ?";
    $params[] = $startDate;
    $params[] = $endDate;
    $types .= "ss";
}

$query .= " ORDER BY rp.payment_date DESC";

// Prepare and execute the statement
$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Initialize totals
$totalAmount = 0;

// Filter descriptions for title
$repName = "All Reps";
if (!empty($repId)) {
    $repQuery = "SELECT username FROM signup WHERE id = ?";
    $repStmt = $conn->prepare($repQuery);
    $repStmt->bind_param("i", $repId);
    $repStmt->execute();
    $repResult = $repStmt->get_result();
    if ($repRow = $repResult->fetch_assoc()) {
        $repName = $repRow['username'];
    }
    $repStmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Rep Payments Report</title>
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
    <h2>Rep Payments Report</h2>
    <p><strong>Rep:</strong> <?php echo htmlspecialchars($repName); ?></p>
    <p><strong>Date Range:</strong> <?php echo htmlspecialchars($startDate ?: 'All Time'); ?> to <?php echo htmlspecialchars($endDate ?: 'Present'); ?></p>
    <button class="print-button" onclick="printReport()">Print Report</button>
    <table>
        <thead>
            <tr>
                <th>Invoice Number</th>
                <th>Customer Name</th>
                <th>Amount</th>
                <th>Payment Method</th>
                <th>Cheque Number</th>
                <th>Rep</th>
                <th>Branch</th>
                <th>Date</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $totalAmount += floatval($row['amount']);
                    echo "<tr>
                        <td>" . htmlspecialchars($row['invoice_number']) . "</td>
                        <td>" . htmlspecialchars($row['customer_name']) . "</td>
                        <td>" . number_format($row['amount'], 2) . "</td>
                        <td>" . htmlspecialchars($row['payment_method']) . "</td>
                        <td>" . htmlspecialchars($row['cheque_num'] ?? 'N/A') . "</td>
                        <td>" . htmlspecialchars($row['rep_name']) . "</td>
                        <td>" . htmlspecialchars($row['branch']) . "</td>
                        <td>" . htmlspecialchars($row['payment_date']) . "</td>
                        <td>" . htmlspecialchars($row['notes'] ?? '') . "</td>
                    </tr>";
                }
            } else {
                echo '<tr><td colspan="9">No payment records found.</td></tr>';
            } ?>
        </tbody>
        <tfoot>
            <tr class="total">
                <td colspan="2">Total Amount</td>
                <td><?php echo number_format($totalAmount, 2); ?></td>
                <td colspan="6"></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
