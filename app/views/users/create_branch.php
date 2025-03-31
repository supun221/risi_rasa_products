<?php

include '../../models/Database.php';

$data = json_decode(file_get_contents("php://input"), true);
$branch_name = trim($data['branch_name']);
$branchPhone = trim($data['branchPhone']);
$branchAddress = trim($data['branchAddress']);

if (empty($branch_name)) {
    echo json_encode(["error" => "Branch name cannot be empty."]);
    exit;
}

$stmt = $db_conn->prepare("INSERT INTO branch (branch_name, address, phone) VALUES (?,?,?)");

if (!$stmt) {
    echo json_encode(["error" => "Failed to prepare statement: " . $db_conn->error]);
    exit;
}

$stmt->bind_param("sss", $branch_name, $branchAddress, $branchPhone);

if ($stmt->execute()) {
    echo json_encode(["success" => "Branch registered successfully!", "branch_id" => $stmt->insert_id]);
} else {
    echo json_encode(["error" => "Failed to register branch: " . $stmt->error]);
}

$stmt->close();
$db_conn->close();
?>
