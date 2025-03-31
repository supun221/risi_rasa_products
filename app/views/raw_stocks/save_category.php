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

header('Content-Type: application/json');

// Decode JSON input
$data = json_decode(file_get_contents("php://input"), true);
$name = trim($data['name'] ?? '');
$key = trim($data['key'] ?? '');

// Validate inputs
if (empty($name) || empty($key)) {
    echo json_encode(['error' => 'Category name and key are required']);
    exit();
}

try {
    // Use a single query to check for duplicates
    $checkSql = "SELECT COUNT(*) AS count FROM categories WHERE category_name = ? OR key_code = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("ss", $name, $key);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    if ($result['count'] > 0) {
        echo json_encode(['error' => 'Category name or key_code already exists']);
        exit();
    }

    // Insert new category
    $insertSql = "INSERT INTO categories (category_name, key_code) VALUES (?, ?)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("ss", $name, $key);

    if ($insertStmt->execute()) {
        echo json_encode(['success' => 'Category added successfully']);
    } else {
        echo json_encode(['error' => 'Failed to add category']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>
