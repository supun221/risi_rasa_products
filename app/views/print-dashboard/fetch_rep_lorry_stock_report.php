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
    $query .= " AND ls.barcode LIKE ?"; // Changed to ls.barcode
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

// Process results
$stock = [];
while ($row = $result->fetch_assoc()) {
    $stock[] = $row;
}

// Return JSON response
$response = [
    "success" => count($stock) > 0,
    "data" => $stock
];

header('Content-Type: application/json');
echo json_encode($response);

$stmt->close();
$conn->close();
?>
