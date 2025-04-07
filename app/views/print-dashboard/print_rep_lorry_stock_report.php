<?php
require_once '../../../config/databade.php';

// Get filter parameters
$repId = isset($_GET['rep_id']) ? $conn->real_escape_string($_GET['rep_id']) : '';
$startDate = isset($_GET['start_date']) ? $conn->real_escape_string($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? $conn->real_escape_string($_GET['end_date']) : '';
$barcode = isset($_GET['barcode']) ? $conn->real_escape_string($_GET['barcode']) : '';

// Base query for rep lorry stock - removed join to products table
$query = "
    SELECT ls.id, ls.product_name, ls.barcode, ls.quantity, ls.unit_price, 
           ls.status, ls.date_added, s.username as rep_name
    FROM lorry_stock ls
    LEFT JOIN signup s ON ls.rep_id = s.id
    WHERE ls.quantity > 0
";

// Add filters if provided
$params = [];
$types = "";

if (!empty($repId)) {
    $query .= " AND ls.rep_id = ?";
    $params[] = $repId;
    $types .= "i";
}

if (!empty($startDate) && !empty($endDate)) {
    $query .= " AND DATE(ls.date_added) BETWEEN ? AND ?";
    $params[] = $startDate;
    $params[] = $endDate;
    $types .= "ss";
}

if (!empty($barcode)) {
    $query .= " AND ls.barcode LIKE ?";
    $params[] = "%$barcode%";
    $types .= "s";
}

$query .= " ORDER BY ls.rep_id, ls.product_name";

// Prepare and execute the statement
$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Initialize totals
$totalQuantity = 0;
$totalValue = 0;

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
    <title>Rep Lorry Stock Report</title>
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
    <h2>Rep Lorry Stock Report</h2>
    <p><strong>Rep:</strong> <?php echo htmlspecialchars($repName); ?></p>
    <p><strong>Date Range:</strong> <?php echo htmlspecialchars($startDate ?: 'All Time'); ?> to <?php echo htmlspecialchars($endDate ?: 'Present'); ?></p>
    <?php if (!empty($barcode)): ?>
    <p><strong>Barcode Filter:</strong> <?php echo htmlspecialchars($barcode); ?></p>
    <?php endif; ?>
    <button class="print-button" onclick="printReport()">Print Report</button>
    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Barcode</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total Value</th>
                <th>Rep</th>
                <th>Status</th>
                <th>Date Added</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $itemValue = floatval($row['quantity']) * floatval($row['unit_price']);
                    $totalQuantity += intval($row['quantity']);
                    $totalValue += $itemValue;
                    
                    echo "<tr>
                        <td>" . htmlspecialchars($row['product_name']) . "</td>
                        <td>" . htmlspecialchars($row['barcode'] ?? 'N/A') . "</td>
                        <td>" . intval($row['quantity']) . "</td>
                        <td>" . number_format($row['unit_price'], 2) . "</td>
                        <td>" . number_format($itemValue, 2) . "</td>
                        <td>" . htmlspecialchars($row['rep_name']) . "</td>
                        <td>" . htmlspecialchars($row['status']) . "</td>
                        <td>" . htmlspecialchars($row['date_added']) . "</td>
                    </tr>";
                }
            } else {
                echo '<tr><td colspan="8">No lorry stock records found.</td></tr>';
            } ?>
        </tbody>
        <tfoot>
            <tr class="total">
                <td colspan="2">Total</td>
                <td><?php echo $totalQuantity; ?></td>
                <td></td>
                <td><?php echo number_format($totalValue, 2); ?></td>
                <td colspan="3"></td>
            </tfoot>
    </table>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
