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

$sql = "SELECT supplier_id, supplier_name, telephone_no, company, credit_balance FROM suppliers WHERE branch = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_branch);
$stmt->execute();
$result = $stmt->get_result();

$suppliers = [];
while ($row = $result->fetch_assoc()) {
    $suppliers[] = $row;
}

echo json_encode($suppliers);
?>
