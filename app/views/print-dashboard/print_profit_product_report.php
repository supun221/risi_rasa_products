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

// Initialize params array for main POS
$paramsMain = [];
$paramsRep = [];

// Query for main POS sales (bill_records + purchase_items + stock_entries)
$sqlMain = "
    SELECT 
        pi.product_name,
        pi.product_barcode AS barcode,
        pi.purchase_qty as quantity,
        pi.price AS selling_price,
        pi.subtotal AS revenue,
        se.cost_price,
        (pi.purchase_qty * se.cost_price) as item_cost,
        'main_pos' as source,
        pi.id as item_id
    FROM 
        purchase_items pi
    JOIN 
        bill_records br ON pi.bill_id = br.bill_id
    LEFT JOIN 
        stock_entries se ON pi.stock_id = se.stock_id
    LEFT JOIN 
        products pro ON pro.product_name = pi.product_name
    WHERE 1=1
";

if (!empty($category)) {
    $sqlMain .= " AND pro.category = ?";
    $paramsMain[] = $category;
}

if (!empty($barcode)) {
    $sqlMain .= " AND pi.product_barcode = ?";
    $paramsMain[] = $barcode;
}

if (!empty($startDate)) {
    $sqlMain .= " AND STR_TO_DATE(br.bill_date, '%Y-%m-%d') >= ?";
    $paramsMain[] = $startDate;
}

if (!empty($endDate)) {
    $sqlMain .= " AND STR_TO_DATE(br.bill_date, '%Y-%m-%d') <= ?";
    $paramsMain[] = $endDate;
}

// Query for rep sales (pos_sales + pos_sale_items + lorry_stock + stock_entries + products)
$sqlRep = "
    SELECT 
        psi.product_name,
        COALESCE(ls.barcode, pro.item_code) as barcode,
        psi.quantity,
        psi.unit_price AS selling_price,
        psi.subtotal AS revenue,
        se.cost_price,
        (psi.quantity * se.cost_price) as item_cost,
        'rep_sales' as source,
        psi.id as item_id
    FROM 
        pos_sale_items psi
    JOIN 
        pos_sales ps ON psi.sale_id = ps.id
    LEFT JOIN 
        lorry_stock ls ON psi.lorry_stock_id = ls.id
    LEFT JOIN 
        stock_entries se ON ls.stock_entry_id = se.id
    LEFT JOIN 
        products pro ON pro.product_name = psi.product_name
    WHERE 1=1
";

if (!empty($category)) {
    $sqlRep .= " AND pro.category = ?";
    $paramsRep[] = $category;
}

if (!empty($barcode)) {
    $sqlRep .= " AND (ls.barcode = ? OR pro.item_code = ?)";
    $paramsRep[] = $barcode;
    $paramsRep[] = $barcode;
}

if (!empty($startDate)) {
    $sqlRep .= " AND DATE(ps.sale_date) >= ?";
    $paramsRep[] = $startDate;
}

if (!empty($endDate)) {
    $sqlRep .= " AND DATE(ps.sale_date) <= ?";
    $paramsRep[] = $endDate;
}

// Initialize variables to store data
$salesData = [];
$totalQuantitySold = 0;
$totalRevenue = 0;
$totalCost = 0;
$totalProfit = 0;

