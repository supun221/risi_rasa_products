<?php
header('Content-Type: application/json');
require_once '../../models/Database.php';

try {
    if (!isset($db_conn) || !$db_conn) {
        throw new Exception("Database connection not initialized.");
    }

    $category = isset($_GET['category']) ? $db_conn->real_escape_string($_GET['category']) : '';
    $barcode = isset($_GET['barcode']) ? $db_conn->real_escape_string($_GET['barcode']) : '';
    $productName = isset($_GET['product_name']) ? $db_conn->real_escape_string($_GET['product_name']) : '';
    $startDate = isset($_GET['start_date']) ? $db_conn->real_escape_string($_GET['start_date']) : '';
    $endDate = isset($_GET['end_date']) ? $db_conn->real_escape_string($_GET['end_date']) : '';
    $issuer = isset($_GET['issuer']) ? $db_conn->real_escape_string($_GET['issuer']) : '';

    $query = "
        SELECT 
            p.product_name, 
            p.category, 
            pi.product_barcode, 
            pi.price, 
            SUM(pi.purchase_qty) AS total_qty, 
            AVG(pi.discount_percentage) AS discount_percentage, 
            SUM(pi.subtotal) AS total_subtotal,
            pi.purchased_date,
            br.issuer AS user,
            br.payment_type
        FROM purchase_items pi
        JOIN products p ON pi.product_name = p.product_name
        JOIN bill_records br ON pi.bill_id = br.bill_id
        WHERE 1=1
    ";

    if (!empty($category)) {
        $query .= " AND p.category = '$category'";
    }

    if (!empty($barcode)) {
        $query .= " AND pi.product_barcode = '$barcode'";
    }

    if (!empty($productName)) {
        $query .= " AND p.product_name LIKE '%$productName%'";
    }

    if (!empty($startDate) && !empty($endDate)) {
        $query .= " AND pi.purchased_date BETWEEN '$startDate' AND '$endDate'";
    }

    if (!empty($issuer)) {
        $query .= " AND br.issuer = '$issuer'";
    }

    $query .= " GROUP BY pi.product_barcode, pi.price, pi.purchased_date, br.issuer, br.payment_type ORDER BY pi.purchased_date DESC";

    $result = $db_conn->query($query);
    $response = [];
    $totalCash = 0;
    $totalCard = 0;

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Ensure total_subtotal is treated as a number
            $subtotal = (float) $row['total_subtotal'];

            if (strtolower($row['payment_type']) === 'cash_payment') {
                $totalCash += $subtotal;
            } elseif (strtolower($row['payment_type']) === 'card_payment') {
                $totalCard += $subtotal;
            }

            $response[] = $row;
        }
    }

    echo json_encode([
        'success' => true,
        'data' => $response,
        'total_cash' => number_format($totalCash, 2, '.', ''),
        'total_card' => number_format($totalCard, 2, '.', '')
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
