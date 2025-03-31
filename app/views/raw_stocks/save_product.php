<?php
require 'connection_db.php';

session_start();
$user_name = $_SESSION['username'];
$user_role = $_SESSION['job_role'];
$user_branch = $_SESSION['store'];

if (!$user_name && !$user_branch) {
    header("Location: ../unauthorized/unauthorized_access.php");
    exit();
}

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$data = json_decode(file_get_contents("php://input"), true);

$productName = $data['productName'] ?? null;
$itemCode = $data['itemCode'] ?? null;
$itemCodeType = $data['mode'] ?? null;


// Validate required fields
if (!$productName || !$itemCode) {
    echo json_encode(["success" => false, "message" => "Please fill in all required fields."]);
    http_response_code(400);
    exit;
}

// Validate itemCode contains only alphanumeric characters
if (!preg_match('/^[a-zA-Z0-9]+$/', $itemCode)) {
    echo json_encode(["success" => false, "message" => "Item code can only contain letters and numbers."]);
    http_response_code(400);
    exit;
}


// Insert into database
$stmt = $conn->prepare("INSERT INTO raw_items (item_code, product_name, item_code_type) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $itemCode, $productName, $itemCodeType);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "item saved successfully!"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to save item."]);
    http_response_code(500);
}
?>
