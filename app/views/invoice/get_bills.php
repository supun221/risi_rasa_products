<?php
include '../../models/Database.php';
include '../../models/POS_Product.php';

try {
    // Ensure the database connection is established
    if (!isset($db_conn) || !$db_conn) {
        throw new Exception("Database connection not initialized.");
    }

    // Get the range value from the query string or default to '10'
    $range = $_GET['range'] ?? '10';

    // Validate and sanitize the range value
    if ($range === 'all') {
        $range = PHP_INT_MAX; // Fetch all records
    } elseif (!ctype_digit($range)) {
        throw new Exception("Invalid range value.");
    } else {
        $range = intval($range);
    }

    // Prepare the query with a dynamic LIMIT clause and join with the purchase_items table
    $query = "
        SELECT 
            b.*, 
            p.product_name, 
            p.product_mrp, 
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
            b.bill_id DESC 
        LIMIT ?
    ";

    $stmt = $db_conn->prepare($query);

    if (!$stmt) {
        throw new Exception("Failed to prepare query: " . $db_conn->error);
    }

    // Bind the range as an integer and execute the query
    $stmt->bind_param('i', $range);
    $stmt->execute();

    // Fetch the results
    $result = $stmt->get_result();
    $bills = [];

    while ($row = $result->fetch_assoc()) {
        $bill_id = $row['bill_id'];

        // Grouping data by bill_id
        if (!isset($bills[$bill_id])) {
            $bills[$bill_id] = [
                'bill_id' => $row['bill_id'],
                'other_bill_fields' => $row, // Replace this with all other bill fields
                'purchase_items' => []
            ];
        }

        // Add purchase item details for this bill_id
        $bills[$bill_id]['purchase_items'][] = [
            'product_name' => $row['product_name'],
            'product_mrp' => $row['product_mrp'],
            'purchase_qty' => $row['purchase_qty'],
            'discount_percentage' => $row['discount_percentage'],
            'subtotal' => $row['subtotal']
        ];
    }

    // Return the results as JSON
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'data' => array_values($bills)]);
} catch (Exception $e) {
    // Handle errors and return them as JSON
    header('Content-Type: application/json', true, 500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
