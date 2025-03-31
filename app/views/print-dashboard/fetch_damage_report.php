<?php
require_once '../../../config/databade.php'; // Ensure the correct database connection file

$branch = $_GET['branch'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$query = "SELECT product_name, damage_description, damage_quantity, price, barcode, branch,date
          FROM damages 
          WHERE 1 ";

$params = [];
if (!empty($branch)) {
    $query .= " AND branch = ?";
    $params[] = $branch;
}
if (!empty($start_date) && !empty($end_date)) {
    $query .= " AND DATE(date) BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
}

$stmt = $conn->prepare($query);
$stmt->execute($params);
$result = $stmt->get_result();

$damageData = [];
while ($row = $result->fetch_assoc()) {
    $damageData[] = $row;
}

$response = [
    "success" => count($damageData) > 0,
    "data" => $damageData
];

header('Content-Type: application/json');
echo json_encode($response);
?>
