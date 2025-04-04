<?php
// Process retrieve stock form data
session_start();
require_once '../../../../config/databade.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => ''
];

// Check if form data is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $lorry_stock_id = isset($_POST['stock_id']) ? (int)$_POST['stock_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    $reason = isset($_POST['reason']) ? $_POST['reason'] : '';
    $customer = isset($_POST['customer']) ? $_POST['customer'] : null;
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $total_amount = isset($_POST['total_amount']) ? (float)$_POST['total_amount'] : 0;
    
    // Get rep_id from session
    $rep_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1; // Default to 1 for testing
    
    // Validate input
    if ($lorry_stock_id <= 0 || $quantity <= 0 || empty($reason)) {
        $response['message'] = 'Invalid product, quantity or reason';
        echo json_encode($response);
        exit;
    }
    
    // Validate customer name for sales
    if ($reason === 'sale' && empty($customer)) {
        $response['message'] = 'Customer name is required for sales';
        echo json_encode($response);
        exit;
    }
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Get lorry stock information
        $stmt = $conn->prepare("
            SELECT ls.id, ls.stock_entry_id, ls.product_name, ls.quantity, ls.barcode, ls.unit_price,
                   se.available_stock
            FROM lorry_stock ls
            JOIN stock_entries se ON ls.stock_entry_id = se.id
            WHERE ls.id = ? AND ls.rep_id = ?
        ");
        $stmt->bind_param("ii", $lorry_stock_id, $rep_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Product not found in lorry stock');
        }
        
        $lorry_product = $result->fetch_assoc();
        $stock_entry_id = $lorry_product['stock_entry_id'];
        
        // Check if enough quantity is available in lorry stock
        if ($lorry_product['quantity'] < $quantity) {
            throw new Exception('Not enough stock available in lorry');
        }
        
        // Update lorry stock quantity
        $new_lorry_quantity = $lorry_product['quantity'] - $quantity;
        $status = $new_lorry_quantity <= 0 ? 'retrieved' : 'active';
        
        $stmt = $conn->prepare("
            UPDATE lorry_stock 
            SET quantity = ?,
                status = ?
            WHERE id = ? AND rep_id = ?
        ");
        $stmt->bind_param("isii", $new_lorry_quantity, $status, $lorry_stock_id, $rep_id);
        $stmt->execute();
        
        // If reason is 'return', add the quantity back to stock_entries
        if ($reason === 'return') {
            // Update stock_entries to increase available_stock
            $new_available_stock = $lorry_product['available_stock'] + $quantity;
            
            $stmt = $conn->prepare("
                UPDATE stock_entries 
                SET available_stock = ? 
                WHERE id = ?
            ");
            $stmt->bind_param("ii", $new_available_stock, $stock_entry_id);
            $stmt->execute();
        }
        
        // Record transaction in lorry_transactions table
        $stmt = $conn->prepare("
            INSERT INTO lorry_transactions 
            (stock_entry_id, rep_id, transaction_type, quantity, reason, customer_name, barcode, price, total_amount) 
            VALUES (?, ?, 'retrieve', ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iissssddd", 
            $stock_entry_id, 
            $rep_id, 
            $quantity, 
            $reason, 
            $customer, 
            $lorry_product['barcode'], 
            $price, 
            $total_amount
        );
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        $response['success'] = true;
        $response['message'] = 'Stock retrieved successfully';
        
        // Add details about the operation for the client
        $response['details'] = [
            'product' => $lorry_product['product_name'],
            'quantity_retrieved' => $quantity,
            'remaining_in_lorry' => $new_lorry_quantity,
            'reason' => $reason
        ];
        
        if ($reason === 'return') {
            $response['details']['returned_to_stock'] = true;
            $response['details']['new_available_stock'] = $new_available_stock;
        }
        
    } catch (Exception $e) {
        // Roll back transaction on error
        $conn->rollback();
        $response['message'] = 'Error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
