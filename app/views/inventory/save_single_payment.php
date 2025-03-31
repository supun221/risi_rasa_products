<?php
// require 'connection_db.php';

// $data = json_decode(file_get_contents('php://input'), true);
// $paymentMethod = $data['paymentMethod'];
// $amount = $data['amount'];
// $chequeNo = $data['chequeNo'] ?? null;
// $refNo = $data['refNo'] ?? null;
// $date = $data['date'] ?? null;
// $bank = $data['bank'] ?? null;
// $selectedSupplierId = $data['selectedSupplierId'];

// // Fetch the last payment_id from the database and increment it
// $result = $conn->query("SELECT MAX(payment_no) as last_id FROM payments");
// $row = $result->fetch_assoc();
// $payment_id = $row['last_id'] ? $row['last_id'] + 1 : 1;

// $query = "INSERT INTO payments (payment_no, supplier_id, payment_method, amount, cheque_no, ref_no, date, bank) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
// $stmt = $conn->prepare($query);
// $stmt->bind_param('iisdssss', $payment_id, $selectedSupplierId, $paymentMethod, $amount, $chequeNo, $refNo, $date, $bank);

// if ($stmt->execute()) {
//     echo json_encode(['success' => true]);
// } else {
//     echo json_encode(['success' => false, 'message' => 'Failed to save payment']);
// }

// $stmt->close();
// $conn->close();
?>

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
$paymentMethod = $data['paymentMethod'];
$amount = $data['amount'];
$chequeNo = $data['chequeNo'] ?? null;
$refNo = $data['refNo'] ?? null;
$date = $data['date'] ?? null;
$bank = $data['bank'] ?? null;
$selectedSupplierId = $data['selectedSupplierId'];

// Fetch the last payment_id from the database and increment it
$result = $conn->query("SELECT MAX(payment_no) as last_id FROM payments");
$row = $result->fetch_assoc();
$payment_id = $row['last_id'] ? $row['last_id'] + 1 : 1;

$query = "INSERT INTO payments (payment_no, supplier_id, payment_method, amount, cheque_no, ref_no, date, bank, branch, paid_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param('iisdssssss', $payment_id, $selectedSupplierId, $paymentMethod, $amount, $chequeNo, $refNo, $date, $bank, $user_branch, $user_name);

if ($stmt->execute()) {
    // Update the supplier's credit_balance
    $updateQuery = "UPDATE suppliers SET credit_balance = credit_balance - ? WHERE supplier_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param('di', $amount, $selectedSupplierId);

    if ($updateStmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update credit balance']);
    }

    $updateStmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save payment']);
}

$stmt->close();
$conn->close();
?>

