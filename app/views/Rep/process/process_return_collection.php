<?php
/**
 * Process Return Collection Submission
 * This file handles the processing of return collections from POS sales
 */

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'return_bill_number' => '',
    'redirect_url' => ''
];

// Check if form data was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submit_return'])) {
    $response['message'] = 'Invalid request method or missing data';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

try {
    require_once '../../../../config/databade.php';
    
    // Start session to get rep_id
    session_start();
    $rep_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
    
    if ($rep_id <= 0) {
        throw new Exception('Invalid user session. Please log in again.');
    }
    
    // Get form data
    $original_invoice = $conn->real_escape_string($_POST['original_invoice']);
    $sale_id = (int)$_POST['sale_id'];
    $customer_id = !empty($_POST['customer_id']) ? (int)$_POST['customer_id'] : null;
    $customer_name = $conn->real_escape_string($_POST['customer_name']);
    $reason = $conn->real_escape_string($_POST['return_reason']);
    $notes = $conn->real_escape_string($_POST['return_notes']);
    
    // Validate required fields
    if (empty($original_invoice) || $sale_id <= 0) {
        throw new Exception('Missing required information: invoice number or sale ID');
    }
    
    if (empty($reason)) {
        throw new Exception('Please select a reason for the return');
    }
    
    // Check if there are items to return
    if (!isset($_POST['item_id']) || !is_array($_POST['item_id']) || empty($_POST['item_id'])) {
        throw new Exception('No items selected for return');
    }
    
    $total_amount = 0;
    $has_items_to_return = false;
    
    // Check if at least one item has quantity > 0
    foreach ($_POST['return_qty'] as $qty) {
        if ((int)$qty > 0) {
            $has_items_to_return = true;
            break;
        }
    }
    
    if (!$has_items_to_return) {
        throw new Exception('Please enter return quantity for at least one item');
    }
    
    // Generate return bill number
    $date_prefix = date('Ymd');
    $random_suffix = mt_rand(1000, 9999);
    $return_bill_number = "RTN-{$date_prefix}-{$random_suffix}";
    
    // Start transaction
    $conn->begin_transaction();
    
    // Insert return header
    $header_query = "INSERT INTO return_collections 
                    (return_bill_number, original_invoice_number, sale_id, customer_id, 
                     customer_name, reason, notes, rep_id, total_amount) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)";
                    
    $stmt = $conn->prepare($header_query);
    if (!$stmt) {
        throw new Exception('Failed to prepare return header query: ' . $conn->error);
    }
    
    $stmt->bind_param("sssisssi", $return_bill_number, $original_invoice, $sale_id, 
                     $customer_id, $customer_name, $reason, $notes, $rep_id);
    $stmt->execute();
    
    if ($stmt->affected_rows <= 0) {
        throw new Exception('Failed to insert return header');
    }
    
    $return_id = $conn->insert_id;
    
    // Process return items
    foreach ($_POST['item_id'] as $key => $sale_item_id) {
        $return_qty = (int)$_POST['return_qty'][$key];
        $original_qty = (int)$_POST['original_qty'][$key];
        
        if ($return_qty > 0) {
            $product_name = $conn->real_escape_string($_POST['product_name'][$key]);
            $unit_price = (float)$_POST['unit_price'][$key];
            $return_amount = $unit_price * $return_qty;
            
            // Insert return item
            $item_query = "INSERT INTO return_collection_items 
                          (return_id, sale_item_id, product_name, unit_price, 
                           return_qty, return_amount, reason) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)";
                           
            $stmt = $conn->prepare($item_query);
            if (!$stmt) {
                throw new Exception('Failed to prepare return item query: ' . $conn->error);
            }
            
            $stmt->bind_param("iisdids", $return_id, $sale_item_id, $product_name, 
                            $unit_price, $return_qty, $return_amount, $reason);
            $stmt->execute();
            
            if ($stmt->affected_rows <= 0) {
                throw new Exception('Failed to insert return item: ' . $product_name);
            }
            
            // Add to total
            $total_amount += $return_amount;
            
            // Update pos_sale_items table - reduce quantity and subtotal
            $new_qty = $original_qty - $return_qty;
            $new_subtotal = $unit_price * $new_qty;
            
            $update_item_query = "UPDATE pos_sale_items 
                                 SET quantity = ?, subtotal = ? 
                                 WHERE id = ?";
            $stmt = $conn->prepare($update_item_query);
            if (!$stmt) {
                throw new Exception('Failed to prepare update item query: ' . $conn->error);
            }
            
            $stmt->bind_param("idi", $new_qty, $new_subtotal, $sale_item_id);
            $stmt->execute();
            
            // Add products back to lorry stock
            $check_stock_query = "SELECT id FROM lorry_stock 
                                 WHERE rep_id = ? AND product_name = ? AND status = 'active' 
                                 LIMIT 1";
            $stmt = $conn->prepare($check_stock_query);
            $stmt->bind_param("is", $rep_id, $product_name);
            $stmt->execute();
            $stock_result = $stmt->get_result();
            
            if ($stock_result->num_rows > 0) {
                // Update existing lorry stock
                $stock_row = $stock_result->fetch_assoc();
                $lorry_stock_id = $stock_row['id'];
                
                $update_stock_query = "UPDATE lorry_stock 
                                      SET quantity = quantity + ?, 
                                          total_amount = total_amount + ? 
                                      WHERE id = ?";
                $stmt = $conn->prepare($update_stock_query);
                if (!$stmt) {
                    throw new Exception('Failed to prepare update stock query: ' . $conn->error);
                }
                
                $stmt->bind_param("idi", $return_qty, $return_amount, $lorry_stock_id);
                $stmt->execute();
            } else {
                // Insert new lorry stock record
                $insert_stock_query = "INSERT INTO lorry_stock 
                                      (rep_id, product_name, quantity, unit_price, total_amount, status) 
                                      VALUES (?, ?, ?, ?, ?, 'active')";
                $stmt = $conn->prepare($insert_stock_query);
                if (!$stmt) {
                    throw new Exception('Failed to prepare insert stock query: ' . $conn->error);
                }
                
                $stmt->bind_param("isids", $rep_id, $product_name, $return_qty, $unit_price, $return_amount);
                $stmt->execute();
            }
        }
    }
    
    // Update pos_sales table - reduce total amount
    $update_sale_query = "UPDATE pos_sales 
                          SET total_amount = total_amount - ?, 
                              net_amount = net_amount - ? 
                          WHERE id = ?";
    $stmt = $conn->prepare($update_sale_query);
    if (!$stmt) {
        throw new Exception('Failed to prepare update sale query: ' . $conn->error);
    }
    
    $stmt->bind_param("ddi", $total_amount, $total_amount, $sale_id);
    $stmt->execute();
    
    // Update return header total
    $update_query = "UPDATE return_collections SET total_amount = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    if (!$stmt) {
        throw new Exception('Failed to prepare update total query: ' . $conn->error);
    }
    
    $stmt->bind_param("di", $total_amount, $return_id);
    $stmt->execute();
    
    // If we got here, everything worked, so commit the transaction
    $conn->commit();
    
    $response['success'] = true;
    $response['message'] = "Return processed successfully. Return Bill Number: " . $return_bill_number;
    $response['return_bill_number'] = $return_bill_number;
    // Update redirect URL to point to the return receipt page
    $response['redirect_url'] = "../invoice/pos_return_receipt.php?return_bill=" . $return_bill_number;
    
} catch (Exception $e) {
    // Roll back transaction on error
    if (isset($conn) && $conn->connect_errno === 0) {
        $conn->rollback();
    }
    $response['message'] = 'Error processing return: ' . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>