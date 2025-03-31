<?php
include '../../models/Database.php';
include '../../models/POS_Stock.php';

header('Content-Type: application/json');

$posStock = new POS_STOCK($db_conn);
session_start();

if (!isset($_SESSION['store'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$branch = $_SESSION['store'];
$records = $posStock->getStockTransferRecordsByTransferredBranch($branch);

if ($records !== false) {
    echo json_encode(['status' => 'success', 'data' => $records]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch records']);
}
?>
