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

try {
    // Build the query
    $query = "
        SELECT 
            ps.invoice_number,
            ps.sale_date,
            ps.customer_name,
            s.username AS rep_name,
            psi.product_name,
            p.item_code AS barcode,
            psi.quantity,
            psi.unit_price,
            psi.discount_percent,
            psi.discount_amount,
            psi.subtotal,
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
    $query .= " ORDER BY ps.sale_date DESC, ps.invoice_number";
    
    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Set headers for CSV download
    $filename = "rep_sales_report_" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Create a file pointer
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for Excel compatibility with special characters
    fputs($output, "\xEF\xBB\xBF");
    
    // Add CSV header
    fputcsv($output, [
        'Invoice Number',
        'Date',
        'Customer',
        'Route',
        'Sales Rep',
        'Product Name',
        'Barcode',
        'Quantity',
        'Unit Price',
        'Discount %',
        'Discount Amount',
        'Subtotal'
    ]);
    
    // Add data rows
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['invoice_number'],
            date('Y-m-d', strtotime($row['sale_date'])),
            $row['customer_name'] ?: 'Walk-in Customer',
            $row['route_name'] ?: 'N/A',
            $row['rep_name'] ?: 'N/A',
            $row['product_name'],
            $row['barcode'] ?: 'N/A',
            $row['quantity'],
            $row['unit_price'],
            $row['discount_percent'],
            $row['discount_amount'],
            $row['subtotal']
        ]);
    }
    
    fclose($output);
    
} catch (Exception $e) {
    // In case of error, return error message
    header('Content-Type: text/plain');
    echo 'Error generating report: ' . $e->getMessage();
}
?>
