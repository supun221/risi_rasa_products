<?php
require_once '../../../config/databade.php'; // Correct to 'database.php' in your setup

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$branchId = $_GET['branch_id'];
$startDate = $_GET['start_date'];
$endDate = $_GET['end_date'];

$sql = "SELECT COALESCE(SUM(gross_amount), 0) as total_sales
        FROM bill_records
        WHERE branch = (SELECT branch_name FROM branch WHERE branch_id = ?)
        AND date(bill_date) BETWEEN ? AND ?";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'iss', $branchId, $startDate, $endDate);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode(['total_sales' => $row['total_sales']]);
} else {
    echo json_encode(['total_sales' => 0]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>