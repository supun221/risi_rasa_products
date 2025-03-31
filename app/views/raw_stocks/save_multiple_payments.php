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

$data = json_decode(file_get_contents('php://input'), true);

if (!empty($data['payments']) && !empty($data['selectedSupplierId'])) {
    $payments = $data['payments'];
    $selectedSupplierId = $data['selectedSupplierId'];

    // Fetch the last payment_id from the database and increment it
    $result = $conn->query("SELECT MAX(payment_no) as last_id FROM payments");
    $row = $result->fetch_assoc();
    $payment_id = $row['last_id'] ? $row['last_id'] + 1 : 1;

    $stmt = $conn->prepare("INSERT INTO payments (payment_no, supplier_id, payment_method, amount, cheque_no, ref_no, date, bank, branch) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $totalAmount = 0;

    foreach ($payments as $payment) {
        $method = $payment['method'];
        $amount = $payment['amount'] ?? 0;
        $chequeNo = $payment['cheque_no'] ?? null;
        $refNo = $payment['ref_no'] ?? null;
        $date = $payment['date'] ?? null;
        $bank = $payment['bank'] ?? null;

        $stmt->bind_param("iisdsssss", $payment_id, $selectedSupplierId, $method, $amount, $chequeNo, $refNo, $date, $bank, $user_branch);
        $stmt->execute();

        $totalAmount += $amount; // Accumulate total payment amount
    }

    $stmt->close();

    
    // Update the supplier's credit_balance
    $updateQuery = "UPDATE suppliers SET credit_balance = credit_balance - ? WHERE supplier_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param('di', $totalAmount, $selectedSupplierId);

    if ($updateStmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update credit balance']);
    }

    $updateStmt->close();

    $conn->close();

} else {
    echo json_encode(['success' => false, 'message' => 'No payment data received.']);
}
?>