try {
    // Execute main POS query
    $stmtMain = $conn->prepare($sqlMain);
    if (!empty($paramsMain)) {
        $types = str_repeat('s', count($paramsMain));
        $stmtMain->bind_param($types, ...$paramsMain);
    }
    $stmtMain->execute();
    $resultMain = $stmtMain->get_result();
    
    // Execute rep sales query
    $stmtRep = $conn->prepare($sqlRep);
    if (!empty($paramsRep)) {
        $types = str_repeat('s', count($paramsRep));
        $stmtRep->bind_param($types, ...$paramsRep);
    }
    $stmtRep->execute();
    $resultRep = $stmtRep->get_result();
    
    // Collect all sales data
    $allSalesData = [];
    
    // Process main POS data
    while ($row = $resultMain->fetch_assoc()) {
        $allSalesData[] = $row;
    }
    
    // Process rep sales data
    while ($row = $resultRep->fetch_assoc()) {
        $allSalesData[] = $row;
    }
    
    // Group by product name and calculate totals
    $productData = [];
    
    foreach ($allSalesData as $sale) {
        $productName = $sale['product_name'];
        
        if (!isset($productData[$productName])) {
            $productData[$productName] = [
                'product_name' => $productName,
                'barcode' => $sale['barcode'] ?? '',
                'total_qty' => 0,
                'total_revenue' => 0,
                'total_cost' => 0,
                'cost_prices' => [],
                'selling_prices' => [],
                'sources' => []
            ];
        }
        
        $quantity = intval($sale['quantity']);
        $revenue = floatval($sale['revenue']);
        $costPrice = floatval($sale['cost_price'] ?? 0);
        $sellingPrice = floatval($sale['selling_price']);
        $itemCost = floatval($sale['item_cost'] ?? 0);
        
        // Accumulate data
        $productData[$productName]['total_qty'] += $quantity;
        $productData[$productName]['total_revenue'] += $revenue;
        $productData[$productName]['total_cost'] += $itemCost;
        
        // Collect cost prices for average calculation (only non-zero values)
        if ($costPrice > 0) {
            $productData[$productName]['cost_prices'][] = $costPrice;
        }
        
        // Collect selling prices for average calculation
        if ($sellingPrice > 0) {
            $productData[$productName]['selling_prices'][] = $sellingPrice;
        }
        
        // Track sources
        if (!in_array($sale['source'], $productData[$productName]['sources'])) {
            $productData[$productName]['sources'][] = $sale['source'];
        }
        
        // Use barcode from current sale if product barcode is empty
        if (empty($productData[$productName]['barcode']) && !empty($sale['barcode'])) {
            $productData[$productName]['barcode'] = $sale['barcode'];
        }
    }
    
    // Calculate averages and profit for each product
    foreach ($productData as $productName => &$data) {
        // Calculate average cost price
        if (count($data['cost_prices']) > 0) {
            $data['avg_cost_price'] = array_sum($data['cost_prices']) / count($data['cost_prices']);
        } else {
            $data['avg_cost_price'] = 0;
        }
        
        // Calculate average selling price
        if (count($data['selling_prices']) > 0) {
            $data['avg_selling_price'] = array_sum($data['selling_prices']) / count($data['selling_prices']);
        } else {
            $data['avg_selling_price'] = 0;
        }
        
        // Calculate profit (total_cost is already the sum of individual item costs)
        $data['profit'] = $data['total_revenue'] - $data['total_cost'];
        
        // Calculate profit percentage
        $data['profit_percentage'] = $data['total_cost'] > 0 ? 
            (($data['profit'] / $data['total_cost']) * 100) : 0;
    }
    
    // Convert to indexed array for display
    $profitData = array_values($productData);
    
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
    $filterDescription = "All Products (Main POS + Rep Sales)";
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
        .sources {
            font-size: 10px;
            color: #666;
        }
        .negative-profit {
            color: red;
        }
        .positive-profit {
            color: green;
        }
        .numeric {
            text-align: right;
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
        <div class="company-info">Sources: Main POS + Rep Sales</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Barcode</th>
                <th>Qty Sold</th>
                <th>Avg Cost Price</th>
                <th>Avg Selling Price</th>
                <th>Total Revenue</th>
                <th>Total Cost</th>
                <th>Profit</th>
                <th>Profit %</th>
                <th>Sources</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($profitData)): ?>
                <tr>
                    <td colspan="10">No profit records found.</td>
                </tr>
            <?php else: ?>
                <?php foreach($profitData as $item): 
                    $profitClass = $item['profit'] >= 0 ? 'positive-profit' : 'negative-profit';
                    $sources = implode(', ', $item['sources']);
                ?>
                <tr>
                    <td><?= htmlspecialchars($item['product_name'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($item['barcode'] ?? 'N/A') ?></td>
                    <td class="numeric"><?= number_format(intval($item['total_qty'])) ?></td>
                    <td class="numeric"><?= number_format($item['avg_cost_price'], 2) ?></td>
                    <td class="numeric"><?= number_format($item['avg_selling_price'], 2) ?></td>
                    <td class="numeric"><?= number_format($item['total_revenue'], 2) ?></td>
                    <td class="numeric"><?= number_format($item['total_cost'], 2) ?></td>
                    <td class="numeric <?= $profitClass ?>"><?= number_format($item['profit'], 2) ?></td>
                    <td class="numeric <?= $profitClass ?>"><?= number_format($item['profit_percentage'], 2) ?>%</td>
                    <td class="sources"><?= htmlspecialchars($sources) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="total-row">
                    <td colspan="2" style="text-align:right;">Grand Total:</td>
                    <td class="numeric"><?= number_format($totalQuantitySold) ?></td>
                    <td>-</td>
                    <td>-</td>
                    <td class="numeric"><?= number_format($totalRevenue, 2) ?></td>
                    <td class="numeric"><?= number_format($totalCost, 2) ?></td>
                    <td class="numeric <?= $totalProfit >= 0 ? 'positive-profit' : 'negative-profit' ?>"><?= number_format($totalProfit, 2) ?></td>
                    <td class="numeric <?= $totalProfit >= 0 ? 'positive-profit' : 'negative-profit' ?>"><?= number_format($totalProfitPercentage, 2) ?>%</td>
                    <td>Both</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer-info">
        <p>Generated by: <?= htmlspecialchars($user_name) ?> | Date: <?= $reportDate ?></p>
        <!-- <p>Note: Cost prices from stock_entries table. Average cost price shown, actual item costs used for total calculations.</p>
        <p>Cost calculation: Main POS via stock_id, Rep Sales via lorry_stock → stock_entries chain.</p> -->
    </div>

</body>
</html>