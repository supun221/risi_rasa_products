<?php
require_once '../../../config/databade.php'; // Ensure correct filename

$query = "SELECT DISTINCT store FROM signup ORDER BY store ASC";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$branches = [];
while ($row = $result->fetch_assoc()) {
    $branches[] = $row['store'];
}

header('Content-Type: application/json');
echo json_encode($branches);
?>
