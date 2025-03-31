<?php
require_once '../../../models/Database.php'; // Adjust path as needed

$id = intval($_POST['id'] ?? 0);
$new_stock = intval($_POST['new_stock'] ?? 0);

if ($id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid ID"]);
    $db_conn->close();
    exit;
}

$stmt = $db_conn->prepare("UPDATE stock_entries_raw SET available_stock = ? WHERE id = ?");
$stmt->bind_param("ii", $new_stock, $id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(["status" => "success", "message" => "Stock updated successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Update failed or no changes made"]);
}

$stmt->close();
$db_conn->close();
?>
