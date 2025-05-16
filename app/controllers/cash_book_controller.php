<?php
session_start(); // Start the session

header('Content-Type: application/json'); // Ensure JSON response
ob_start(); // Start output buffering

include '../../config/databade.php'; // Fix typo

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === "add") {
    // Fetch the logged-in user's name and branch from the session
    $user_name = $_SESSION['username'] ?? '';
    $branch = $_SESSION['store'] ?? '';

    $reason = $_POST['reason'];
    $amount = $_POST['amount'];

    $stmt = $conn->prepare("INSERT INTO cash_book (user_name, reason, amount, branch) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssds", $user_name, $reason, $amount, $branch);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}

ob_end_flush(); // Ensure no extra output
?>