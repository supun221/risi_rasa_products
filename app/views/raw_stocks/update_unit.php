<?php
require 'connection_db.php';
header("Content-Type: application/json");

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    $oldName = $data["oldName"];
    $newName = $data["newName"];

    // Check for duplicates
    $checkSql = "SELECT * FROM unit WHERE unit_name = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("s", $newName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['message' => 'Unit already exists']);
        exit();
    }

    // SQL Update Query
    $stmt = $conn->prepare("UPDATE unit SET unit_name = ? WHERE unit_name = ?");
    $stmt->bind_param("ss", $newName, $oldName);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Unit updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update unit"]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}
?>
