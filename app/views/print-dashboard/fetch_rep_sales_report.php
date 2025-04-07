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
    $query .= " AND ls.barcode LIKE ?"; // Changed to ls.barcode
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

// Process results
$sales = [];
while ($row = $result->fetch_assoc()) {
    $sales[] = $row;
}

// Return JSON response
$response = [
    "success" => count($sales) > 0,
    "data" => $sales
];

header('Content-Type: application/json');
echo json_encode($response);

$stmt->close();
$conn->close();
?>
