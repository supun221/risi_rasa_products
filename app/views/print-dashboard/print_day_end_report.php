<?php

require_once '../../models/Database.php';

$user = isset($_GET['user']) ? trim($db_conn->real_escape_string($_GET['user'])) : '';
$startDate = isset($_GET['start_date']) ? $db_conn->real_escape_string($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? $db_conn->real_escape_string($_GET['end_date']) : '';

$query = "
    SELECT * FROM day_end_reports WHERE 1=1
";

if (!empty($user)) {
    $query .= " AND username = '$user'";
}

if (!empty($startDate) && !empty($endDate)) {
    $query .= " AND created_at BETWEEN '$startDate' AND '$endDate'";
} elseif (!empty($startDate)) {
    $query .= " AND created_at >= '$startDate'";
} elseif (!empty($endDate)) {
    $query .= " AND created_at <= '$endDate'";
}

$query .= " ORDER BY created_at DESC";

$result = $db_conn->query($query);

echo '
<!DOCTYPE html>
<html>
<head>
    <title>Day End Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
      .print-button { margin: 10px; padding: 10px 20px; background-color: blue; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <h2>Day End Report</h2>
     <button class="print-button" onclick="window.print()">Print Report</button>
    <table>
        <thead>
            <tr><th>Username</th><th>Opening Balance</th><th>Total Gross</th><th>Total Net</th><th>Total Discount</th><th>Total Bills</th><th>Created At</th></tr>
        </thead>
        <tbody>';

while ($row = $result->fetch_assoc()) {
    echo "<tr><td>{$row['username']}</td><td>{$row['opening_balance']}</td><td>{$row['total_gross']}</td><td>{$row['total_net']}</td><td>{$row['total_discount']}</td><td>{$row['total_bills']}</td><td>{$row['created_at']}</td></tr>";
}

echo '</tbody></table></body></html>';
?>
