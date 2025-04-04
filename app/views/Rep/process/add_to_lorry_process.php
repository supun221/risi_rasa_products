<?php
// Process add to lorry form data
session_start();
require_once '../../../../config/databade.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'debug_info' => [] // Add debug info to track parameter values
];

// Check if form data is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $stock_entry_id = isset($_POST['stock_entry_id']) ? (int)$_POST['stock_entry_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    $rep_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    
    // Store debug info
    $response['debug_info'] = [
        'stock_entry_id' => $stock_entry_id,
        'quantity' => $quantity,
        'rep_id' => $rep_id,
        'raw_post' => $_POST,
        'session_user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set'
    ];
    
    // Detailed validation with specific error messages
    $errors = [];
    if ($stock_entry_id <= 0) {
        $errors[] = "Invalid product selection (stock_entry_id: $stock_entry_id)";
    }
    if ($quantity <= 0) {
        $errors[] = "Invalid quantity: must be greater than 0";
    }
    if ($rep_id <= 0) {
        $errors[] = "Invalid rep ID: user not properly logged in";
    }
    
    // Check if there are any validation errors
    if (!empty($errors)) {
        $response['message'] = implode(", ", $errors);
        $response['validation_errors'] = $errors;
        echo json_encode($response);
        exit;
    }
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Get stock entry details and verify available quantity
        $stmt = $conn->prepare("
            SELECT stock_id, itemcode, product_name, barcode, wholesale_price, max_retail_price, available_stock 
            FROM stock_entries 
            WHERE id = ?
        ");
        $stmt->bind_param("i", $stock_entry_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception('Stock entry not found with ID: ' . $stock_entry_id);
        }
        
        $stock_entry = $result->fetch_assoc();
        
        // Check if there's enough stock available
        if ($stock_entry['available_stock'] < $quantity) {
            throw new Exception('Insufficient stock. Only ' . $stock_entry['available_stock'] . ' available.');
        }
        
        // Log stock entry details for debugging
        $response['debug_info']['stock_entry'] = $stock_entry;
        
        // Check if this product already exists in lorry stock for this rep
        $stmt = $conn->prepare("
            SELECT id, quantity, unit_price FROM lorry_stock 
            WHERE stock_entry_id = ? AND rep_id = ? AND status = 'active'
        ");
        $stmt->bind_param("ii", $stock_entry_id, $rep_id);
        $stmt->execute();
        $existing_result = $stmt->get_result();
        $exists = $existing_result->num_rows > 0;
        
        // Calculate total amount for transaction
        $total_amount = $quantity * $stock_entry['wholesale_price'];
        
        if ($exists) {
            // Update existing lorry stock record
            $existing_stock = $existing_result->fetch_assoc();
            $new_quantity = $existing_stock['quantity'] + $quantity;
            
            $stmt = $conn->prepare("
                UPDATE lorry_stock 
                SET quantity = ? 
                WHERE id = ? AND rep_id = ?
            ");
            $stmt->bind_param("iii", $new_quantity, $existing_stock['id'], $rep_id);
            $stmt->execute();
            
            $response['message'] = 'Stock updated successfully. Added ' . $quantity . ' to existing stock.';
        } else {
            // Insert new lorry stock record
            $stmt = $conn->prepare("
                INSERT INTO lorry_stock (
                    stock_entry_id, rep_id, product_name, itemcode, quantity, barcode, unit_price, total_amount
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                "iississd", 
                $stock_entry_id, 
                $rep_id, 
                $stock_entry['product_name'], 
                $stock_entry['itemcode'], 
                $quantity, 
                $stock_entry['barcode'], 
                $stock_entry['wholesale_price'], 
                $total_amount
            );
            $stmt->execute();
            
            $response['message'] = 'Stock added successfully.';
        }
        
        // CRITICAL: Update available_stock in stock_entries table
        $new_available_stock = $stock_entry['available_stock'] - $quantity;
        $stmt = $conn->prepare("
            UPDATE stock_entries 
            SET available_stock = ? 
            WHERE id = ?
        ");
        $stmt->bind_param("ii", $new_available_stock, $stock_entry_id);
        $stmt->execute();
        
        // Add record to lorry_transactions
        $stmt = $conn->prepare("
            INSERT INTO lorry_transactions (
                stock_entry_id, rep_id, transaction_type, quantity, reason, barcode, price, total_amount
            ) VALUES (?, ?, 'add', ?, 'lorry_stock', ?, ?, ?)
        ");
        $stmt->bind_param(
            "iissdd",
            $stock_entry_id,
            $rep_id,
            $quantity,
            $stock_entry['barcode'],
            $stock_entry['wholesale_price'],
            $total_amount
        );
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Add information about updated stock to response
        $response['success'] = true;
        $response['updated_stock'] = [
            'product_name' => $stock_entry['product_name'],
            'previous_stock' => $stock_entry['available_stock'],
            'new_stock' => $new_available_stock,
            'quantity_added_to_lorry' => $quantity
        ];
        
    } catch (Exception $e) {
        // Roll back transaction on error
        $conn->rollback();
        $response['message'] = 'Error: ' . $e->getMessage();
        $response['debug_info']['exception'] = $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method';
}

// Return JSON response with debug info in development environment
header('Content-Type: application/json');
echo json_encode($response);
?>
