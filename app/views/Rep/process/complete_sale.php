<?php
// Connect to database
require_once '../../../../config/databade.php';
session_start();

// Initialize response
$response = [
    'success' => false,
    'message' => '',
    'invoice_id' => null,
    'invoice_number' => null
];

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get rep_id from session
        $rep_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        
        if (!$rep_id) {
            throw new Exception("User session not found. Please log in again.");
        }
        
        // Get cart items
        if (!isset($_SESSION['pos_cart']) || empty($_SESSION['pos_cart'])) {
            throw new Exception("Your cart is empty. Please add items before completing the sale.");
        }
        
        // Get form data
        $payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : 'cash';
        $paid_amount = isset($_POST['paid_amount']) ? (float)$_POST['paid_amount'] : 0;
        $invoice_number = isset($_POST['invoice_number']) ? trim($_POST['invoice_number']) : '';
        $cheque_number = isset($_POST['cheque_number']) ? trim($_POST['cheque_number']) : null;
        $return_bill_number = isset($_POST['return_bill_number']) ? trim($_POST['return_bill_number']) : null;
        $customer_id = isset($_POST['customer_id']) ? (int)$_POST['customer_id'] : null;
        $customer_name = isset($_POST['customer_name']) ? trim($_POST['customer_name']) : 'Walk-in Customer';
        
        // Log incoming data for debug purposes
        error_log("Sale data received: " . json_encode($_POST));
        
        // Check if use_advance is set and convert to boolean
        $use_advance = (isset($_POST['use_advance']) && ($_POST['use_advance'] === 'true' || $_POST['use_advance'] === true));
        $advance_amount = isset($_POST['advance_amount']) ? (float)$_POST['advance_amount'] : 0;
        
        error_log("Use advance: " . ($use_advance ? 'true' : 'false') . ", Advance amount: $advance_amount");
        
        // Start transaction
        $conn->begin_transaction();
        
        // Calculate totals
        $subtotal = 0;
        $total_discount = 0;
        $grand_total = 0;
        $total_items = count($_SESSION['pos_cart']);
        
        foreach ($_SESSION['pos_cart'] as $item) {
            $item_subtotal = $item['quantity'] * $item['unit_price'];
            $subtotal += $item_subtotal;
            
            // Calculate discount
            $percent_discount = $item_subtotal * ($item['discount_percent'] / 100);
            $fixed_discount = $item['discount_amount'];
            $item_total_discount = $percent_discount + $fixed_discount;
            $total_discount += $item_total_discount;
        }
        
        $grand_total = $subtotal - $total_discount;
        
        // Calculate credit amount (if applicable)
        $credit_amount = 0;
        if ($payment_method === 'credit') {
            $credit_amount = max(0, $grand_total - $paid_amount);
            
            // Ensure customer is selected for credit payment
            if (!$customer_id) {
                throw new Exception("Customer selection is required for credit payment.");
            }
        }
        
        // Apply return bill amount (if applicable)
        $return_bill_amount = 0;
        if ($return_bill_number) {
            // Verify return bill exists and is valid
            // Modified to include both 'approved' and 'pending' statuses
            $stmt = $conn->prepare("
                SELECT id, total_amount, approval_status 
                FROM return_collections 
                WHERE return_bill_number = ? AND rep_id = ? 
                AND approval_status IN ('approved', 'pending') 
                AND used_in_invoice IS NULL
            ");
            $stmt->bind_param("si", $return_bill_number, $rep_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Invalid return bill number or already used.");
            }
            
            $return_bill = $result->fetch_assoc();
            $return_bill_amount = (float)$return_bill['total_amount'];
            
            // Update return_collections to mark as used
            $stmt = $conn->prepare("
                UPDATE return_collections 
                SET used_in_invoice = ?, used_date = NOW() 
                WHERE id = ?
            ");
            $stmt->bind_param("si", $invoice_number, $return_bill['id']);
            if (!$stmt->execute()) {
                throw new Exception("Failed to update return bill status: " . $stmt->error);
            }
        }
        
        // Use customer advance amount (if applicable)
        $advance_used = 0;
        if ($use_advance && $customer_id && $advance_amount > 0) {
            error_log("Processing advance payment for customer ID: $customer_id");
            
            // Get current customer's advance amount from customers table
            $stmt = $conn->prepare("SELECT advance_amount FROM customers WHERE id = ?");
            $stmt->bind_param("i", $customer_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $customer = $result->fetch_assoc();
                $current_advance_amount = (float)$customer['advance_amount'];
                
                error_log("Current customer advance amount: $current_advance_amount");
                
                // Calculate how much advance to use (up to the bill amount or available advance)
                $advance_used = min($current_advance_amount, $grand_total);
                
                error_log("Advance used amount: $advance_used");
                
                if ($advance_used > 0) {
                    // Calculate remaining advance amount
                    $new_advance_amount = $current_advance_amount - $advance_used;
                    
                    // Update customer's advance_amount
                    $stmt = $conn->prepare("UPDATE customers SET advance_amount = ? WHERE id = ?");
                    $stmt->bind_param("di", $new_advance_amount, $customer_id);
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to update customer advance amount: " . $stmt->error);
                    }
                    
                    // Create advance payment record
                    $stmt = $conn->prepare("
                        INSERT INTO advance_payments 
                        (customer_id, customer_name, payment_type, reason, net_amount, advance_bill_number) 
                        VALUES (?, ?, 'cash', ?, ?, ?)
                    ");
                    
                    $reason = "Used for invoice #$invoice_number";
                    
                    $stmt->bind_param(
                        "issds", 
                        $customer_id, 
                        $customer_name,
                        $reason,
                        $new_advance_amount,
                        $invoice_number
                    );
                    
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to create advance payment record: " . $stmt->error);
                    }
                    
                    error_log("Advance payment record created successfully. New advance amount: $new_advance_amount");
                }
            } else {
                error_log("Customer not found when processing advance");
            }
        }
        
        // Determine change amount
        $change_amount = 0;
        if ($payment_method !== 'credit' && $paid_amount > $grand_total) {
            $change_amount = $paid_amount - $grand_total;
        }
        
        // Check if cheque_number column exists in pos_sales table
        $checkColumnQuery = "SHOW COLUMNS FROM pos_sales LIKE 'cheque_number'";
        $checkColumnResult = $conn->query($checkColumnQuery);
        $hasChequeNumberColumn = $checkColumnResult->num_rows > 0;
        
        // Prepare SQL statement based on whether cheque_number column exists
        if ($hasChequeNumberColumn) {
            $stmt = $conn->prepare("
                INSERT INTO pos_sales (
                    invoice_number, customer_id, customer_name, total_amount, 
                    discount_amount, net_amount, payment_method, cheque_number,
                    paid_amount, change_amount, credit_amount, advance_used,
                    rep_id, sale_date, status, return_bill_number, return_bill_amount
                ) VALUES (
                    ?, ?, ?, ?, 
                    ?, ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, NOW(), 'completed', ?, ?
                )
            ");
            
            $stmt->bind_param(
                "sisddssddddissd",
                $invoice_number,
                $customer_id,
                $customer_name,
                $subtotal,
                $total_discount,
                $grand_total,
                $payment_method,
                $cheque_number,
                $paid_amount,
                $change_amount,
                $credit_amount,
                $advance_used,
                $rep_id,
                $return_bill_number,
                $return_bill_amount
            );
        } else {
            // Old version without cheque_number column
            $stmt = $conn->prepare("
                INSERT INTO pos_sales (
                    invoice_number, customer_id, customer_name, total_amount, 
                    discount_amount, net_amount, payment_method,
                    paid_amount, change_amount, credit_amount, advance_used,
                    rep_id, sale_date, status, return_bill_number, return_bill_amount
                ) VALUES (
                    ?, ?, ?, ?, 
                    ?, ?, ?,
                    ?, ?, ?, ?,
                    ?, NOW(), 'completed', ?, ?
                )
            ");
            
            $stmt->bind_param(
                "sisddsddddissd",
                $invoice_number,
                $customer_id,
                $customer_name,
                $subtotal,
                $total_discount,
                $grand_total,
                $payment_method,
                $paid_amount,
                $change_amount,
                $credit_amount,
                $advance_used,
                $rep_id,
                $return_bill_number,
                $return_bill_amount
            );
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert sale: " . $stmt->error);
        }
        
        $sale_id = $conn->insert_id;
        
        // Insert sale items and update lorry stock
        foreach ($_SESSION['pos_cart'] as $item) {
            // Insert sale item
            $stmt = $conn->prepare("
                INSERT INTO pos_sale_items (
                    sale_id, lorry_stock_id, product_name, quantity, free_quantity, unit_price, 
                    discount_percent, discount_amount, subtotal
                ) VALUES (
                    ?, ?, ?, ?, ?, ?,
                    ?, ?, ?
                )
            ");
            
            $lorry_stock_id_to_insert = isset($item['lorry_stock_id']) && $item['lorry_stock_id'] ? (int)$item['lorry_stock_id'] : null;

            $stmt->bind_param(
                "iisiidddd", // Corrected type string
                $sale_id,
                $lorry_stock_id_to_insert,
                $item['product_name'],
                $item['quantity'],
                $item['free_quantity'],
                $item['unit_price'],
                $item['discount_percent'],
                $item['discount_amount'],
                $item['subtotal']
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert sale item: " . $stmt->error);
            }
            
            // Update lorry stock (reduce quantity)
            if (isset($item['lorry_stock_id']) && $item['lorry_stock_id']) {
                $stmt = $conn->prepare("
                    UPDATE lorry_stock 
                    SET quantity = quantity - ? 
                    WHERE id = ? AND rep_id = ?
                ");
                
                $stmt->bind_param("iii", $item['quantity'], $item['lorry_stock_id'], $rep_id);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update lorry stock: " . $stmt->error);
                }
            }
        }
        
        // Update customer credit balance if credit payment
        if ($payment_method === 'credit' && $customer_id && $credit_amount > 0) {
            $stmt = $conn->prepare("
                UPDATE customers 
                SET credit_balance = credit_balance + ? 
                WHERE id = ?
            ");
            
            $stmt->bind_param("di", $credit_amount, $customer_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update customer credit balance: " . $stmt->error);
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        // Set success response
        $response['success'] = true;
        $response['message'] = "Sale completed successfully.";
        $response['invoice_id'] = $sale_id;
        $response['invoice_number'] = $invoice_number;
        
        // Clear cart after successful sale
        $_SESSION['pos_cart'] = [];
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        $response['message'] = $e->getMessage();
        error_log("Error in complete_sale.php: " . $e->getMessage());
    }
} else {
    $response['message'] = "Invalid request method.";
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
