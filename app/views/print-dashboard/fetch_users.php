<?php
require_once '../../../config/databade.php'; // Ensure correct filename

$query = "SELECT DISTINCT username FROM signup ORDER BY username ASC";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row['username'];
}

header('Content-Type: application/json');
echo json_encode($users);
?>
