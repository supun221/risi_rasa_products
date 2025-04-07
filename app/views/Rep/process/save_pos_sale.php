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
    $cheque_number = null;  // Default to null
    
    // If payment method is cheque, get the cheque number
    if ($payment_method === 'cheque' && isset($data['cheque_number'])) {
        $cheque_number = $data['cheque_number'];
    }
    
    // Optional fields
    $customer_id = $data['customer_id'] ?? null;
    $customer_name = $data['customer_name'] ?? null;
    
    // Get rep_id from session
    $rep_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1; // Default to 1 for testing
    
    // Get branch from session - using store value instead of branch
    $branch = isset($_SESSION['store']) ? $_SESSION['store'] : 'main';
    
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
        if ($payment_method === 'cheque') {
            // Include cheque number in the query
            $stmt = $conn->prepare("
                INSERT INTO rep_payments (
                    invoice_number, customer_id, customer_name, amount, payment_method, cheque_num, 
                    rep_id, branch, notes
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $payment_notes = "Sale payment";
            $stmt->bind_param(
                "sisdsisss",
                $invoice_number,
                $customer_id,
                $customer_name,
                $paid_amount,
                $payment_method,
                $cheque_number,
                $rep_id,
                $branch,
                $payment_notes
            );
        } else {
            // Normal payment record without cheque number
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
        }
        
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
        
        // If advance was used, update both advance_payments and customers tables
        if ($advance_used > 0) {
            // First verify that customer has sufficient advance
            $stmt = $conn->prepare("SELECT advance_amount FROM customers WHERE id = ?");
            $stmt->bind_param("i", $customer_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $customer = $result->fetch_assoc();
                $available_advance = (float)$customer['advance_amount'];
                
                // Make sure we don't use more than available
                if ($advance_used > $available_advance) {
                    $advance_used = $available_advance;
                }
                
                // Only proceed if there's advance to use
                if ($advance_used > 0) {
                    // Get the latest advance payment record for history tracking
                    $stmt = $conn->prepare("
                        SELECT id, net_amount, advance_bill_number
                        FROM advance_payments 
                        WHERE customer_id = ? 
                        ORDER BY created_at DESC 
                        LIMIT 1
                    ");
                    $stmt->bind_param("i", $customer_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $advance_record = $result->fetch_assoc();
                        $advance_id = $advance_record['id'];
                        $current_advance = $advance_record['net_amount'];
                        $advance_bill_number = $advance_record['advance_bill_number'];
                        
                        // Calculate new balance
                        $new_advance_balance = $current_advance - $advance_used;
                        
                        // Update the advance payment record for history tracking
                        $stmt = $conn->prepare("
                            UPDATE advance_payments 
                            SET net_amount = ?,
                                reason = CONCAT(IFNULL(reason, ''), '\nUsed Rs. " . number_format($advance_used, 2) . " for invoice " . $invoice_number . " on " . date('Y-m-d H:i:s') . "')
                            WHERE id = ?
                        ");
                        $stmt->bind_param("di", $new_advance_balance, $advance_id);
                        $stmt->execute();
                    }
                    
                    // CRITICAL: Update the customer's advance_amount in the customers table
                    // This is the most important update - it ensures the customer's balance is reduced
                    $stmt = $conn->prepare("
                        UPDATE customers 
                        SET advance_amount = advance_amount - ?,
                            last_purchase_date = NOW()
                        WHERE id = ?
                    ");
                    $stmt->bind_param("di", $advance_used, $customer_id);
                    $stmt->execute();
                    
                    // Log the transaction
                    $stmt = $conn->prepare("
                        INSERT INTO lorry_transactions 
                        (rep_id, transaction_type, reason, customer_name, total_amount) 
                        VALUES (?, 'advance_used', ?, ?, ?)
                    ");
                    $reason = "Used advance payment for invoice " . $invoice_number;
                    $stmt->bind_param("issd", $rep_id, $reason, $customer_name, $advance_used);
                    $stmt->execute();
                }
            }
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
