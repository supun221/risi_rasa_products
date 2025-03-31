<?php
    require 'connection_db.php';

    header('Content-Type: application/json');
    $data = json_decode(file_get_contents("php://input"), true);

    $name = $data['name'] ?? null;

    if (!$name) {
        echo json_encode(['message' => 'Unit name is required']);
        exit();
    }

    // Check for duplicates
    $checkSql = "SELECT * FROM unit WHERE unit_name = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['message' => 'Unit already exists']);
        exit();
    }

    // Insert new category
    $insertSql = "INSERT INTO unit (unit_name) VALUES (?)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("s", $name);

    if ($insertStmt->execute()) {
        echo json_encode(['message' => 'Unit added successfully']);
    } else {
        echo json_encode(['message' => 'Failed to add unit']);
    }
?>
