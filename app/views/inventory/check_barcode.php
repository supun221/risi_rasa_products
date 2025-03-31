<?php
require 'connection_db.php';

if (isset($_GET['barcode'])) {
    $barcode = $_GET['barcode'];
    $query = "SELECT COUNT(*) as count FROM stock_entries WHERE barcode = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    echo json_encode(['exists' => $result['count'] > 0]);
}

$conn->close();
?>
