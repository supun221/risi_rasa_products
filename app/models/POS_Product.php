<?php

class POS_PRODUCT
{
    private $connection;

    public function __construct($db_connection)
    {
        $this->connection = $db_connection;
    }

    public function retrieveProduct($stockId, $itemId)
    {
        $stmt = $this->connection->prepare("SELECT * FROM stock_entries WHERE stock_id = ? AND itemcode = ?");
        $stmt->bind_param('ss', $stockId, $itemId);
        $stmt->execute();

        if ($stmt->execute()) {
            $results = $stmt->get_result();
            if ($results->num_rows > 0) {
                return $results->fetch_assoc();
            } else {
                return "Product not exists";
            }
        } else {
            return "Error executing query";
        }
    }

    public function retrieveStaticProduct($barcode)
    {
        $stmt = $this->connection->prepare("SELECT * FROM stock_entries WHERE barcode = ?");
        $stmt->bind_param('s', $barcode);
        $stmt->execute();

        if ($stmt->execute()) {
            $results = $stmt->get_result();
            $products = [];
            while ($row = $results->fetch_assoc()) {
                $products[] = $row;
            }

            if (!empty($products)) {
                return $products;
            } else {
                return "Product not exists";
            }
        } else {
            return "Error executing query";
        }
    }

    public function retrievePromotions($barcode)
    {
        $stmt = $this->connection->prepare("SELECT * FROM promotions WHERE product_barcode = ?");
        $stmt->bind_param('s', $barcode);
        $stmt->execute();

        $results = $stmt->get_result();
        $promotions = [];
        while ($row = $results->fetch_assoc()) {
            $promotions[] = $row;
        }

        return $promotions;
    }

