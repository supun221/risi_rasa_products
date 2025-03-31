<?php
include '../../models/Database.php';
header('Content-Type: application/json');

try {
    $stmt = $db_conn->prepare("SELECT * FROM measurement_conversions");
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $db_conn->error);
    } 
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Failed to fetch data: " . $stmt->error);
    }
    $records = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $records]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
