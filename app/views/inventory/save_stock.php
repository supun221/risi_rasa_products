<?php
require 'connection_db.php';

session_start();
$user_name = $_SESSION['username'] ?? null;
$user_role = $_SESSION['job_role'] ?? null;
$user_branch = $_SESSION['store'] ?? null;

if (!$user_name || !$user_branch) {
    header("Location: ../unauthorized/unauthorized_access.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $supplier_id = $_POST['supplier-id'];
    $itemcode = $_POST['itemcode'];
    $product_name = $_POST['product-name'];
    $purchase_qty = (float)$_POST['purchase-qty'];
    $available_stock = $_POST['purchase-qty'];
    $unit = $_POST['unit'];
    $cost_price = (float)$_POST['cost-price'];
    $wholesale_price = $_POST['wholesale-price'];
    $max_retail_price = $_POST['max-retail-price'];
    $super_customer_price = $_POST['super-customer-genuine-price'];
    $our_price = $_POST['our-price'];
    $low_stock = $_POST['low-stock'];
    $expire_date = $_POST['expire-date'];
    $discount_percent = (float)$_POST['discount-percent'];
    //$current_branch_stock = $_POST['current_branch_stock'];

    $free = isset($_POST['free-item']) && $_POST['free-item'] === "true" ? 1 : 0;
    $gift = isset($_POST['gift']) && $_POST['gift'] === "true" ? 1 : 0;
    $voucher = isset($_POST['voucher']) && $_POST['voucher'] === "true" ? 1 : 0;

    // Calculate total amount
    $discount_amount = ($cost_price * $purchase_qty) * ($discount_percent / 100);
    $total_amount = ($cost_price * $purchase_qty) - $discount_amount;
    $unit_cost_price = $cost_price - ($cost_price * $discount_percent / 100);

    try {
        // Start transaction
        $conn->begin_transaction();

        // Get next stock_id
        $result = $conn->query("SELECT MAX(stock_id) AS max_id FROM stock_entries");
        $row = $result->fetch_assoc();
        $next_stock_id = $row['max_id'] ? $row['max_id'] + 1 : 10001;

        // Insert stock entry
        $sql = "INSERT INTO stock_entries (supplier_id, stock_id, itemcode, product_name, purchase_qty, unit, available_stock, cost_price, wholesale_price, max_retail_price, super_customer_price, our_price, low_stock, expire_date, discount_percent, free, gift, voucher, barcode, total_cost_amount, branch) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssdsddddddssdiiisds", 
            $supplier_id, $next_stock_id, $itemcode, $product_name, $purchase_qty, $unit, 
            $available_stock, $unit_cost_price, $wholesale_price, $max_retail_price, 
            $super_customer_price, $our_price, $low_stock, $expire_date, $discount_percent, 
            $free, $gift, $voucher, $itemcode, $total_amount, $user_branch
        );

        if (!$stmt->execute()) {
            throw new Exception("Error inserting stock: " . $stmt->error);
        }

        // Update supplier's credit balance
        $updateCreditSql = "UPDATE suppliers SET credit_balance = credit_balance + ? WHERE supplier_id = ?";
        $updateStmt = $conn->prepare($updateCreditSql);
        $updateStmt->bind_param("di", $total_amount, $supplier_id);

        if (!$updateStmt->execute()) {
            throw new Exception("Error updating supplier credit balance: " . $updateStmt->error);
        }

        // Commit transaction
        $conn->commit();
        echo "Stock saved successfully";
    } catch (Exception $e) {
        $conn->rollback();
        echo "Transaction failed: " . $e->getMessage();
    }

    $stmt->close();
    $updateStmt->close();
    $conn->close();
}
?>