    public function retrieveStaticProduct2($barcode, $branch)
    {
        // First fetch the stock entries
        $stmt = $this->connection->prepare("
                SELECT * 
                FROM stock_entries 
                WHERE barcode = ? AND branch = ?
            ");
        $stmt->bind_param('ss', $barcode, $branch);
        
        if ($stmt->execute()) {
            $results = $stmt->get_result();
            $products = [];
            
            while ($row = $results->fetch_assoc()) {
                // For each stock entry, get the image path separately
                $itemcode = $row['itemcode'];
                $imgStmt = $this->connection->prepare("
                    SELECT image_path 
                    FROM products 
                    WHERE item_code = ?
                    LIMIT 1
                ");
                $imgStmt->bind_param('s', $itemcode);
                $imgStmt->execute();
                $imgResult = $imgStmt->get_result();
                
                if ($imgRow = $imgResult->fetch_assoc()) {
                    $row['image_path'] = $imgRow['image_path'];
                } else {
                    $row['image_path'] = null;
                }
                
                $products[] = $row;
                $imgStmt->close();
            }
            
            if (!empty($products)) {
                return $products;
            } else {
                return "Product not exists";
            }
        } else {
            return "Error executing query";
        }
    }
    public function getAllProducts($branch)
    {
        $stmt = $this->connection->prepare("SELECT * FROM stock_entries WHERE branch = ?");
        if (!$stmt) {
            return ["error" => "Failed to prepare statement: " . $this->connection->error];
        }
        $stmt->bind_param("s", $branch);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $products = $result->fetch_all(MYSQLI_ASSOC);
            return $products;
        } else {
            return ["error" => "Failed to execute query: " . $stmt->error];
        }
    }

    public function createPurchaseItemRecord($billId, $barcode, $product_name, $price, $qty, $disc_percentage, $subtotal, $date)
    {
        $stmt = $this->connection->prepare(
            "INSERT INTO purchase_items (bill_id, product_barcode, product_name, product_mrp, purchase_qty, discount_percentage, subtotal, purchased_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('sssdidds', $billId, $barcode, $product_name, $price, $qty, $disc_percentage, $subtotal, $date);
        $results = $stmt->execute();
        if ($results) {
            return "Purchase record created successfully!";
        } else {
            return "Purchase record creation failed! Error: " . $stmt->error;
        }
    }
    public function createPaymentTrackerRecord($billId, $paymentStatus, $insertedId)
    {
        // Prepare the SQL statement
        $stmt = $this->connection->prepare(
            "INSERT INTO payment_status (bill_id, payment_status, bill_record_id) 
             VALUES (?, ?, ?)"
        );

        if (!$stmt) {
            error_log("Failed to prepare statement: " . $this->connection->error);
            return false;
        }

        // Bind parameters
        $stmt->bind_param('ssi', $billId, $paymentStatus, $insertedId);

        // Execute the statement
        $result = $stmt->execute();

        if (!$result) {
            error_log("Payment tracker record creation failed: " . $stmt->error);
            return false;
        }

        return true;
    }

    public function createPurchaseItemRecord2($billId, $stockId, $barcode, $product_name, $price, $qty, $disc_percentage, $subtotal, $date, $branch)
    {
        $stmt = $this->connection->prepare(
            "INSERT INTO purchase_items (bill_id, stock_id, product_barcode, product_name, price, purchase_qty, discount_percentage, subtotal, purchased_date, branch) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('ssssdiddss', $billId, $stockId, $barcode, $product_name, $price, $qty, $disc_percentage, $subtotal, $date, $branch);
        $results = $stmt->execute();
        if ($results) {
            return "Purchase record created successfully!";
        } else {
            return "Purchase record creation failed! Error: " . $stmt->error;
        }
    }
    // public function createBillRecord($billId, $customerID, $grossAmount, $netAmount, $discountAmount, $numOfProducts, $paymentType, $balance, $billDate, $branch, $user, $customerNo, $orderWeight, $transportFee, $remark)
    // {
    //     // timezone to Sri Lanka
    //     date_default_timezone_set('Asia/Colombo');
    
    //     $billDate = date('Y-m-d H:i:s');
    
    //     $stmt = $this->connection->prepare(
    //         "INSERT INTO bill_records (bill_id, customer_name, gross_amount, net_amount, discount_amount, num_of_products, payment_type, balance, bill_date, branch, issuer, customer_id, weight, transport_fee, remark) 
    //             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    //     );
    
    //     $stmt->bind_param('ssdddissssssdds', $billId, $customerID, $grossAmount, $netAmount, $discountAmount, $numOfProducts, $paymentType, $balance, $billDate, $branch, $user, $customerNo, $orderWeight, $transportFee, $remark);
    
    //     $result = $stmt->execute();
    //     $insertedId = $this->connection->insert_id;
    
    //     if ($result) {
    //         // Create a record in the payment_tracker table
    //         $this->createPaymentTrackerRecord($billId, $paymentStatus, $insertedId);
    //         return "Bill record created successfully!";
    //     } else {
    //         return "Bill record creation failed! Error: " . $stmt->error;
    //     }
    // }
    public function createBillRecord($billId, $customerID, $grossAmount, $netAmount, $discountAmount, $numOfProducts, $paymentType, $balance, $billDate, $branch, $user, $customerNo, $orderWeight, $transportFee, $remark, $paymentStatus = null)
{
    // Set timezone to Sri Lanka
    date_default_timezone_set('Asia/Colombo');

    // Use the provided bill date or current date and time if not provided
    $billDate = $billDate ?: date('Y-m-d H:i:s');

    $stmt = $this->connection->prepare(
        "INSERT INTO bill_records (bill_id, customer_name, gross_amount, net_amount, discount_amount, num_of_products, payment_type, balance, bill_date, branch, issuer, customer_id, weight, transport_fee, remark) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );

    if (!$stmt) {
        return "Failed to prepare statement: " . $this->connection->error;
    }

    $stmt->bind_param('ssdddissssssdds', $billId, $customerID, $grossAmount, $netAmount, $discountAmount, $numOfProducts, $paymentType, $balance, $billDate, $branch, $user, $customerNo, $orderWeight, $transportFee, $remark);

    $result = $stmt->execute();
    $insertedId = $this->connection->insert_id;

    if ($result) {
        // Create a record in the payment_tracker table
        if ($this->createPaymentTrackerRecord($billId, $paymentStatus, $insertedId)) {
            return "Bill record and payment tracker record created successfully!";
        } else {
            return "Bill record created, but payment tracker record creation failed!";
        }
    } else {
        return "Bill record creation failed! Error: " . $stmt->error;
    }
}
    
    // Function to create a record in the payment_tracker table
    public function createPaymentStatusRecord($billId, $paymentStatus, $billRecordId)
    {
        $stmt = $this->connection->prepare(
            "INSERT INTO payment_status (bill_id, payment_status, bill_record_id, created_at) 
                VALUES (?, ?, ?, NOW())"
        );
    
        $stmt->bind_param('ssi', $billId, $paymentStatus, $billRecordId);
        $result = $stmt->execute();
    
        return $result;
    }

    public function updateStockEntries($productId, $remainingQty, $branch)
    {
        $stmt = $this->connection->prepare(
            "UPDATE stock_entries SET available_stock = ? WHERE id = ? AND branch = ?"
        );
        $stmt->bind_param('iis', $remainingQty, $productId, $branch);

        $results = $stmt->execute();

        if ($results) {
            return "Stock entries updated successfully!";
        } else {
            return "Stock entry update failed! Error: " . $stmt->error;
        }
    }

    public function billIdExists($billId)
    {
        $stmt = $this->connection->prepare("SELECT bill_id FROM bill_records WHERE bill_id = ?");
        $stmt->bind_param('s', $billId);
        $stmt->execute();
        $stmt->store_result(); // Store the result to check the number of rows
        return $stmt->num_rows > 0;
    }

    public function deleteHoldBillAndItems($billId)
    {
        $this->connection->begin_transaction();

        try {
            $stmt1 = $this->connection->prepare("DELETE FROM hold_purchase_items WHERE bill_id = ?");
            $stmt1->bind_param('s', $billId);
            $stmt1->execute();
            $stmt1->close();

            $stmt2 = $this->connection->prepare("DELETE FROM hold_bill_records WHERE bill_id = ?");
            $stmt2->bind_param('s', $billId);
            $stmt2->execute();
            $stmt2->close();

            $this->connection->commit();
        } catch (Exception $e) {
            $this->connection->rollback();
            error_log("Failed to delete hold records for bill ID $billId: " . $e->getMessage());
        }
    }


    public function createHoldPurchaseItemRecord($billId, $barcode, $product_name, $unit_price, $our_price, $qty, $disc_percentage, $subtotal, $stockId, $free, $branch)
    {
        $stmt = $this->connection->prepare(
            "INSERT INTO hold_purchase_items (bill_id, product_barcode, product_name, product_mrp, our_price, purchase_qty, discount_percentage, subtotal, stock_id, free, branch) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('sssddiddiis', $billId, $barcode, $product_name, $unit_price, $our_price, $qty, $disc_percentage, $subtotal, $stockId, $free, $branch);
        $results = $stmt->execute();
        if ($results) {
            return "Purchase saved successfully!";
        } else {
            return "Purchase record creation failed! Error: " . $stmt->error;
        }
    }

    public function checkIfItemExists($billId, $stockId)
    {
        $stmt = $this->connection->prepare("SELECT COUNT(*) FROM hold_purchase_items WHERE bill_id = ? AND stock_id = ?");
        $stmt->bind_param('si', $billId, $stockId);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        return $count > 0; // Return true if the item exists
    }

    public function updateHoldPurchaseItem($billId, $qty, $disc_percentage, $subtotal, $free, $stockId)
    {
        $stmt = $this->connection->prepare(
            "UPDATE hold_purchase_items 
                SET purchase_qty = ?, discount_percentage = ?, subtotal = ?, free = ? 
                WHERE bill_id = ? AND stock_id = ?"
        );
        $stmt->bind_param('iddisi', $qty, $disc_percentage, $subtotal, $free, $billId, $stockId);
        $result = $stmt->execute();
        return $result; // Return the result of the update operation
    }


    public function createHoldBillRecord($billId, $billDate, $branch, $user)
    {
        $stmt = $this->connection->prepare(
            "INSERT INTO hold_bill_records (bill_id, bill_date, branch, held_by) 
                VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param('ssss', $billId, $billDate, $branch, $user);

        $results = $stmt->execute();

        if ($results) {
            return "Bill record saved successfully!";
        } else {
            return "Bill record creation failed! Error: " . $stmt->error;
        }
    }

    public function deleteHoldBillRecord($billId)
    {
        // Check if records exist
        $checkStmt = $this->connection->prepare("SELECT COUNT(*) FROM hold_bill_records WHERE bill_id = ?");
        $checkStmt->bind_param('s', $billId);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($count > 0) {
            // Proceed with deletion if records exist
            $deleteStmt = $this->connection->prepare("DELETE FROM hold_bill_records WHERE bill_id = ?");
            $deleteStmt->bind_param('s', $billId);
            $result = $deleteStmt->execute();
            $deleteStmt->close();
            return $result;  // Return true if delete was successful
        }
        return false;  // Return false if no records found
    }


    public function deleteHoldPurchaseItems($billId)
    {
        // Check if records exist
        $checkStmt = $this->connection->prepare("SELECT COUNT(*) FROM hold_purchase_items WHERE bill_id = ?");
        $checkStmt->bind_param('s', $billId);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($count > 0) {
            // Proceed with deletion if records exist
            $deleteStmt = $this->connection->prepare("DELETE FROM hold_purchase_items WHERE bill_id = ?");
            $deleteStmt->bind_param('s', $billId);
            $result = $deleteStmt->execute();
            $deleteStmt->close();
            return $result;  // Return true if delete was successful
        }
        return false;  // Return false if no records found
    }

    public function checkIfBillRecordExists($billId)
    {
        $stmt = $this->connection->prepare("SELECT COUNT(*) FROM hold_bill_records WHERE bill_id = ?");
        $stmt->bind_param('s', $billId);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        return $count > 0;
    }
    // public function checkIfBillItemsExists($billId) {
    //     $stmt = $this->connection->prepare("SELECT COUNT(*) FROM hold_purchase_items WHERE bill_id = ?");
    //     $stmt->bind_param('s', $billId);
    //     $stmt->execute();
    //     $stmt->bind_result($count);
    //     $stmt->fetch();
    //     $stmt->close();

    //     return $count > 0;
    // }

    public function retrieveHoldStaticProduct($barcode)
    {
        $stmt = $this->connection->prepare("SELECT * FROM stock_entries WHERE barcode = ?");
        $stmt->bind_param('s', $barcode);
        $stmt->execute();

        if ($stmt->execute()) {
            $results = $stmt->get_result();
            if ($results->num_rows > 0) {
                return $results->fetch_assoc();
            } else {
                return "Product not exists";
            }
        } else {
            return "Error executing query";
        }
    }

    public function getUnderstockedItems()
    {
        $stmt = $this->connection->prepare("SELECT * FROM stock_entries WHERE available_stock < 0");
        if (!$stmt) {
            return ["error" => "Failed to prepare statement: " . $this->connection->error];
        }
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $products = $result->fetch_all(MYSQLI_ASSOC);
            return $products;
        } else {
            return ["error" => "Failed to execute query: " . $stmt->error];
        }
    }

    public function getSuggestedBarcodes($branch, $searchTerm) {
        $stmt = $this->connection->prepare("SELECT barcode FROM stock_entries WHERE branch = ? AND barcode LIKE CONCAT('%', ?, '') LIMIT 10");
        
        if (!$stmt) {
            return ["error" => "Failed to prepare statement: " . $this->connection->error];
        }
        
        $stmt->bind_param("ss", $branch, $searchTerm);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $barcodes = $result->fetch_all(MYSQLI_ASSOC);
            return $barcodes;
        } else {
            return ["error" => "Failed to execute query: " . $stmt->error];
        }
    }
}
