<?php

include '../../models/Database.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $record_id = isset($_POST["record_id"]) ? intval($_POST["record_id"]) : 0;
    $rejected_reason = isset($_POST["rejected_reason"]) ? trim($_POST["rejected_reason"]) : "";

    if ($record_id <= 0 || empty($rejected_reason)) {
        echo json_encode(["success" => false, "error" => "Invalid input."]);
        exit;
    }

    $stmt = $db_conn->prepare("UPDATE stock_transfer_items SET state = 'rejected', rejected_reason = ? WHERE id = ?");
    $stmt->bind_param("si", $rejected_reason, $record_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => "Database error: " . $stmt->error]);
    }

    $stmt->close();
}

$db_conn->close();
?>
