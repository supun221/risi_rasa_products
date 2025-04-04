<?php
// Connect to database
require_once '../../../../config/databade.php';
session_start();

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'details' => []
];

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $stock_id = isset($_POST['stock_id']) ? (int)$_POST['stock_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    $reason = isset($_POST['reason']) ? $_POST['reason'] : '';
    $note = isset($_POST['note']) ? $_POST['note'] : '';
    
    // Get rep_id from session
    $rep_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    
    // Validate input
    if ($stock_id <= 0) {
        $response['message'] = 'Invalid stock item selected';
        echo json_encode($response);
        exit;
    }
    
    if ($quantity <= 0) {
        $response['message'] = 'Quantity must be greater than 0';
        echo json_encode($response);
        exit;
    }
    
    if (empty($reason)) {
        $response['message'] = 'Please select a reason for retrieving stock';
        echo json_encode($response);
        exit;
    }
    
    if ($rep_id <= 0) {
        $response['message'] = 'User session not found or expired';
        echo json_encode($response);
        exit;
    }
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Get stock item details
        $stmt = $conn->prepare("
            SELECT ls.id, ls.stock_entry_id, ls.product_name, ls.quantity, 
                   ls.unit_price, ls.barcode, se.available_stock, se.itemcode
            FROM lorry_stock ls
            LEFT JOIN stock_entries se ON ls.stock_entry_id = se.id
            WHERE ls.id = ? AND ls.rep_id = ? AND ls.status = 'active'
        ");
        $stmt->bind_param("ii", $stock_id, $rep_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Stock item not found in your inventory');
        }
        
        $stock_item = $result->fetch_assoc();
        $stock_entry_id = $stock_item['stock_entry_id'];
        
        // Check if quantity is available
        if ($stock_item['quantity'] < $quantity) {
            throw new Exception('Requested quantity exceeds available stock (' . $stock_item['quantity'] . ' available)');
        }
        
        // Calculate new quantity after retrieval
        $new_quantity = $stock_item['quantity'] - $quantity;
        
        // Calculate total value of retrieved items
        $total_amount = $quantity * $stock_item['unit_price'];
        
        // Update lorry_stock quantity or status if empty
        $status = $new_quantity <= 0 ? 'retrieved' : 'active';
        
        $stmt = $conn->prepare("
            UPDATE lorry_stock 
            SET quantity = ?, status = ?
            WHERE id = ? AND rep_id = ?
        ");
        $stmt->bind_param("isii", $new_quantity, $status, $stock_id, $rep_id);
        $stmt->execute();
        
        // If reason is 'return', update main stock quantity
        if ($reason === 'return') {
            // Add the quantity back to stock_entries
            $stmt = $conn->prepare("
                UPDATE stock_entries 
                SET available_stock = available_stock + ? 
                WHERE id = ?
            ");
            $stmt->bind_param("ii", $quantity, $stock_entry_id);
            $stmt->execute();
            
            // Store the new available stock for response
            $new_available_stock = $stock_item['available_stock'] + $quantity;
        }
        
        // Log transaction in lorry_transactions table
        $stmt = $conn->prepare("
            INSERT INTO lorry_transactions (
                stock_entry_id, rep_id, transaction_type, quantity, 
                reason, barcode, price, total_amount, customer_name
            ) VALUES (?, ?, 'retrieve', ?, ?, ?, ?, ?, ?)
        ");
        
        // Fix the bind_param call - ensure the type string matches the number of parameters
        // We have 8 parameters, so we need 8 type identifiers
        $stmt->bind_param(
            "iisssdds",  // Fixed: 8 type identifiers for 8 parameters
            $stock_entry_id,  // i - stock_entry_id (integer)
            $rep_id,          // i - rep_id (integer)
            $quantity,        // s - quantity (string/number)
            $reason,          // s - reason (string)
            $stock_item['barcode'], // s - barcode (string)
            $stock_item['unit_price'], // d - price (double/float)
            $total_amount,    // d - total_amount (double/float)
            $note             // s - customer_name/note (string)
        );
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Prepare success response
        $response['success'] = true;
        $response['message'] = 'Stock retrieved successfully';
        $response['details'] = [
            'product' => $stock_item['product_name'],
            'quantity' => $quantity,
            'remaining' => $new_quantity,
            'reason' => $reason,
            'total_value' => $total_amount
        ];
        
        // Add info about returned stock if applicable
        if ($reason === 'return') {
            $response['details']['returned_to_warehouse'] = true;
            $response['details']['new_warehouse_stock'] = $new_available_stock;
        } else {
            $response['details']['returned_to_warehouse'] = false;
        }
        
    } catch (Exception $e) {
        // Roll back transaction on error
        $conn->rollback();
        $response['message'] = 'Error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method. POST required.';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
