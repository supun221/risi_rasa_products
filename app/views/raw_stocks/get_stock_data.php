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
    // Use prepared statements to prevent SQL injection
    $query = "SELECT itemcode, stock_id, barcode, product_name, cost_price, purchase_qty, wholesale_price, max_retail_price 
              FROM stock_entries 
              WHERE branch = ? 
              ORDER BY id DESC";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(["error" => "Failed to prepare statement"]);
        exit();
    }

    // Bind parameters and execute query
    $stmt->bind_param("s", $user_branch);
    $stmt->execute();
    $result = $stmt->get_result();

    $stockData = [];
    while ($row = $result->fetch_assoc()) {
        $stockData[] = $row;
    }

    echo json_encode($stockData);

} catch (Exception $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}

$conn->close();
?>
