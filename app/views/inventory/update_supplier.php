<?php
require 'connection_db.php';
header("Content-Type: application/json");

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    $supplierId = $data["supplierId"];
    $newName = $data["newName"];
    $newCompany = $data["newCompany"];
    $newPhone = $data["newPhone"];

    // SQL Update Query
    $stmt = $conn->prepare("UPDATE suppliers SET supplier_name = ?, company = ?, telephone_no = ? WHERE supplier_id = ?");
    $stmt->bind_param("sssi", $newName, $newCompany, $newPhone, $supplierId);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Supplier updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update supplier"]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
}
?>
