<?php
require_once '../../../../config/databade.php';  // Keeping the typo as it's in the original code
session_start();

// Get rep_id from session
$rep_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;

// Get request parameters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';
$product = isset($_GET['product']) ? $_GET['product'] : '';

// Build the SQL query - removed notes reference
$sql = "
    SELECT 
        se.product_name, 
        se.itemcode,
        lt.transaction_type, 
        lt.quantity, 
        lt.reason,
        lt.customer_name, 
        lt.price, 
        lt.total_amount, 
        lt.transaction_date,
        lt.barcode
    FROM lorry_transactions lt
    LEFT JOIN stock_entries se ON lt.stock_entry_id = se.id
    WHERE lt.rep_id = ?
";

$params = [$rep_id];
$param_types = "i";

// Add date filters
if ($start_date && $end_date) {
    $sql .= " AND lt.transaction_date BETWEEN ? AND DATE_ADD(?, INTERVAL 1 DAY)";
    $params[] = $start_date;
    $params[] = $end_date;
    $param_types .= "ss";
}

// Add transaction type filter
if ($type) {
    $sql .= " AND lt.transaction_type = ?";
    $params[] = $type;
    $param_types .= "s";
}

// Add product filter
if ($product) {
    $sql .= " AND (se.product_name LIKE ? OR se.itemcode LIKE ?)";
    $params[] = "%$product%";
    $params[] = "%$product%";
    $param_types .= "ss";
}

// Order by date
$sql .= " ORDER BY lt.transaction_date DESC";

try {
    // Get data
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="stock_movements_export_' . date('Y-m-d') . '.csv"');
    
    // Create file pointer connected to output stream
    $output = fopen('php://output', 'w');
    
    // Write the CSV headers
    fputcsv($output, [
        'Product Name',
        'Item Code',
        'Transaction Type',
        'Quantity',
        'Reason',
        'Customer',
        'Price',
        'Total Amount',
        'Transaction Date',
        'Barcode'
    ]);
    
    // Fetch all rows and write to CSV
    while ($row = $result->fetch_assoc()) {
        // Format transaction type
        if ($row['transaction_type'] === 'add') {
            $type = 'Added';
        } else if ($row['transaction_type'] === 'retrieve') {
            $type = 'Retrieved';
        } else if ($row['transaction_type'] === 'return') {
            $type = 'Return';
        } else if ($row['transaction_type'] === 'transfer') {
            $type = 'Transfer';
        } else {
            $type = ucfirst($row['transaction_type']);
        }
        
        fputcsv($output, [
            $row['product_name'] ?? 'Unknown Product',
            $row['itemcode'] ?? 'N/A',
            $type,
            $row['quantity'],
            ucfirst($row['reason'] ?? 'N/A'),
            $row['customer_name'] ?? 'N/A',
            $row['price'] ? 'Rs. ' . number_format($row['price'], 2) : 'N/A',
            $row['total_amount'] ? 'Rs. ' . number_format($row['total_amount'], 2) : 'N/A',
            date('Y-m-d H:i:s', strtotime($row['transaction_date'])),
            $row['barcode'] ?? 'N/A'
        ]);
    }
    
    fclose($output);
    
} catch (Exception $e) {
    // If there's an error, return it as text
    header('Content-Type: text/plain');
    echo 'Error exporting data: ' . $e->getMessage();
}
?>
