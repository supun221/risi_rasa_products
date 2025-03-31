<?php
header('Content-Type: application/json');

require_once '../../models/Database.php';

try {
    if (!isset($db_conn) || !$db_conn) {
        throw new Exception("Database connection not initialized.");
    }

    $startDate = isset($_GET['start_date']) ? $db_conn->real_escape_string($_GET['start_date']) : '';
    $endDate = isset($_GET['end_date']) ? $db_conn->real_escape_string($_GET['end_date']) : '';

    $query = "
        SELECT 
            b.bill_id, 
            b.customer_id, 
            b.gross_amount, 
            b.net_amount, 
            b.discount_amount, 
            b.num_of_products, 
            b.payment_type, 
            b.balance, 
            b.bill_date,
            p.product_name, 
            p.price, 
            p.purchase_qty, 
            p.discount_percentage, 
            p.subtotal
        FROM 
            bill_records AS b
        LEFT JOIN 
            purchase_items AS p 
        ON 
            b.bill_id = p.bill_id
        WHERE 1=1
    ";

    if (!empty($startDate)) {
        $query .= " AND b.bill_date >= '$startDate'";
    }

    if (!empty($endDate)) {
        $query .= " AND b.bill_date <= '$endDate'";
    }

    $query .= " ORDER BY b.bill_id DESC";

    $result = $db_conn->query($query);

    $bills = [];
    while ($row = $result->fetch_assoc()) {
        $bill_id = $row['bill_id'];

        if (!isset($bills[$bill_id])) {
            $bills[$bill_id] = [
                'bill_id' => $row['bill_id'],
                'customer_id' => $row['customer_id'],
                'gross_amount' => $row['gross_amount'],
                'net_amount' => $row['net_amount'],
                'discount_amount' => $row['discount_amount'],
                'num_of_products' => $row['num_of_products'],
                'payment_type' => $row['payment_type'],
                'balance' => $row['balance'],
                'bill_date' => $row['bill_date'],
                'purchase_items' => []
            ];
        }

        if ($row['product_name']) {
            $bills[$bill_id]['purchase_items'][] = [
                'product_name' => $row['product_name'],
                'price' => $row['price'],
                'purchase_qty' => $row['purchase_qty'],
                'discount_percentage' => $row['discount_percentage'],
                'subtotal' => $row['subtotal']
            ];
        }
    }

    echo json_encode(['success' => true, 'data' => array_values($bills)]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
