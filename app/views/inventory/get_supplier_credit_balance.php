<?php
require 'connection_db.php';

$supplierId = $_GET['supplierId'] ?? '';
$response = ['success' => false, 'credit_balance' => 0];

if ($supplierId) {
    $stmt = $conn->prepare("SELECT credit_balance FROM suppliers WHERE supplier_id = ?");
    $stmt->bind_param('i', $supplierId);
    $stmt->execute();
    $stmt->bind_result($credit_balance);
    
    if ($stmt->fetch()) {
        $response['success'] = true;
        $response['credit_balance'] = $credit_balance;
    }
    
    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>
