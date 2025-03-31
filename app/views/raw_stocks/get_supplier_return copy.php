<?php
require 'connection_db.php';

$supplierId = isset($_GET['supplier_id']) ? $_GET['supplier_id'] : '';

if ($supplierId) {
    $sql = "SELECT SUM(total_amount) AS total_amount FROM returns WHERE supplier_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $supplierId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $totalAmount = $result->fetch_assoc();
    
    echo json_encode($totalAmount);
} else {
    echo json_encode(["total_amount" => 0]);
}
?>

