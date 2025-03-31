<?php
require 'connection_db.php';
session_start();
$user_name = $_SESSION['username'] ?? null;
$user_role = $_SESSION['job_role'] ?? null;
$user_branch = $_SESSION['store'] ?? null;

if (!$user_name || !$user_branch) {
    header("Location: ../unauthorized/unauthorized_access.php");
    exit();
}

header("Content-Type: application/json");

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    $oldKey = $data["oldKey"];
    $newName = $data["newName"];
    $newKey = $data["newKey"];

    // Check for duplicates
    // $checkSql = "SELECT * FROM categories WHERE category_name = ? OR key_code = ?";
    // $stmt = $conn->prepare($checkSql);
    // $stmt->bind_param("ss", $newName, $newKey);
    // $stmt->execute();
    // $result = $stmt->get_result();

    // if ($result->num_rows > 0) {
    //     echo json_encode(['message' => 'Category already exists']);
    //     exit();
    // }

    // SQL Update Query
    $stmt = $conn->prepare("UPDATE categories SET category_name = ?, key_code = ? WHERE key_code = ?");
    $stmt->bind_param("sss", $newName, $newKey, $oldKey);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Category updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update category"]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}
?>
