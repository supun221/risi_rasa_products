<?php
require_once '../../../config/databade.php';

// Get filter parameters
$repId = isset($_GET['rep_id']) ? $conn->real_escape_string($_GET['rep_id']) : '';
$startDate = isset($_GET['start_date']) ? $conn->real_escape_string($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? $conn->real_escape_string($_GET['end_date']) : '';
$barcode = isset($_GET['barcode']) ? $conn->real_escape_string($_GET['barcode']) : '';

// Base query for rep sales items - join with lorry_stock to get barcode
$query = "
    SELECT ps.invoice_number, psi.product_name, ls.barcode,
           psi.quantity, psi.free_quantity, psi.unit_price, 
           psi.discount_percent, psi.subtotal, s.username as rep_name,
           ps.sale_date
    FROM pos_sales ps
    JOIN pos_sale_items psi ON ps.id = psi.sale_id
    LEFT JOIN lorry_stock ls ON psi.lorry_stock_id = ls.id
    LEFT JOIN signup s ON ps.rep_id = s.id
    WHERE 1=1
";

// Add filters if provided
$params = [];
$types = "";

if (!empty($repId)) {
    $query .= " AND ps.rep_id = ?";
    $params[] = $repId;
    $types .= "i";
}

if (!empty($startDate) && !empty($endDate)) {
    $query .= " AND DATE(ps.sale_date) BETWEEN ? AND ?";
    $params[] = $startDate;
    $params[] = $endDate;
    $types .= "ss";
}

if (!empty($barcode)) {
    $query .= " AND ls.barcode LIKE ?";
    $params[] = "%$barcode%";
    $types .= "s";
}

$query .= " ORDER BY ps.sale_date DESC, ps.invoice_number";

// Prepare and execute the statement
$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Initialize totals
$totalQuantity = 0;
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
    <title>Rep Sales Items Report</title>
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
    <h2>Rep Sales Items Report</h2>
    <p><strong>Rep:</strong> <?php echo htmlspecialchars($repName); ?></p>
    <p><strong>Date Range:</strong> <?php echo htmlspecialchars($startDate ?: 'All Time'); ?> to <?php echo htmlspecialchars($endDate ?: 'Present'); ?></p>
    <?php if (!empty($barcode)): ?>
    <p><strong>Barcode Filter:</strong> <?php echo htmlspecialchars($barcode); ?></p>
    <?php endif; ?>
    <button class="print-button" onclick="printReport()">Print Report</button>
    <table>
        <thead>
            <tr>
                <th>Invoice Number</th>
                <th>Product Name</th>
                <th>Barcode</th>
                <th>Quantity</th>
                <th>Free Quantity</th>
                <th>Unit Price</th>
                <th>Discount (%)</th>
                <th>Subtotal</th>
                <th>Rep</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $totalQuantity += intval($row['quantity']);
                    $totalAmount += floatval($row['subtotal']);
                    echo "<tr>
                        <td>" . htmlspecialchars($row['invoice_number']) . "</td>
                        <td>" . htmlspecialchars($row['product_name']) . "</td>
                        <td>" . htmlspecialchars($row['barcode'] ?? 'N/A') . "</td>
                        <td>" . intval($row['quantity']) . "</td>
                        <td>" . intval($row['free_quantity']) . "</td>
                        <td>" . number_format($row['unit_price'], 2) . "</td>
                        <td>" . number_format($row['discount_percent'], 2) . "%</td>
                        <td>" . number_format($row['subtotal'], 2) . "</td>
                        <td>" . htmlspecialchars($row['rep_name']) . "</td>
                        <td>" . htmlspecialchars($row['sale_date']) . "</td>
                    </tr>";
                }
            } else {
                echo '<tr><td colspan="10">No sales records found.</td></tr>';
            } ?>
        </tbody>
        <tfoot>
            <tr class="total">
                <td colspan="3">Total</td>
                <td><?php echo $totalQuantity; ?></td>
                <td colspan="3"></td>
                <td><?php echo number_format($totalAmount, 2); ?></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
