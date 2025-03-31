<?php
require_once '../../../config/databade.php'; // Ensure the correct database connection file

$branch = $_GET['branch'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$query = "SELECT product_name, damage_description, damage_quantity, price, barcode, branch 
          FROM damages 
          WHERE 1 ";

$params = [];
if (!empty($branch)) {
    $query .= " AND branch = ?";
    $params[] = $branch;
}
if (!empty($start_date) && !empty($end_date)) {
    $query .= " AND DATE(damage_date) BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
}

$stmt = $conn->prepare($query);
$stmt->execute($params);
$result = $stmt->get_result();

echo "<html><head><title>Damage Report</title></head><body>";
echo "<h2>Damage Report</h2>";
echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr>
        <th>Product Name</th>
        <th>Damage Description</th>
        <th>Damage Quantity</th>
        <th>Price</th>
        <th>Barcode</th>
        <th>Branch</th>
      </tr>";

$totalDamageCost = 0;
$totalDamageQuantity = 0;

while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>{$row['product_name']}</td>
            <td>{$row['damage_description']}</td>
            <td>{$row['damage_quantity']}</td>
            <td>{$row['price']}</td>
            <td>{$row['barcode']}</td>
            <td>{$row['branch']}</td>
          </tr>";
    
    $totalDamageQuantity += intval($row['damage_quantity']);
    $totalDamageCost += floatval($row['price']) * intval($row['damage_quantity']);
}

// Append total row
echo "<tr style='font-weight: bold;'>
        <td colspan='2' style='text-align:right;'>Total:</td>
        <td>{$totalDamageQuantity}</td>
        <td colspan='3'>{$totalDamageCost}</td>
      </tr>";

echo "</table>";
echo "<script>window.print();</script>";
echo "</body></html>";
?>
