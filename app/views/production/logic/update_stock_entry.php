<?php
require_once '../../../models/Database.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $barcode = $_POST['barcode'];
    $price = $_POST['our_price'];
    $new_stock = $_POST['new_stock'];

    if (!empty($barcode) && !empty($price) && isset($new_stock)) {
        // Get current product information before update
        $info_stmt = $db_conn->prepare("SELECT * FROM stock_entries WHERE itemcode = ? AND our_price = ?");
        $info_stmt->bind_param("sd", $barcode, $price);
        $info_stmt->execute();
        $result = $info_stmt->get_result();
        
        if ($result->num_rows > 0) {
            $stock_info = $result->fetch_assoc();
            $old_stock = $stock_info['available_stock'];
            
            // Update stock_entries
            $stmt = $db_conn->prepare("UPDATE stock_entries SET available_stock = ? WHERE itemcode = ? AND our_price = ?");
            $stmt->bind_param("isd", $new_stock, $barcode, $price);
            $update_success = $stmt->execute();
            $stmt->close();
            
            // Get latest stock_id for stock_creations
            $latest_stock_id = 1;
            $id_query = "SELECT stock_id FROM stock_creations ORDER BY stock_id DESC LIMIT 1";
            $id_result = $db_conn->query($id_query);
            if ($id_result && $id_row = $id_result->fetch_assoc()) {
                $latest_stock_id = (int) $id_row['stock_id'] + 1;
            }
            
            // Insert into stock_creations
            $stock_change = $new_stock - $old_stock; // Record only the change
            $creation_stmt = $db_conn->prepare("INSERT INTO stock_creations 
                (supplier_id, stock_id, itemcode, product_name, available_stock, our_price, 
                cost_price, wholesale_price, max_retail_price, super_customer_price, branch, 
                created_at, expire_date, purchase_qty, unit, barcode) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $supplier_id = $stock_info['supplier_id'];
            $product_name = $stock_info['product_name'];
            $our_price = $stock_info['our_price'];
            $cost_price = $stock_info['cost_price'];
            $wholesale_price = $stock_info['wholesale_price'];
            $mr_price = $stock_info['max_retail_price'];
            $sc_price = $stock_info['super_customer_price'];
            $branch = $stock_info['branch'];
            $created_at = date("Y-m-d");
            $expire_date = $stock_info['expire_date'];
            $purchase_qty = $stock_change; // Use stock change as purchase quantity
            $unit = $stock_info['unit'];

            $creation_stmt->bind_param("sissddddddsissis", 
                $supplier_id, $latest_stock_id, $barcode, $product_name, $stock_change, 
                $our_price, $cost_price, $wholesale_price, $mr_price, 
                $sc_price, $branch, $created_at, $expire_date, $purchase_qty, $unit, $barcode);
            
            $creation_success = $creation_stmt->execute();
            $creation_stmt->close();
            
            if ($update_success && $creation_success) {
                echo json_encode(["status" => "success", "message" => "Stock updated successfully"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Stock update failed"]);
            }
            
            $info_stmt->close();
        } else {
            echo json_encode(["status" => "error", "message" => "Product not found with the given barcode and price"]);
        }

        $db_conn->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Missing required parameters"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
?>
