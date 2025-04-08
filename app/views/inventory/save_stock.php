<?php
require 'connection_db.php';

session_start();
$user_name = $_SESSION['username'];
$user_role = $_SESSION['job_role'];
$user_branch = $_SESSION['store'];

if (!$user_name || !$user_branch) {
    header("Location: ../unauthorized/unauthorized_access.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $supplier_id = $_POST['supplier-id'];
    $itemcode = trim($_POST['itemcode']);
    $product_name = trim($_POST['product-name']);
    $purchase_qty = (float)$_POST['purchase-qty'];
    $unit = $_POST['unit'];
    $cost_price = (float)$_POST['cost-price'];
    $wholesale_price = $_POST['wholesale-price'];
    $max_retail_price = $_POST['max-retail-price'];
    $super_customer_price = $_POST['super-customer-genuine-price'];
    $our_price = $_POST['our-price'];
    $low_stock = $_POST['low-stock'];
    $expire_date = $_POST['expire-date'];
    $discount_percent = (float)$_POST['discount-percent'];
    $current_branch_stock = $_POST['current_branch_stock'];

    $free = isset($_POST['free-item']) && $_POST['free-item'] === "true" ? 1 : 0;
    $gift = isset($_POST['gift']) && $_POST['gift'] === "true" ? 1 : 0;
    $voucher = isset($_POST['voucher']) && $_POST['voucher'] === "true" ? 1 : 0;
    // Keep this line in case it's used elsewhere, but don't use it in SQL
    $stock_category = isset($_POST['stock-category']) && $_POST['stock-category'] === "true" ? 'bulk' : 'packet';

    // Calculate total amount
    $unit_discount_amount = ($cost_price) * ($discount_percent / 100);
    $discount_amount = ($cost_price * $purchase_qty) * ($discount_percent / 100);
    $total_amount = ($cost_price * $purchase_qty) - $discount_amount;
    $unit_cost_price = $cost_price - ($cost_price * $discount_percent / 100);

    try {
        // Start transaction
        $conn->begin_transaction();

        // VALIDATION 1: Check if the itemcode exists in products table
        $check_exists_sql = "SELECT product_name FROM products WHERE item_code = ?";
        $check_exists_stmt = $conn->prepare($check_exists_sql);
        $check_exists_stmt->bind_param("s", $itemcode);
        $check_exists_stmt->execute();
        $exists_result = $check_exists_stmt->get_result();
        
        if ($exists_result->num_rows === 0) {
            throw new Exception("The product with item code '$itemcode' does not exist in products list");
        }
        
        // VALIDATION 2: Check if the product name matches what's in the database
        $db_product = $exists_result->fetch_assoc();
        $db_product_name = trim($db_product['product_name']);
        if ($db_product_name !== $product_name) {
            throw new Exception("Product name mismatch. The product name for item code '$itemcode' should be '{$db_product_name}'. Please check product name again.");
        }

        // Check if matching stock exists in the same branch
        $check_sql = "SELECT stock_id, purchase_qty, available_stock FROM stock_entries 
                      WHERE itemcode = ? 
                      AND cost_price = ? 
                      AND wholesale_price = ? 
                      AND max_retail_price = ? 
                      AND super_customer_price = ? 
                      AND our_price = ? 
                      AND branch = ?";
        
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("sddddds", 
            $itemcode, 
            $unit_cost_price, 
            $wholesale_price, 
            $max_retail_price, 
            $super_customer_price, 
            $our_price, 
            $user_branch
        );
        
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Stock exists, update quantity
            $stock_data = $result->fetch_assoc();
            $stock_id = $stock_data['stock_id'];
            $new_purchase_qty = $stock_data['purchase_qty'] + $purchase_qty;
            $new_available_stock = $stock_data['available_stock'] + $purchase_qty;
            
            $update_sql = "UPDATE stock_entries 
                          SET purchase_qty = ?, 
                              available_stock = ?,
                              expire_date = IF(? > expire_date OR expire_date IS NULL, ?, expire_date)
                          WHERE stock_id = ? AND branch = ?";
            
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ddssss", 
                $new_purchase_qty, 
                $new_available_stock,
                $expire_date,
                $expire_date,
                $stock_id, 
                $user_branch
            );
            
            if (!$update_stmt->execute()) {
                throw new Exception("Error updating stock: " . $update_stmt->error);
            }
        } else {
            // Get next stock_id
            $id_result = $conn->query("SELECT MAX(stock_id) AS max_id FROM stock_entries");
            $id_row = $id_result->fetch_assoc();
            $stock_id = $id_row['max_id'] ? $id_row['max_id'] + 1 : 10001;
            
            // Insert new stock entry - REMOVED category field
            $insert_sql = "INSERT INTO stock_entries (
                supplier_id, stock_id, itemcode, product_name, purchase_qty, unit, 
                available_stock, cost_price, wholesale_price, max_retail_price, 
                super_customer_price, our_price, low_stock, expire_date, discount_percent, 
                free, gift, voucher, barcode, total_cost_amount, branch
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("ssssdsddddddssdiiisds", 
                $supplier_id, $stock_id, $itemcode, $product_name, $purchase_qty, $unit, 
                $purchase_qty, $unit_cost_price, $wholesale_price, $max_retail_price, 
                $super_customer_price, $our_price, $low_stock, $expire_date, $discount_percent, 
                $free, $gift, $voucher, $itemcode, $total_amount, $user_branch
            );
            
            if (!$insert_stmt->execute()) {
                throw new Exception("Error inserting stock: " . $insert_stmt->error);
            }
        }
        
        // Always record the transaction in stock_creations - REMOVED category field
        $creation_sql = "INSERT INTO stock_creations (
            supplier_id, stock_id, itemcode, product_name, purchase_qty, unit, 
            available_stock, cost_price, wholesale_price, max_retail_price, 
            super_customer_price, our_price, low_stock, expire_date, discount_percent, 
            free, gift, voucher, barcode, total_cost_amount, branch
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $creation_stmt = $conn->prepare($creation_sql);
        $creation_stmt->bind_param("ssssdsddddddssdiiisds", 
            $supplier_id, $stock_id, $itemcode, $product_name, $purchase_qty, $unit, 
            $purchase_qty, $unit_cost_price, $wholesale_price, $max_retail_price, 
            $super_customer_price, $our_price, $low_stock, $expire_date, $discount_percent, 
            $free, $gift, $voucher, $itemcode, $total_amount, $user_branch
        );
        
        if (!$creation_stmt->execute()) {
            throw new Exception("Error recording stock creation: " . $creation_stmt->error);
        }

        // Update supplier's credit balance
        $update_credit_sql = "UPDATE suppliers SET credit_balance = credit_balance + ? WHERE supplier_id = ?";
        $update_credit_stmt = $conn->prepare($update_credit_sql);
        $update_credit_stmt->bind_param("ds", $total_amount, $supplier_id);
        
        if (!$update_credit_stmt->execute()) {
            throw new Exception("Error updating supplier credit balance: " . $update_credit_stmt->error);
        }

        // Commit transaction
        $conn->commit();

        date_default_timezone_set('Asia/Colombo');
        $sri_lanka_time = date('Y-m-d H:i:s');

        $response = [
            'success' => true,
            'message' => 'Stock saved successfully',
            'stockData' => [
                'created_at' => $sri_lanka_time,
                'stock_id' => $stock_id,
                'barcode' => $itemcode,
                'product_name' => $product_name,
                'cost_price' => $unit_cost_price,
                'wholesale_price' => $wholesale_price,
                'max_retail_price' => $max_retail_price,
                'super_customer_price' => $super_customer_price,
                'our_price' => $our_price,
                'purchase_qty' => $purchase_qty,
                'discount_percent' => $discount_percent,
                'discount_value' => $unit_discount_amount,
                'total_cost_amount' => $total_amount
            ]
        ];
        
        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode($response);
    } catch (Exception $e) {
        $conn->rollback();
        // Return error as JSON
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    } finally {
        // Close all prepared statements
        if (isset($check_exists_stmt)) $check_exists_stmt->close();
        if (isset($check_stmt)) $check_stmt->close();
        if (isset($update_stmt)) $update_stmt->close();
        if (isset($insert_stmt)) $insert_stmt->close();
        if (isset($creation_stmt)) $creation_stmt->close();
        if (isset($update_credit_stmt)) $update_credit_stmt->close();
        $conn->close();
    }
}
?>