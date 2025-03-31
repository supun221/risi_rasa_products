<?php
header('Content-Type: application/json');

require_once '../../models/Database.php';

try {
    if (!isset($db_conn) || !$db_conn) {
        throw new Exception("Database connection not initialized.");
    }

    $user = isset($_GET['user']) ? trim($db_conn->real_escape_string($_GET['user'])) : '';
    $startDate = isset($_GET['start_date']) ? $db_conn->real_escape_string($_GET['start_date']) : '';
    $endDate = isset($_GET['end_date']) ? $db_conn->real_escape_string($_GET['end_date']) : '';

    $query = "
        SELECT 
            bill_id, 
            cancelled_by, 
            cancelled_date, 
            reason, 
            bill_amount
        FROM deleted_bills
        WHERE 1=1
    ";

    if (!empty($user)) {
        $query .= " AND cancelled_by = '$user'";
    }

    if (!empty($startDate)) {
        $query .= " AND cancelled_date >= '$startDate'";
    }

    if (!empty($endDate)) {
        $query .= " AND cancelled_date <= '$endDate'";
    }

    $query .= " ORDER BY cancelled_date DESC";

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
