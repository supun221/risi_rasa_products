<?php
// Include database connection
require_once '../../../../config/databade.php';
session_start();

// Get query parameters
$repId = isset($_GET['rep_id']) ? $_GET['rep_id'] : '';
$routeId = isset($_GET['route_id']) ? $_GET['route_id'] : '';
$customerId = isset($_GET['customer_id']) ? $_GET['customer_id'] : '';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$barcode = isset($_GET['barcode']) ? $_GET['barcode'] : '';

// Get rep name, route name, customer name for display
$repName = '';
$routeName = '';
$customerName = '';

try {
    // Get rep name
    if (!empty($repId)) {
        $stmt = $conn->prepare("SELECT username FROM signup WHERE id = ?");
        $stmt->bind_param("i", $repId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $repName = $row['username'];
        }
    }
    
    // Get route name
    if (!empty($routeId)) {
        $stmt = $conn->prepare("SELECT name FROM routes WHERE id = ?");
        $stmt->bind_param("i", $routeId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $routeName = $row['name'];
        }
    }
    
    // Get customer name
    if (!empty($customerId)) {
        $stmt = $conn->prepare("SELECT name FROM customers WHERE id = ?");
        $stmt->bind_param("i", $customerId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $customerName = $row['name'];
        }
    }

    // Build the same query as in fetch_rep_sales_report.php
    $query = "
        SELECT 
            ps.id,
            ps.invoice_number,
            ps.sale_date,
            ps.customer_name,
            ps.rep_id,
            s.username AS rep_name,
            psi.id AS item_id,
            psi.product_name,
            psi.quantity,
            psi.free_quantity,
            psi.unit_price,
            psi.discount_percent,
            psi.discount_amount,
            psi.subtotal,
            p.item_code AS barcode,
            c.route_id,
            r.name AS route_name
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
    
    if (!empty($routeId)) {
        $query .= " AND c.route_id = ?";
        $params[] = $routeId;
        $types .= "i";
    }
    
    if (!empty($customerId)) {
        $query .= " AND ps.customer_id = ?";
        $params[] = $customerId;
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
    
    if (!empty($barcode)) {
        $query .= " AND (p.item_code = ? OR p.barcode = ?)";
        $params[] = $barcode;
        $types .= "s";
        $params[] = $barcode;
        $types .= "s";
    }
    
    // Add order by
    $query .= " ORDER BY ps.sale_date DESC, ps.invoice_number, psi.id";
    
    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Fetch all data
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    // Group by invoice for better printout
    $invoices = [];
    foreach ($data as $row) {
        $invoiceNum = $row['invoice_number'];
        if (!isset($invoices[$invoiceNum])) {
            $invoices[$invoiceNum] = [
                'invoice_number' => $invoiceNum,
                'sale_date' => $row['sale_date'],
                'customer_name' => $row['customer_name'],
                'rep_name' => $row['rep_name'],
                'route_name' => $row['route_name'],
                'items' => []
            ];
        }
        $invoices[$invoiceNum]['items'][] = $row;
    }
    
    // Calculate report totals
    $totalQuantity = 0;
    $totalValue = 0;
    foreach ($data as $row) {
        $totalQuantity += $row['quantity'];
        $totalValue += $row['subtotal'];
    }
    
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rep Sales Items Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        h1, h2 {
            text-align: center;
            margin-bottom: 10px;
        }
        .report-header {
            margin-bottom: 20px;
            text-align: center;
        }
        .filter-info {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            padding: 10px;
            background-color: #f9f9f9;
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
        .totals {
            font-weight: bold;
            background-color: #f2f2f2;
        }
        .print-button {
            text-align: center;
            margin: 20px 0;
        }
        .invoice-group {
            margin-bottom: 10px;
        }
        .invoice-header {
            background-color: #e9ecef;
            padding: 5px;
            margin-bottom: 5px;
            font-weight: bold;
        }
        @media print {
            .print-button {
                display: none;
            }
            @page {
                size: landscape;
            }
        }
    </style>
</head>
<body>
    <div class="report-header">
        <h1>Rep Sales Items Report</h1>
        <p>Generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>
    
    <div class="filter-info">
        <p><strong>Filters:</strong></p>
        <p>
            <?php if (!empty($repName)): ?>Rep: <?php echo htmlspecialchars($repName); ?><br><?php endif; ?>
            <?php if (!empty($routeName)): ?>Route: <?php echo htmlspecialchars($routeName); ?><br><?php endif; ?>
            <?php if (!empty($customerName)): ?>Customer: <?php echo htmlspecialchars($customerName); ?><br><?php endif; ?>
            <?php if (!empty($startDate)): ?>From: <?php echo htmlspecialchars($startDate); ?><?php endif; ?>
            <?php if (!empty($endDate)): ?> To: <?php echo htmlspecialchars($endDate); ?><br><?php endif; ?>
            <?php if (!empty($barcode)): ?>Barcode/Item Code: <?php echo htmlspecialchars($barcode); ?><br><?php endif; ?>
        </p>
    </div>
    
    <?php if (empty($data)): ?>
        <div style="text-align: center; padding: 20px;">
            <p>No data found for the selected criteria.</p>
        </div>
    <?php else: ?>
        <?php foreach ($invoices as $invoice): ?>
            <div class="invoice-group">
                <div class="invoice-header">
                    Invoice: <?php echo htmlspecialchars($invoice['invoice_number']); ?> | 
                    Date: <?php echo date('Y-m-d', strtotime($invoice['sale_date'])); ?> | 
                    Customer: <?php echo htmlspecialchars($invoice['customer_name'] ?? 'Walk-in Customer'); ?> |
                    Rep: <?php echo htmlspecialchars($invoice['rep_name'] ?? 'N/A'); ?> |
                    Route: <?php echo htmlspecialchars($invoice['route_name'] ?? 'N/A'); ?>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Barcode</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Discount %</th>
                            <th>Discount Amount</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $invoiceTotal = 0;
                        foreach ($invoice['items'] as $item): 
                            $invoiceTotal += $item['subtotal'];
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($item['barcode'] ?? 'N/A'); ?></td>
                                <td><?php echo number_format($item['quantity']); ?></td>
                                <td><?php echo number_format($item['unit_price'], 2); ?></td>
                                <td><?php echo number_format($item['discount_percent'], 2); ?></td>
                                <td><?php echo number_format($item['discount_amount'], 2); ?></td>
                                <td><?php echo number_format($item['subtotal'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="6" style="text-align: right;"><strong>Invoice Total:</strong></td>
                            <td><strong><?php echo number_format($invoiceTotal, 2); ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
        
        <table style="margin-top: 20px;">
            <tr class="totals">
                <td colspan="6" style="text-align: right;">Grand Total:</td>
                <td><?php echo number_format($totalValue, 2); ?></td>
            </tr>
        </table>
    <?php endif; ?>
    
    <div class="print-button">
        <button onclick="window.print();">Print Report</button>
        <button onclick="window.close();">Close</button>
    </div>
</body>
</html>
