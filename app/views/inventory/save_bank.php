<?php
require 'connection_db.php';

$data = json_decode(file_get_contents('php://input'), true);
$bankCode = $data['bankCode'];
$bankName = $data['bankName'];

$stmt = $conn->prepare("INSERT INTO banks (bank_code, bank_name) VALUES (?, ?)");
$stmt->bind_param('ss', $bankCode, $bankName);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
$stmt->close();
$conn->close();
?>
