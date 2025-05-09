<?php
require 'connection_db.php';
$data = json_decode(file_get_contents("php://input"), true);
$itemCode = $data['item_code'] ?? null;if ($itemCode) {
$sql = "DELETE FROM products WHERE item_code = ?";
$stmt = $conn->prepare($sql);if (!$stmt) {
echo json_encode(["message" => "Prepare failed: " . $conn->error]);
exit;}$stmt->bind_param("s", $itemCode);if ($stmt->execute()) {
echo json_encode(["message" => "Product deleted successfully."]);} else {echo json_encode(["message" => "Execution failed: " . $stmt->error]); }
$stmt->close();
} else {
echo json_encode(["message" => "Invalid item code."]);}$conn->close();
?>