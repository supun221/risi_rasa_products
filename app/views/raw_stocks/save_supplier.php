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


$data = json_decode(file_get_contents("php://input"), true);

$name = $data['name'];
$telephone = $data['telephone'];
$company = $data['company'];

// Get the max supplier_id from the database
$result = $conn->query("SELECT MAX(supplier_id) AS max_id FROM suppliers");
$row = $result->fetch_assoc();

$next_id = $row['max_id'] ? $row['max_id'] + 1 : 1; // If no records, start with 1

$sql = "INSERT INTO suppliers (supplier_id, supplier_name, telephone_no, company, branch) VALUES ('$next_id', '$name', '$telephone', '$company', '$user_branch')";

if ($conn->query($sql)) {
    echo json_encode(["message" => "Supplier added successfully"]);
} else {
    echo json_encode(["message" => "Error: " . $conn->error]);
}
?>
