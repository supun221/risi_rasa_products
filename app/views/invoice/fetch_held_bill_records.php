<?php
include '../../models/Database.php';
include '../../models/POS_Product.php';
$productHandler = new POS_PRODUCT($db_conn);

session_start();
$user_name = $_SESSION['username'] ?? null;
$user_role = $_SESSION['job_role'] ?? null;
$user_branch = $_SESSION['store'] ?? null;

// Fetch all hold bills
$query = "SELECT bill_id FROM hold_bill_records WHERE branch = ? AND held_by = ? ORDER BY bill_id ASC";
$stmt = $db_conn->prepare($query);
$stmt->bind_param('ss', $user_branch, $user_name);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($invoice = $result->fetch_assoc()) {
        $bill_id = htmlspecialchars($invoice['bill_id']);
        echo '<div class="held-invoice-item">
                <button class="held-invoice-btn" data-bill-no="' . $bill_id . '">' . $bill_id . '</button>
                <span class="held-invoice-delete-icon" data-bill-no="' . $bill_id . '"
                onclick="deleteHeldInvoice(\'' . $bill_id . '\')">
                    <i class="fa fa-trash" aria-hidden="true"></i>
                </span> <!-- delete icon -->
              </div><br>';
    }
} else {
    echo "No held invoices available.";
}
?>