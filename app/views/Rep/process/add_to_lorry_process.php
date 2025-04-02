<?php
// Process add to lorry form data
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
    $stock_entry_id = isset($_POST['stock_entry_id']) ? (int)$_POST['stock_entry_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $total_amount = isset($_POST['total_amount']) ? (float)$_POST['total_amount'] : 0;
    
    // Get rep_id from session (assuming it's stored there)
    $rep_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1; // Default to 1 for testing
    
    // Validate input
    if ($stock_entry_id <= 0 || $quantity <= 0) {
        $response['message'] = 'Invalid product or quantity';
        echo json_encode($response);
        exit;
    }
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Get product information and check if enough stock is available
        $stmt = $conn->prepare("
            SELECT stock_id, itemcode, product_name, available_stock, wholesale_price, barcode
            FROM stock_entries 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $stock_entry_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Product not found');
        }
        
        $product = $result->fetch_assoc();
        
        if ($product['available_stock'] < $quantity) {
            throw new Exception('Not enough stock available');
        }
        
        // Update stock in stock_entries table
        $new_available_stock = $product['available_stock'] - $quantity;
        
        $stmt = $conn->prepare("
            UPDATE stock_entries 
            SET available_stock = ? 
            WHERE id = ?
        ");
        $stmt->bind_param("ii", $new_available_stock, $stock_entry_id);
        $stmt->execute();
        
        // Record transaction in lorry_stock table
        $stmt = $conn->prepare("
            INSERT INTO lorry_stock 
            (stock_entry_id, rep_id, product_name, itemcode, quantity, barcode, unit_price, total_amount) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iissssdd", 
            $stock_entry_id, 
            $rep_id, 
            $product['product_name'], 
            $product['itemcode'], 
            $quantity, 
            $product['barcode'], 
            $price, 
            $total_amount
        );
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        $response['success'] = true;
        $response['message'] = 'Product added to lorry successfully';
        
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
