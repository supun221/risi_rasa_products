<?php
header('Content-Type: application/json');
require_once '../../models/Database.php';

try {
    if (!isset($db_conn) || !$db_conn) {
        throw new Exception("Database connection not initialized.");
    }

    $user = isset($_GET['user']) ? trim($db_conn->real_escape_string($_GET['user'])) : '';
    $startDate = isset($_GET['start_date']) && !empty($_GET['start_date']) ? $db_conn->real_escape_string($_GET['start_date']) : null;
    $endDate = isset($_GET['end_date']) && !empty($_GET['end_date']) ? $db_conn->real_escape_string($_GET['end_date']) : null;

    $query = "
        SELECT 
            username, 
            opening_balance, 
            total_gross, 
            total_net, 
            total_discount, 
            total_bills, 
            total_cash, 
            total_credit, 
            bill_payment, 
            cash_drawer, 
            voucher_payment, 
            free_payment, 
            total_balance, 
            day_end_hand_balance, 
            cash_balance, 
            today_balance, 
            difference_hand, 
            created_at
        FROM day_end_reports
        WHERE 1=1
    ";

    if (!empty($user)) {
        $query .= " AND username = '$user'";
    }

    if (!empty($startDate) && !empty($endDate)) {
        $query .= " AND created_at BETWEEN '$startDate' AND '$endDate'";
    } elseif (!empty($startDate)) {
        $query .= " AND created_at >= '$startDate'";
    } elseif (!empty($endDate)) {
        $query .= " AND created_at <= '$endDate'";
    }

    $query .= " ORDER BY created_at DESC";

    $result = $db_conn->query($query);
    $response = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $response[] = $row;
        }
    }

    echo json_encode(['success' => true, 'data' => $response]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
