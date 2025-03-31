<?php
require 'connection_db.php';
// Get JSON input
$data = json_decode(file_get_contents("php://input"), true);
$id = isset($data['id']) ? $data['id'] : 0;

// Get barcode before deleting
$sql = "SELECT product_barcode FROM promotions WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($barcode);
$stmt->fetch();
$stmt->close();

// Delete promotion
$sql = "DELETE FROM promotions WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$success = $stmt->execute();
$stmt->close();

if ($success) {
    echo json_encode(["success" => true, "barcode" => $barcode]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to delete promotion."]);
}

$conn->close();
?>
