<?php

require_once '../../models/Database.php';

$user = isset($_GET['user']) ? $db_conn->real_escape_string($_GET['user']) : '';
$startDate = isset($_GET['start_date']) ? $db_conn->real_escape_string($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? $db_conn->real_escape_string($_GET['end_date']) : '';

$query = "
    SELECT 
        bill_id, 
        cancelled_by, 
        cancelled_date, 
        reason, 
        bill_amount
    FROM deleted_bills
    WHERE 1=1
";

if (!empty($user)) {
    $query .= " AND cancelled_by = '$user'";
}

if (!empty($startDate)) {
    $query .= " AND cancelled_date >= '$startDate'";
}

if (!empty($endDate)) {
    $query .= " AND cancelled_date <= '$endDate'";
}

$query .= " ORDER BY cancelled_date DESC";

$result = $db_conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deleted Bills Report</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .print-button { margin-bottom: 20px; padding: 10px; background: blue; color: white; border: none; cursor: pointer; }
        .print-button:hover { background: #0056b3; }
    </style>
</head>
<body>

    <h2>Deleted Bills Report</h2>
    <button class="print-button" onclick="window.print()">Print Report</button>

    <table>
        <thead>
            <tr>
                <th>Bill ID</th>
                <th>Cancelled By</th>
                <th>Cancelled Date</th>
                <th>Reason</th>
                <th>Bill Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['bill_id']) ?></td>
                    <td><?= htmlspecialchars($row['cancelled_by']) ?></td>
                    <td><?= htmlspecialchars($row['cancelled_date']) ?></td>
                    <td><?= htmlspecialchars($row['reason']) ?></td>
                    <td><?= htmlspecialchars($row['bill_amount']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <?php $db_conn->close(); ?>

</body>
</html>
