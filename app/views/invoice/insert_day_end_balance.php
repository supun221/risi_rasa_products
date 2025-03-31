<?php
session_start();
require_once '../../../config/databade.php';

if (!isset($_SESSION['username'])) {
    die(json_encode(["success" => false, "message" => "User not logged in."]));
}

$user_id = $_SESSION['username'];

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

if (!$input) {
    die(json_encode(["success" => false, "message" => "Invalid JSON payload."]));
}

$total_balance = isset($input['total_balance']) ? floatval($input['total_balance']) : null;
$denomination_5000 = isset($input['denomination_5000']) ? intval($input['denomination_5000']) : 0;
$denomination_1000 = isset($input['denomination_1000']) ? intval($input['denomination_1000']) : 0;
$denomination_500 = isset($input['denomination_500']) ? intval($input['denomination_500']) : 0;
$denomination_100 = isset($input['denomination_100']) ? intval($input['denomination_100']) : 0;
$denomination_50 = isset($input['denomination_50']) ? intval($input['denomination_50']) : 0;
$denomination_20 = isset($input['denomination_20']) ? intval($input['denomination_20']) : 0;
$denomination_10 = isset($input['denomination_10']) ? intval($input['denomination_10']) : 0;
$denomination_5 = isset($input['denomination_5']) ? intval($input['denomination_5']) : 0;
$denomination_2 = isset($input['denomination_2']) ? intval($input['denomination_2']) : 0;
$denomination_1 = isset($input['denomination_1']) ? intval($input['denomination_1']) : 0;

if (is_null($total_balance)) {
    die(json_encode(["success" => false, "message" => "Total balance is required."]));
}

$date = date("Y-m-d");

// Fetch store information
$fetchStoreStmt = $conn->prepare("SELECT store FROM signup WHERE username = ?");
$fetchStoreStmt->bind_param("s", $user_id);
$fetchStoreStmt->execute();
$fetchStoreStmt->bind_result($store);
$fetchStoreStmt->fetch();
$fetchStoreStmt->close();

if (!$store) {
    die(json_encode(["success" => false, "message" => "Store not found for the user."]));
}

// Check if a day-end balance entry already exists
$checkStmt = $conn->prepare("SELECT id FROM day_end_balance WHERE username = ? AND date = ?");
$checkStmt->bind_param("ss", $user_id, $date);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    die(json_encode(["success" => false, "message" => "You have already submitted a day-end balance today."]));
}
$checkStmt->close();

// Insert day-end balance
$stmt = $conn->prepare("INSERT INTO day_end_balance (username, date, total_balance, denomination_5000, denomination_1000, denomination_500, denomination_100, denomination_50, denomination_20, denomination_10, denomination_5, denomination_2, denomination_1, branch) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssdiidiiiiiiis", $user_id, $date, $total_balance, $denomination_5000, $denomination_1000, $denomination_500, $denomination_100, $denomination_50, $denomination_20, $denomination_10, $denomination_5, $denomination_2, $denomination_1, $store);

echo json_encode(["success" => $stmt->execute(), "message" => $stmt->error ? "Error: " . $stmt->error : "Day-end balance recorded successfully."]);

$stmt->close();
$conn->close();
?>
