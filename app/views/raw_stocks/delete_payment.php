<?php
require 'connection_db.php'; // Include your database connection

// Get the JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Validate the input
if (!isset($data['payment_no'])) {
    echo json_encode(["message" => "Payment number is required."]);
    exit;
}

$paymentNo = $data['payment_no'];

// Step 1: Retrieve the sum of all payment_amount for the given payment_no
$sumSql = "SELECT SUM(amount) AS total_amount, supplier_id FROM payments WHERE payment_no = ?";
$sumStmt = $conn->prepare($sumSql);
$sumStmt->bind_param("i", $paymentNo);
$sumStmt->execute();
$sumStmt->bind_result($totalAmount, $supplierId);
$sumStmt->fetch();
$sumStmt->close();

// Step 2: Update the credit_balance of the corresponding supplier
if ($totalAmount > 0 && $supplierId !== null) {
    $updateSql = "UPDATE suppliers SET credit_balance = credit_balance + ? WHERE supplier_id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("di", $totalAmount, $supplierId); // Use "d" for double (floating-point) and "i" for integer
    $updateStmt->execute();
    $updateStmt->close();
}

// Step 3: Delete payments with the given payment_no
$sql = "DELETE FROM payments WHERE payment_no = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $paymentNo);

if ($stmt->execute()) {
        // Get the updated credit balance
        $balanceQuery = "SELECT credit_balance FROM suppliers WHERE supplier_id = ?";
        $balanceStmt = $conn->prepare($balanceQuery);
        $balanceStmt->bind_param("i", $supplierId);
        $balanceStmt->execute();
        $balanceStmt->bind_result($newBalance);
        $balanceStmt->fetch();
        $balanceStmt->close();
    echo json_encode(["message" => "Payments deleted successfully and credit balance updated.",
        "new_balance" => $newBalance
    ]);
} else {
    echo json_encode(["message" => "Error deleting payments."]);
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
