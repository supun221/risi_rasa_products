<?php
require 'connection_db.php';

$data = json_decode(file_get_contents("php://input"), true);
$itemCode = $data['item_code'];

$sql = "DELETE FROM raw_items WHERE item_code = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $itemCode);

if ($stmt->execute()) {
    echo json_encode(["message" => "Product deleted successfully."]);
} else {
    echo json_encode(["message" => "Error deleting product."]);
}

$stmt->close();
$conn->close();
?>
