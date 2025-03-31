<?php
require 'connection_db.php';

session_start();
$user_name = $_SESSION['username'];
$user_role = $_SESSION['job_role'];
$user_branch = $_SESSION['store'];

if (!$user_name || !$user_branch) {
    header("Location: ../unauthorized/unauthorized_access.php");
    exit();
}

header('Content-Type: application/json');

try {
    // Use a prepared statement to prevent SQL injection
    $sql = "SELECT category_name, key_code FROM categories";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(["error" => "Failed to prepare statement"]);
        exit();
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }

    echo json_encode($categories);

} catch (Exception $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}

$conn->close();
?>
