<?php
// Connect to database
require_once '../../../config/databade.php';

// Get query parameters
$repId = isset($_GET['rep_id']) ? $_GET['rep_id'] : '';
$routeId = isset($_GET['route_id']) ? $_GET['route_id'] : '';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$barcode = isset($_GET['barcode']) ? $_GET['barcode'] : '';

// Build filter description
$filterText = "Rep Sales Items Report";
if (!empty($startDate) && !empty($endDate)) {
    $filterText .= " from " . date('d/m/Y', strtotime($startDate)) . " to " . date('d/m/Y', strtotime($endDate));
} else if (!empty($startDate)) {
    $filterText .= " from " . date('d/m/Y', strtotime($startDate));
} else if (!empty($endDate)) {
    $filterText .= " until " . date('d/m/Y', strtotime($endDate));
}

// Get rep name if specified
$repName = "All Representatives";
if (!empty($repId)) {
    // Use signup table instead of users table
    $repQuery = "SELECT username FROM signup WHERE id = ?";
    $stmt = $conn->prepare($repQuery);
    $stmt->bind_param("i", $repId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $repName = $row['username'];
    }
}

// Get route name if specified
$routeName = "All Routes";
if (!empty($routeId)) {
    $routeQuery = "SELECT name FROM routes WHERE id = ?";
    $stmt = $conn->prepare($routeQuery);
    $stmt->bind_param("i", $routeId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $routeName = $row['name'];
    }
}

// Build the query with the same improvements as in fetch_rep_sales_report.php
$query = "
    SELECT 
        psi.id,
        ps.invoice_number,
        psi.product_name,
        p.item_code AS barcode,
        psi.quantity,
        psi.free_quantity,
        psi.unit_price,
        psi.discount_percent,
        psi.discount_amount,
        psi.subtotal,
        ps.customer_name,
        c.route_id,
        r.name AS route_name,
        s.username AS rep_name,
        ps.sale_date
    FROM 
        pos_sales ps
    INNER JOIN 
        pos_sale_items psi ON ps.id = psi.sale_id
    LEFT JOIN 
        customers c ON ps.customer_id = c.id
    LEFT JOIN 
        routes r ON c.route_id = r.id
    LEFT JOIN 
        products p ON psi.product_name = p.product_name
    LEFT JOIN 
        signup s ON ps.rep_id = s.id
    WHERE 1=1
";

// Add filters based on parameters
$params = [];
$types = "";

if (!empty($repId)) {
    $query .= " AND ps.rep_id = ?";
    $params[] = $repId;
    $types .= "i";
}

// Improved route ID filter
if (!empty($routeId)) {
    $query .= " AND c.route_id = ?";
    $params[] = $routeId;
    $types .= "i";
}

if (!empty($startDate)) {
    $query .= " AND DATE(ps.sale_date) >= ?";
    $params[] = $startDate;
    $types .= "s";
}

if (!empty($endDate)) {
    $query .= " AND DATE(ps.sale_date) <= ?";
    $params[] = $endDate;
    $types .= "s";
}

// Update barcode filter to account for both fields
if (!empty($barcode)) {
    $query .= " AND (p.item_code = ? OR p.barcode = ?)";
    $params[] = $barcode;
    $types .= "s";
    $params[] = $barcode;
    $types .= "s";
}

// Add order by
$query .= " ORDER BY ps.sale_date DESC, ps.invoice_number";

// Prepare and execute the query
$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Calculate totals
$totalQuantity = 0;
$totalAmount = 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rep Sales Items Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .report-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .report-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-subtitle {
            font-size: 16px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="report-header">
        <h1 class="report-title">Rep Sales Items Report</h1>
        <div class="report-subtitle">
            <p>Period: <?php echo (!empty($startDate) ? date('d/m/Y', strtotime($startDate)) : 'All') . 
                      ' to ' . 
                      (!empty($endDate) ? date('d/m/Y', strtotime($endDate)) : 'Current'); ?></p>
            <p>Representative: <?php echo htmlspecialchars($repName); ?></p>
            <p>Route: <?php echo htmlspecialchars($routeName); ?></p>
            <?php if (!empty($barcode)): ?>
                <p>Barcode: <?php echo htmlspecialchars($barcode); ?></p>
            <?php endif; ?>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Invoice</th>
                <th>Product</th>
                <th>Barcode</th>
                <th>Qty</th>
                <th>Free</th>
                <th>Unit Price</th>
                <th>Disc %</th>
                <th>Subtotal</th>
                <th>Customer</th>
                <th>Route</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $counter = 1;
            while ($row = $result->fetch_assoc()) {
                $totalQuantity += $row['quantity'];
                $totalAmount += $row['subtotal'];
            ?>
                <tr>
                    <td><?php echo $counter++; ?></td>
                    <td><?php echo htmlspecialchars($row['invoice_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['barcode'] ?? 'N/A'); ?></td>
                    <td><?php echo $row['quantity']; ?></td>
                    <td><?php echo $row['free_quantity']; ?></td>
                    <td><?php echo number_format($row['unit_price'], 2); ?></td>
                    <td><?php echo number_format($row['discount_percent'], 2); ?>%</td>
                    <td><?php echo number_format($row['subtotal'], 2); ?></td>
                    <td><?php echo htmlspecialchars($row['customer_name'] ?? 'Walk-in'); ?></td>
                    <td><?php echo htmlspecialchars($row['route_name'] ?? 'N/A'); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($row['sale_date'])); ?></td>
                </tr>
            <?php } ?>
            
            <?php if ($counter > 1): // Only show totals if there are items ?>
                <tr class="total-row">
                    <td colspan="4" style="text-align: right;"><strong>Total:</strong></td>
                    <td><strong><?php echo $totalQuantity; ?></strong></td>
                    <td colspan="3"></td>
                    <td><strong><?php echo number_format($totalAmount, 2); ?></strong></td>
                    <td colspan="3"></td>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="12" style="text-align: center;">No sales records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div class="footer">
        <p>Generated on: <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>
    
    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()">Print Report</button>
        <button onclick="window.close()">Close</button>
    </div>
</body>
</html>
