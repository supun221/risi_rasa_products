<?php
require 'connection_db.php';

$supplierId = isset($_GET['supplier_id']) ? $_GET['supplier_id'] : '';

if ($supplierId) {
    $sql = "SELECT payment_no, date, amount, payment_method FROM payments WHERE supplier_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $supplierId);
    $stmt->execute();
    $result = $stmt->get_result();

    $payments = [];
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }

    echo json_encode($payments);
} else {
    echo json_encode([]);
}
?>
