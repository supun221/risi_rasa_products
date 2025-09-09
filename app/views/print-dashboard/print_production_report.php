<?php
// Database connection
require_once '../../../config/databade.php'; // Fixed the typo in database.php

// Get filter parameters
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$startDate = isset($_GET['start_date']) ? $conn->real_escape_string($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? $conn->real_escape_string($_GET['end_date']) : '';
$branch = isset($_GET['branch']) ? $conn->real_escape_string($_GET['branch']) : '';

// Build the SQL query - filter for supplier_id = 'self_001' to get production items
$query = "SELECT * FROM stock_entries 
          WHERE supplier_id = 'self_001'";

// Add filters
$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND (product_name LIKE ? OR barcode LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

if (!empty($startDate)) {
    $query .= " AND DATE(created_at) >= ?";
    $params[] = $startDate;
    $types .= "s";
}

if (!empty($endDate)) {
    $query .= " AND DATE(created_at) <= ?";
    $params[] = $endDate;
    $types .= "s";
}

if (!empty($branch)) {
    $query .= " AND branch = ?";
    $params[] = $branch;
    $types .= "s";
}

// Order by created_at (newest first)
$query .= " ORDER BY created_at DESC";

// Initialize data arrays
$productionData = [];
$totalQuantity = 0;  // Fixed: Added the missing $ sign
$totalCost = 0;

try {
    // Prepare and execute the statement
    $stmt = $conn->prepare($query);
    
    // Bind parameters if there are any
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $productionData[] = $row;
            $totalQuantity += intval($row['purchase_qty']);
            $totalCost += floatval($row['total_cost_amount']);
        }
    }
    
    $stmt->close();

    // Filter information for the report header
    $filterInfo = [];
    
} catch (Exception $e) {
    // Log error
    error_log("Error in print_production_report.php: " . $e->getMessage());
    // Set empty data array
    $productionData = [];
    $totalQuantity = 0;
    $totalCost = 0;
    $filterInfo = ["Error: Unable to fetch data"];
}

if (!empty($search)) {
    $filterInfo[] = "Search: " . htmlspecialchars($search);
}

if (!empty($startDate)) {
    $filterInfo[] = "From: " . htmlspecialchars($startDate);
}

if (!empty($endDate)) {
    $filterInfo[] = "To: " . htmlspecialchars($endDate);
}

if (!empty($branch)) {
    $filterInfo[] = "Branch: " . htmlspecialchars($branch);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }
        h1 {
            text-align: center;
            margin-bottom: 10px;
        }
        .report-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .filter-info {
            text-align: center;
            margin-bottom: 15px;
            font-style: italic;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .totals-row {
            font-weight: bold;
            background-color: #f2f2f2;
        }
        .date-generated {
            text-align: right;
            margin-top: 20px;
            font-size: 10px;
            font-style: italic;
        }
        @media print {
            body {
                padding: 0;
                font-size: 10px;
            }
            button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="report-header">
        <h1>Production Report</h1>
        <?php if (!empty($filterInfo)): ?>
            <div class="filter-info">
                <?php echo implode(" | ", $filterInfo); ?>
            </div>
        <?php endif; ?>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Barcode</th>
                <th>Production Quantity</th>
                <th>Unit</th>
                <th>Cost Price</th>
                <th>Total Cost</th>
                <th>MRP</th>
                <th>Branch</th>
                <th>Production Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($productionData)): ?>
                <tr>
                    <td colspan="9" style="text-align: center;">No production records found</td>
                </tr>
            <?php else: ?>
                <?php foreach ($productionData as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['product_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($item['barcode'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($item['purchase_qty'] ?? '0'); ?></td>
                        <td><?php echo htmlspecialchars($item['unit'] ?? 'N/A'); ?></td>
                        <td><?php echo number_format((float)($item['cost_price'] ?? 0), 2); ?></td>
                        <td><?php echo number_format((float)($item['total_cost_amount'] ?? 0), 2); ?></td>
                        <td><?php echo number_format((float)($item['max_retail_price'] ?? 0), 2); ?></td>
                        <td><?php echo htmlspecialchars($item['branch'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($item['created_at'] ?? 'N/A'); ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="totals-row">
                    <td colspan="2" style="text-align:right;">Total:</td>
                    <td><?php echo $totalQuantity; ?></td>
                    <td></td>
                    <td></td>
                    <td><?php echo number_format($totalCost, 2); ?></td>
                    <td colspan="3"></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div class="date-generated">
        Report generated on: <?php echo date('Y-m-d H:i:s'); ?>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
