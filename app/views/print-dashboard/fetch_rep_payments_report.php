<?php
require_once '../../../config/databade.php';

// Get filter parameters
$repId = isset($_GET['rep_id']) ? $conn->real_escape_string($_GET['rep_id']) : '';
$startDate = isset($_GET['start_date']) ? $conn->real_escape_string($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? $conn->real_escape_string($_GET['end_date']) : '';

// Base query for rep payments
$query = "
    SELECT rp.invoice_number, rp.customer_name, rp.amount, rp.payment_method, 
           rp.cheque_num, rp.branch, rp.payment_date, rp.notes,
           s.username as rep_name
    FROM rep_payments rp
    LEFT JOIN signup s ON rp.rep_id = s.id
    WHERE 1=1
";

// Add filters if provided
$params = [];
$types = "";

if (!empty($repId)) {
    $query .= " AND rp.rep_id = ?";
    $params[] = $repId;
    $types .= "i";
}

if (!empty($startDate) && !empty($endDate)) {
    $query .= " AND DATE(rp.payment_date) BETWEEN ? AND ?";
    $params[] = $startDate;
    $params[] = $endDate;
    $types .= "ss";
}

$query .= " ORDER BY rp.payment_date DESC";

// Prepare and execute the statement
$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Process results
$payments = [];
while ($row = $result->fetch_assoc()) {
    $payments[] = $row;
}

// Return JSON response
$response = [
    "success" => count($payments) > 0,
    "data" => $payments
];

header('Content-Type: application/json');
echo json_encode($response);

$stmt->close();
$conn->close();
?>
