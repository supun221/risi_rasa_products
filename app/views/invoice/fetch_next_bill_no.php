<?php
include '../../models/Database.php';

$nextBillNumber = 'Bill/0000001'; // Default starting number

$highestBillNumber = 0;

// Query to get the maximum numeric part of the bill_id
$query = "
    SELECT MAX(CAST(SUBSTRING(bill_id, 6) AS UNSIGNED)) AS max_numeric_id
    FROM (
        SELECT bill_id FROM bill_records
        UNION ALL
        SELECT bill_id FROM hold_bill_records
    ) AS combined_records
";

$result = $db_conn->query($query);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $highestBillNumber = (int)$row['max_numeric_id'];
}

// Generate the next bill number
if ($highestBillNumber > 0) {
    $nextBillNumber = 'Bill/' . str_pad($highestBillNumber + 1, 7, '0', STR_PAD_LEFT);
}

echo json_encode(['next_bill_no' => $nextBillNumber]);
?>
