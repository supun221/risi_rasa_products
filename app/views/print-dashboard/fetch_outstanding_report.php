<?php
require_once '../../../config/databade.php';
header('Content-Type: application/json');

// Get filter parameters
$startDate = isset($_GET['start_date']) ? $conn->real_escape_string($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? $conn->real_escape_string($_GET['end_date']) : '';

// Query for outstanding balances in selected date range
$query = "
    SELECT supplier_name, telephone_no, company, credit_balance, created_at
    FROM suppliers
    WHERE credit_balance > 0.00
";

if (!empty($startDate) && !empty($endDate)) {
    $query .= " AND created_at BETWEEN '$startDate' AND '$endDate'";
}

$result = $conn->query($query);
$data = [];

// Fetch records for selected period
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Query for last total outstanding balance (ignoring date range)
$totalOutstandingQuery = "SELECT SUM(credit_balance) AS totalOutstanding FROM suppliers WHERE credit_balance > 0.00";
$totalResult = $conn->query($totalOutstandingQuery);
$totalOutstanding = ($totalResult && $totalResult->num_rows > 0) ? $totalResult->fetch_assoc()['totalOutstanding'] : 0;

// Return response
echo json_encode([
    "records" => $data,
    "totalOutstanding" => $totalOutstanding
]);

$conn->close();
?>
