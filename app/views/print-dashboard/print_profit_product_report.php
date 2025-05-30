<?php
// Start session to access session variables
session_start();
$user_name = $_SESSION['username'] ?? 'Unknown User';
$user_branch = $_SESSION['store'] ?? 'Unknown Branch';

// Database connection
require_once '../../../config/databade.php';

// Get parameters from request
$category = isset($_GET['category']) ? $_GET['category'] : '';
$barcode = isset($_GET['barcode']) ? $_GET['barcode'] : '';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Initialize params array
$params = [];

// Base query to get profit information with correct column references
$sql = "
    SELECT 
        psi.product_name,
        p.product_barcode AS barcode,
        SUM(psi.quantity) AS total_qty,
        AVG(p.price) AS cost_price, -- Using price from purchase_items
        AVG(psi.unit_price) AS selling_price,
        SUM(psi.subtotal) AS total_revenue,
        SUM(psi.quantity * p.price) AS total_cost -- Calculate cost using price
    FROM 
        pos_sale_items psi
    JOIN 
        pos_sales ps ON psi.sale_id = ps.id
    LEFT JOIN 
        purchase_items p ON p.product_name = psi.product_name
    WHERE 1=1
";

if (!empty($barcode)) {
    $sql .= " AND p.product_barcode = ?";
    $params[] = $barcode;
}

if (!empty($startDate)) {
    $sql .= " AND DATE(ps.transaction_date) >= ?";
    $params[] = $startDate;
}

if (!empty($endDate)) {
    $sql .= " AND DATE(ps.transaction_date) <= ?";
    $params[] = $endDate;
}

// Group by product to get per-product profit
$sql .= " GROUP BY psi.product_name";

// Initialize variables to store totals
$profitData = [];
$totalQuantitySold = 0;
$totalRevenue = 0;
$totalCost = 0;
$totalProfit = 0;

try {
    // Prepare the statement
    $stmt = $conn->prepare($sql);
    
    // Bind parameters for mysqli
    if (!empty($params)) {
        $types = str_repeat('s', count($params));
        $stmt->bind_param($types, ...$params);
    }
    
    // Execute the statement
    $stmt->execute();
    
    // Get the result
    $result = $stmt->get_result();
    $profitData = [];
    
    // Fetch data using mysqli methods
    while ($row = $result->fetch_assoc()) {
        $profitData[] = $row;
    }
    
    // Calculate totals
    foreach ($profitData as $item) {
        $totalQuantitySold += intval($item['total_qty']);
        $totalRevenue += floatval($item['total_revenue']);
        $totalCost += floatval($item['total_cost']);
    }
    
    $totalProfit = $totalRevenue - $totalCost;
    $totalProfitPercentage = $totalCost > 0 ? (($totalProfit / $totalCost) * 100) : 0;
    
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage();
    exit;
}

// Set filter descriptions for the report header
$filterDescription = "Filters: ";
if (!empty($category)) {
    $filterDescription .= "Category: $category, ";
}
if (!empty($barcode)) {
    $filterDescription .= "Barcode: $barcode, ";
}
if (!empty($startDate) && !empty($endDate)) {
    $filterDescription .= "Period: $startDate to $endDate, ";
} else if (!empty($startDate)) {
    $filterDescription .= "From: $startDate, ";
} else if (!empty($endDate)) {
    $filterDescription .= "Until: $endDate, ";
}
$filterDescription = rtrim($filterDescription, ", ");
if ($filterDescription === "Filters: ") {
    $filterDescription = "All Products";
}

// Format the date for the report header
$reportDate = date("Y-m-d H:i:s");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profit Report (Product Wise)</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .report-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .company-info {
            margin-bottom: 5px;
        }
        .filter-info {
            font-style: italic;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .total-row {
            font-weight: bold;
            background-color: #f8f8f8;
        }
        .footer-info {
            text-align: center;
            font-size: 10px;
            margin-top: 30px;
            color: #777;
        }
        @media print {
            body {
                margin: 0;
                padding: 10px;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="report-header">
        <div class="report-title">PROFIT REPORT (PRODUCT WISE)</div>
        <div class="company-info">Branch: <?= htmlspecialchars($user_branch) ?></div>
        <div class="company-info">Generated on: <?= $reportDate ?></div>
        <div class="filter-info"><?= htmlspecialchars($filterDescription) ?></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Barcode</th>
                <th>Qty Sold</th>
                <th>Cost Price</th>
                <th>Selling Price</th>
                <th>Revenue</th>
                <th>Cost</th>
                <th>Profit</th>
                <th>Profit %</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($profitData)): ?>
                <tr>
                    <td colspan="9">No profit records found.</td>
                </tr>
            <?php else: ?>
                <?php foreach($profitData as $item): 
                    $profit = floatval($item['total_revenue']) - floatval($item['total_cost']);
                    $profitPercentage = floatval($item['total_cost']) > 0 ? 
                        (($profit / floatval($item['total_cost'])) * 100) : 0;
                ?>
                <tr>
                    <td><?= htmlspecialchars($item['product_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($item['barcode'] ?? 'N/A') ?></td>
                    <td><?= intval($item['total_qty']) ?></td>
                    <td><?= number_format(floatval($item['cost_price']), 2) ?></td>
                    <td><?= number_format(floatval($item['selling_price']), 2) ?></td>
                    <td><?= number_format(floatval($item['total_revenue']), 2) ?></td>
                    <td><?= number_format(floatval($item['total_cost']), 2) ?></td>
                    <td><?= number_format($profit, 2) ?></td>
                    <td><?= number_format($profitPercentage, 2) ?>%</td>
                </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="2" style="text-align:right;">Grand Total:</td>
                    <td><?= $totalQuantitySold ?></td>
                    <td>-</td>
                    <td>-</td>
                    <td><?= number_format($totalRevenue, 2) ?></td>
                    <td><?= number_format($totalCost, 2) ?></td>
                    <td><?= number_format($totalProfit, 2) ?></td>
                    <td><?= number_format($totalProfitPercentage, 2) ?>%</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer-info">
        <p>Generated by: <?= htmlspecialchars($user_name) ?> | Date: <?= $reportDate ?></p>
    </div>

</body>
</html>
