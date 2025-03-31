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
$category = $data['category'] ?? null;
$productName = $data['productName'] ?? null;
$sinhalaProductName = $data['sinhalaProductName'] ?? null;
$itemCode = $data['itemCode'] ?? null;
$productImage = $data["productImage"] ?? null;
$itemCodeType = $data['mode'] ?? null;
// $productPrice = $data['productPrice'] ?? null;
// $productWPrice = $data['productWPrice'] ?? null;
// $productMRPrice = $data['productMRPrice'] ?? null;

// Validate required fields
// if (!$category || !$productName || !$sinhalaProductName || !$itemCode || !$itemCodeType) {
//     echo json_encode(["success" => false, "message" => "Please fill in all required fields."]);
//     http_response_code(400);
//     exit;
// }

// Validate itemCode contains only alphanumeric characters
if (!preg_match('/^[a-zA-Z0-9]+$/', $itemCode)) {
    echo json_encode(["success" => false, "message" => "Item code can only contain letters and numbers."]);
    http_response_code(400);
    exit;
}

// Decode and save image
$imagePath = null;
if ($productImage) {
    $imageData = base64_decode($productImage);
    $imagePath = "uploads/" . uniqid() . ".jpg";

    if (!file_put_contents($imagePath, $imageData)) {
        echo json_encode(["success" => false, "message" => "Failed to save the image."]);
        http_response_code(500);
        exit();
    }
}

// Insert into database
$stmt = $conn->prepare("INSERT INTO products (item_code, category, product_name, sinhala_name, image_path, item_code_type) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $itemCode, $category, $productName, $sinhalaProductName, $imagePath, $itemCodeType);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Product saved successfully!"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to save product."]);
    http_response_code(500);
}
?>
