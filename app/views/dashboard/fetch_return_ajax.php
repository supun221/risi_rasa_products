<?php
require_once '../../../config/databade.php';

$startDate = isset($_GET['start_date']) ? $conn->real_escape_string($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? $conn->real_escape_string($_GET['end_date']) : '';

$query = "
    SELECT 
        r.id,
        s.supplier_name,
        r.return_date,
        r.total_amount
    FROM returns r
    JOIN suppliers s ON r.supplier_id = s.supplier_id
    WHERE 1=1
";

if (!empty($startDate)) {
    $query .= " AND r.return_date >= '$startDate'";
}

if (!empty($endDate)) {
    $query .= " AND r.return_date <= '$endDate'";
}

$result = $conn->query($query);
$response = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $response[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($response);

$conn->close();
?>
