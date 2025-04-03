<?php
// Connect to database
require_once '../../../../config/databade.php';
session_start();

// Initialize response array
$response = [
    'success' => false,
    'invoice_number' => '',
    'next_invoice' => '',
    'message' => ''
];

try {
    // Get JSON data from request body
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);
    
    // Validate data
    if (!$data) {
        throw new Exception('Invalid request data');
    }
    
    // Required fields
    $invoice_number = $data['invoice_number'] ?? '';
    $items = $data['items'] ?? [];
    $subtotal = $data['subtotal'] ?? 0;
    $discount_amount = $data['discount_amount'] ?? 0;
    $net_amount = $data['net_amount'] ?? 0;
    $payment_method = $data['payment_method'] ?? 'cash';
    $paid_amount = $data['paid_amount'] ?? 0;
    $change_amount = $data['change_amount'] ?? 0;
    $credit_amount = $data['credit_amount'] ?? 0;
    $advance_used = $data['advance_used'] ?? 0;
    $print_invoice = $data['print_invoice'] ?? 0;
    
    // Optional fields
    $customer_id = $data['customer_id'] ?? null;
    $customer_name = $data['customer_name'] ?? null;
    
    // Get rep_id from session
    $rep_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1; // Default to 1 for testing
    
    // Get branch from session
    $branch = isset($_SESSION['branch']) ? $_SESSION['branch'] : 'main';
    
    // Validate items
    if (empty($items)) {
        throw new Exception('No items in the cart');
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    // Insert into sales table - FIX: Correct the type definition string
    $stmt = $conn->prepare("
        INSERT INTO pos_sales (
            invoice_number, customer_id, customer_name, total_amount, 
            discount_amount, net_amount, payment_method, paid_amount, 
            change_amount, credit_amount, advance_used, rep_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    // Fix: Update the type string to match the 12 parameters
    $stmt->bind_param(
        "sisdddsdddii", 
        $invoice_number, 
        $customer_id, 
        $customer_name, 
        $subtotal,
        $discount_amount, 
        $net_amount, 
        $payment_method, 
        $paid_amount,
        $change_amount, 
        $credit_amount, 
        $advance_used,
        $rep_id
    );
    
    $stmt->execute();
    
    $sale_id = $conn->insert_id;
    
    // Process each item in the cart
    foreach ($items as $item) {
        // Get item details
        $lorry_stock_id = $item['stockId'];
        $product_name = $item['productName'];
        $quantity = $item['quantity'];
        $free_qty = $item['freeQty'];
        $unit_price = $item['unitPrice'];
        $discount_percent = $item['discountPercent'];
        $discount_amount = $item['discountAmount'];
        $subtotal = $item['subtotal'];
        
        // Insert into sale_items table
        $stmt = $conn->prepare("
            INSERT INTO pos_sale_items (
                sale_id, lorry_stock_id, product_name, quantity, free_quantity,
                unit_price, discount_percent, discount_amount, subtotal
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "iisiidddd",
            $sale_id, $lorry_stock_id, $product_name, $quantity, $free_qty,
            $unit_price, $discount_percent, $discount_amount, $subtotal
        );
        $stmt->execute();
        
        // Update lorry_stock - reduce quantity
        $total_qty_reduction = $quantity + $free_qty;
        
        $stmt = $conn->prepare("
            UPDATE lorry_stock
            SET quantity = quantity - ?,
                status = CASE WHEN quantity - ? <= 0 THEN 'sold' ELSE status END
            WHERE id = ? AND rep_id = ?
        ");
        $stmt->bind_param("iiii", $total_qty_reduction, $total_qty_reduction, $lorry_stock_id, $rep_id);
        $stmt->execute();
    }
    
    // Handle customer credit and advance balances if applicable
    if ($customer_id) {
        // First record the payment in rep_payments
        $stmt = $conn->prepare("
            INSERT INTO rep_payments (
                invoice_number, customer_id, customer_name, amount, payment_method, 
                rep_id, branch, notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $payment_notes = "Sale payment";
        $stmt->bind_param(
            "sisdsiss",
            $invoice_number,
            $customer_id,
            $customer_name,
            $paid_amount,
            $payment_method,
            $rep_id,
            $branch,
            $payment_notes
        );
        $stmt->execute();
        
        // If credit amount is present, update customer credit balance
        if ($credit_amount > 0) {
            $stmt = $conn->prepare("
                UPDATE customers
                SET credit_balance = credit_balance + ?,
                    last_purchase_date = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("di", $credit_amount, $customer_id);
            $stmt->execute();
        }
        
        // If advance was used, deduct from customer advance amount
        if ($advance_used > 0) {
            $stmt = $conn->prepare("
                UPDATE customers
                SET advance_amount = advance_amount - ?,
                    last_purchase_date = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("di", $advance_used, $customer_id);
            $stmt->execute();
        }
    }
    
    // Generate next invoice number for the response
    $prefix = 'INV';
    $date = date('Ymd');
    
    // Get the last used number for today
    $stmt = $conn->prepare("SELECT MAX(invoice_number) as max_num FROM pos_sales WHERE invoice_number LIKE ?");
    $search_pattern = $prefix . $date . '%';
    $stmt->bind_param("s", $search_pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['max_num']) {
        // Extract the sequence number and increment
        $last_num = intval(substr($row['max_num'], -4));
        $next_num = $last_num + 1;
    } else {
        // No invoices yet today, start with 1
        $next_num = 1;
    }
    
    // Format with leading zeros (4 digits)
    $sequence = str_pad($next_num, 4, '0', STR_PAD_LEFT);
    $next_invoice = $prefix . $date . $sequence;
    
    // Commit transaction
    $conn->commit();
    
    // Set response
    $response['success'] = true;
    $response['invoice_number'] = $invoice_number;
    $response['next_invoice'] = $next_invoice;
    $response['message'] = 'Sale completed successfully';
    
} catch (Exception $e) {
    // Roll back transaction on error
    if (isset($conn)) {
        $conn->rollback();
    }
    
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log("Error in save_pos_sale.php: " . $e->getMessage());
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
