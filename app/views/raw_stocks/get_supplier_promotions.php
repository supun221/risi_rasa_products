<?php
require 'connection_db.php';

session_start();
$user_name = $_SESSION['username'];
$user_role = $_SESSION['job_role'];
$user_branch = $_SESSION['store'];

if (!$user_name || !$user_branch) {
    header("Location: ../unauthorized/unauthorized_access.php");
    exit();
}

$sql = "SELECT supplier_id, supplier_name, discount_percentage, discount_value, start_date, end_date FROM supplier_promotions WHERE branch = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_branch);
$stmt->execute();
$result = $stmt->get_result();

$promotions = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $promotions[] = $row;
    }
}

echo json_encode($promotions);

$conn->close();
?>
