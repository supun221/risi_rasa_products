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
            d.bill_id, 
            d.deleted_by, 
            d.deleted_date, 
            d.item_name, 
            d.barcode, 
            d.unit_price, 
            d.quantity, 
            d.discount, 
            d.total_price, 
            d.bill_type
        FROM deleted_bill_items d
        JOIN signup s ON d.deleted_by = s.username
        WHERE 1=1
    ";

    if (!empty($user)) {
        $query .= " AND d.deleted_by = '$user'";
    }

    if (!empty($startDate)) {
        $query .= " AND d.deleted_date >= '$startDate'";
    }

    if (!empty($endDate)) {
        $query .= " AND d.deleted_date <= '$endDate'";
    }

    $query .= " ORDER BY d.deleted_date DESC";

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
