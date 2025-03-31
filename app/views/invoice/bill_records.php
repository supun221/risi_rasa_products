
<?php
header('Content-Type: application/json');

require_once '../../models/Database.php'; // Include the database connection
require_once '../../models/POS_Product.php'; // Include the POS_Product model

try {
    // Ensure the database connection is established
    if (!isset($db_conn) || !$db_conn) {
        throw new Exception("Database connection not initialized.");
    }

    // Get the range value from the query string or default to '10'
    $range = $_GET['range'] ?? '10';

    // Validate and sanitize the range value
    if ($range === 'all') {
        $limitClause = ""; // No limit if 'all' is selected
    } elseif (ctype_digit($range)) {
        $range = intval($range);
        $limitClause = "LIMIT ?";
    } else {
        throw new Exception("Invalid range value.");
    }

    // Fetch the bill records based on the range
    $queryDetails = "
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
        ORDER BY 
            b.id DESC
    ";

    $stmtDetails = $db_conn->prepare($queryDetails);
    if (!$stmtDetails) {
        throw new Exception("Failed to prepare query for details: " . $db_conn->error);
    }

    // Bind the range value if applicable
    // if ($limitClause === "LIMIT ?") {
    //     $stmtDetails->bind_param('i', $range);
    // }

    $stmtDetails->execute();
    $resultDetails = $stmtDetails->get_result();

    // Group results by bill_id
    $bills = [];
    while ($row = $resultDetails->fetch_assoc()) {
        $bill_id = $row['bill_id'];

        if (!isset($bills[$bill_id])) {
            $bills[$bill_id] = [
                'bill_id' => htmlspecialchars($row['bill_id'], ENT_QUOTES, 'UTF-8'),
                'customer_id' => htmlspecialchars($row['customer_id'], ENT_QUOTES, 'UTF-8'),
                'gross_amount' => $row['gross_amount'],
                'net_amount' => $row['net_amount'],
                'discount_amount' => $row['discount_amount'],
                'num_of_products' => $row['num_of_products'],
                'payment_type' => htmlspecialchars($row['payment_type'], ENT_QUOTES, 'UTF-8'),
                'balance' => $row['balance'],
                'bill_date' => $row['bill_date'],
                'purchase_items' => [] // Ensure it's always an array
            ];
        }

        // Add purchase item details for this bill_id
        if (!empty($row['product_name'])) { // Only add items if they exist
            $bills[$bill_id]['purchase_items'][] = [
                'product_name' => htmlspecialchars($row['product_name'], ENT_QUOTES, 'UTF-8'),
                'price' => $row['price'],
                'purchase_qty' => $row['purchase_qty'],
                'discount_percentage' => $row['discount_percentage'],
                'subtotal' => $row['subtotal']
            ];
        }
    }

    // Return the results as JSON
    echo json_encode(['success' => true, 'data' => array_values($bills)], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
} catch (Exception $e) {
    // Handle errors and return them as JSON
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
