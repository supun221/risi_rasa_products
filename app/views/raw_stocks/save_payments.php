<?php
require 'connection_db.php';

$data = json_decode(file_get_contents('php://input'), true);
$payments = $data['payments'] ?? [];

$query = "INSERT INTO payments (payment_method, amount, cheque_no, ref_no, date, bank) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);

foreach ($payments as $payment) {
    $stmt->bind_param(
        'sdssss',
        $payment['method'],
        $payment['amount'],
        $payment['chequeNo'] ?? null,
        $payment['refNo'] ?? null,
        $payment['date'] ?? null,
        $payment['bank'] ?? null
    );
    $stmt->execute();
}

echo json_encode(['success' => true]);

$stmt->close();
$conn->close();
?>
