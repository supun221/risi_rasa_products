<?php
require_once '../../config/databade.php';

session_start();
$user_name = $_SESSION['username'];
$user_role = $_SESSION['job_role'];
$user_branch = $_SESSION['store'];

if (!$user_name && !$user_branch) {
    header("Location: ../unauthorized/unauthorized_access.php");
    exit();
}

// Handle fetching products for dropdown (NEW CODE)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'fetchProducts') {
    $query = "SELECT product_name, item_code AS barcode FROM products";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(['error' => 'Failed to prepare statement']);
        exit;
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($products);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getEntries') {
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $offset = ($page - 1) * $limit;
    $search = isset($_GET['search']) ? "%" . $_GET['search'] . "%" : "%%";

    // Validate inputs
    if ($page < 1 || $limit < 1) {
        echo json_encode(['error' => 'Invalid pagination parameters']);
        exit;
    }

    // Fetch entries with pagination and search
    $query = "SELECT 
                se.id,
                se.stock_id,
                se.supplier_id,
                s.supplier_name AS supplier_name,
                se.product_name,
                se.available_stock,
                se.cost_price,
                se.wholesale_price,
                se.max_retail_price,
                se.super_customer_price,
                se.our_price,
                se.low_stock,
                se.expire_date,
                se.discount_percent,
                se.barcode,
                se.free,
                se.gift,
                se.voucher,
                se.unit
              FROM stock_entries se
              LEFT JOIN suppliers s ON se.supplier_id = s.supplier_id
              WHERE (se.product_name LIKE ? OR se.barcode LIKE ?) AND se.branch = ?
              ORDER BY se.id DESC
              LIMIT ?, ?";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(['error' => 'Failed to prepare statement']);
        exit;
    }

    $stmt->bind_param("sssii", $search, $search, $user_branch, $offset, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $entries = $result->fetch_all(MYSQLI_ASSOC);

    // Get total entries for pagination
    $countQuery = "SELECT COUNT(*) AS total 
                   FROM stock_entries se
                   LEFT JOIN suppliers s ON se.supplier_id = s.supplier_id
                   WHERE (se.product_name LIKE ? OR se.barcode LIKE ?) AND se.branch = ?";

    $countStmt = $conn->prepare($countQuery);
    if (!$countStmt) {
        echo json_encode(['error' => 'Failed to prepare count statement']);
        exit;
    }

    $countStmt->bind_param("sss", $search, $search, $user_branch);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalEntries = $countResult->fetch_assoc()['total'];
    $totalPages = ceil($totalEntries / $limit);

    echo json_encode([
        'entries' => $entries,
        'totalPages' => $totalPages,
        'currentPage' => $page
    ]);
    exit;
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Handle updateEntry
    if ($_POST['action'] === 'updateEntry') {
        // Extract and validate fields
        $id = intval($_POST['id']);
        $product_name = $_POST['product_name'];
        $available_stock = intval($_POST['available_stock']);
        $wholesale_price = floatval($_POST['wholesale_price']);
        $max_retail_price = floatval($_POST['max_retail_price']);
        $super_customer_price = floatval($_POST['super_customer_price']);
        $our_price = floatval($_POST['our_price']);
        $low_stock = intval($_POST['low_stock']);
        $expire_date = !empty($_POST['expire_date']) ? $_POST['expire_date'] : NULL; // Null if empty
        $discount_percent = floatval($_POST['discount_percent']);
        $barcode = $_POST['barcode'];
        $free = isset($_POST['free']) ? intval($_POST['free']) : 0; // Default to 0
        $gift = isset($_POST['gift']) ? intval($_POST['gift']) : 0; // Default to 0
        $voucher = isset($_POST['voucher']) ? intval($_POST['voucher']) : 0; // Default to 0

        // SQL query for updating stock entry
        $query = "UPDATE stock_entries 
                  SET product_name = ?, available_stock = ?, wholesale_price = ?, 
                      max_retail_price = ?, super_customer_price = ?, our_price = ?, low_stock = ?, 
                      expire_date = ?, discount_percent = ?, barcode = ?, free = ?, gift = ?, voucher = ? 
                  WHERE id = ?";

        $stmt = $conn->prepare($query);

        if ($stmt === false) {
            echo json_encode(['error' => 'Failed to prepare statement']);
            exit;
        }

        // Bind parameters
        $stmt->bind_param(
            "siddddddssiiii",
            $product_name,
            $available_stock,
            $wholesale_price,
            $max_retail_price,
            $super_customer_price,
            $our_price,
            $low_stock,
            $expire_date,
            $discount_percent,
            $barcode,
            $free,
            $gift,
            $voucher,
            $id
        );

        // Execute query
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database update failed']);
        }
        exit;
    }

    // Handle addDeal
    if ($_POST['action'] === 'addDeal') {
        // Extract and validate deal information
        $product_name = $_POST['product_name']; // Get product name
        $deal_price = floatval($_POST['deal_price']);
        $start_date = $_POST['start_date'];  // Date format (YYYY-MM-DD)
        $end_date = $_POST['end_date'];      // Date format (YYYY-MM-DD)

        // Validate that all required fields are present
        if (empty($product_name) || empty($deal_price) || empty($start_date) || empty($end_date)) {
            echo json_encode(['success' => false, 'error' => 'Missing required fields']);
            exit;
        }

        // SQL query for adding the deal using product name
        $query = "UPDATE stock_entries
                  SET deal_price = ?, start_date = ?, end_date = ?
                  WHERE product_name = ?"; // Use product_name to identify the row

        $stmt = $conn->prepare($query);

        if ($stmt === false) {
            echo json_encode(['error' => 'Failed to prepare statement']);
            exit;
        }

        // Bind parameters and execute query
        $stmt->bind_param("dsss", $deal_price, $start_date, $end_date, $product_name);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to add deal']);
        }
        exit;
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getStockCreations') {
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $offset = ($page - 1) * $limit;
    $search = isset($_GET['search']) ? "%" . $_GET['search'] . "%" : "%%";

    // Validate inputs
    if ($page < 1 || $limit < 1) {
        echo json_encode(['error' => 'Invalid pagination parameters']);
        exit;
    }

    // Fetch entries with pagination and search
    $query = "SELECT 
                se.id,
                se.stock_id,
                se.supplier_id,
                s.supplier_name AS supplier_name,
                se.product_name,
                se.purchase_qty,
                se.cost_price,
                se.wholesale_price,
                se.max_retail_price,
                se.super_customer_price,
                se.our_price,
                se.low_stock,
                se.expire_date,
                se.discount_percent,
                se.barcode,
                se.free,
                se.gift,
                se.voucher,
                se.created_at,
                se.unit
              FROM stock_creations se
              LEFT JOIN suppliers s ON se.supplier_id = s.supplier_id
              WHERE (se.product_name LIKE ? OR se.barcode LIKE ?) AND se.branch = ?
              ORDER BY se.id DESC
              LIMIT ?, ?";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(['error' => 'Failed to prepare statement']);
        exit;
    }

    $stmt->bind_param("sssii", $search, $search, $user_branch, $offset, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $entries = $result->fetch_all(MYSQLI_ASSOC);

    // Get total entries for pagination
    $countQuery = "SELECT COUNT(*) AS total 
                   FROM stock_creations se
                   LEFT JOIN suppliers s ON se.supplier_id = s.supplier_id
                   WHERE (se.product_name LIKE ? OR se.barcode LIKE ?) AND se.branch = ?";

    $countStmt = $conn->prepare($countQuery);
    if (!$countStmt) {
        echo json_encode(['error' => 'Failed to prepare count statement']);
        exit;
    }

    $countStmt->bind_param("sss", $search, $search, $user_branch);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $totalEntries = $countResult->fetch_assoc()['total'];
    $totalPages = ceil($totalEntries / $limit);

    echo json_encode([
        'entries' => $entries,
        'totalPages' => $totalPages,
        'currentPage' => $page
    ]);
    exit;
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Handle updateEntry
    // if ($_POST['action'] === 'updateStockCreation') {
    //     // Extract and validate fields
    //     $id = intval($_POST['id']);
    //     $supplier_id = intval($_POST['supplier_id']);
    //     $stock_id = intval($_POST['stock_id']);
    //     $product_name = $_POST['product_name'];
    //     $purchase_qty = floatval($_POST['purchase_qty']);
    //     $previous_purchase_qty = floatval($_POST['previous_purchase_qty']);
    //     $unit = $_POST['unit'];
    //     $cost_price = floatval($_POST['cost_price']);
    //     $previous_cost_price = floatval($_POST['previous_cost_price']);
    //     $wholesale_price = floatval($_POST['wholesale_price']);
    //     $max_retail_price = floatval($_POST['max_retail_price']);
    //     $super_customer_price = floatval($_POST['super_customer_price']);
    //     $our_price = floatval($_POST['our_price']);
    //     $low_stock = floatval($_POST['low_stock']);
    //     $expire_date = !empty($_POST['expire_date']) ? $_POST['expire_date'] : NULL; // Null if empty
    //     $discount_percent = floatval($_POST['discount_percent']);
    //     $barcode = $_POST['barcode'];
    //     $category = $_POST['category'];
    //     // $free = isset($_POST['free']) ? intval($_POST['free']) : 0; // Default to 0
    //     // $gift = isset($_POST['gift']) ? intval($_POST['gift']) : 0; // Default to 0
    //     // $voucher = isset($_POST['voucher']) ? intval($_POST['voucher']) : 0; // Default to 0


    //     // Calculate previous total cost and new total cost
    //     $previous_total_cost = $previous_cost_price * $previous_purchase_qty;
    //     $new_total_cost = $cost_price * $purchase_qty;

    //     // First, update the supplier's credit_balance
    //     // 1. Deduct the previous total cost
    //     $stmt = $conn->prepare("UPDATE suppliers SET credit_balance = credit_balance - ? WHERE supplier_id = ?");
    //     $stmt->bind_param("di", $previous_total_cost, $supplier_id);
    //     $stmt->execute();
    //     $stmt->close();

    //     // 2. Add the new total cost
    //     $stmt = $conn->prepare("UPDATE suppliers SET credit_balance = credit_balance + ? WHERE supplier_id = ?");
    //     $stmt->bind_param("di", $new_total_cost, $supplier_id);
    //     $stmt->execute();
    //     $stmt->close();

    //     // First, get the current stock entry for the given stock_id
    //     $stmt = $conn->prepare("SELECT cost_price, wholesale_price, max_retail_price, super_customer_price, our_price, purchase_qty, available_stock 
    //     FROM stock_entries 
    //     WHERE stock_id = ?");
    //     $stmt->bind_param("i", $stock_id);
    //     $stmt->execute();
    //     $result = $stmt->get_result();
    //     $stockEntry = $result->fetch_assoc();
    //     $stmt->close();

    //     // Check if stock entry exists
    //     if ($stockEntry) {
    //         // Check if the price values match with the stock entry
    //         $pricesMatch = (
    //             $cost_price == $stockEntry['cost_price'] &&
    //             $wholesale_price == $stockEntry['wholesale_price'] &&
    //             $max_retail_price == $stockEntry['max_retail_price'] &&
    //             $super_customer_price == $stockEntry['super_customer_price'] &&
    //             $our_price == $stockEntry['our_price']
    //         );

    //         if ($pricesMatch) {
    //             // First deduct previous quantity then add new quantity
    //             $stmt = $conn->prepare("UPDATE stock_entries 
    //                     SET purchase_qty = purchase_qty - ?, available_stock = available_stock - ? 
    //                     WHERE stock_id = ?");
    //             $stmt->bind_param("ddi", $previous_purchase_qty, $previous_purchase_qty, $stock_id);
    //             $stmt->execute();
    //             $stmt->close();

    //             // Add the updated quantity
    //             $stmt = $conn->prepare("UPDATE stock_entries 
    //                     SET purchase_qty = purchase_qty + ?, available_stock = available_stock + ? 
    //                     WHERE stock_id = ?");
    //             $stmt->bind_param("ddi", $purchase_qty, $purchase_qty, $stock_id);
    //             $stmt->execute();
    //             $stmt->close();
    //         } else {
    //             // First deduct the previous quantity from the original stock entry
    //             $stmt = $conn->prepare("UPDATE stock_entries 
    //                                     SET purchase_qty = purchase_qty - ?, available_stock = available_stock - ? 
    //                                     WHERE stock_id = ?");
    //             $stmt->bind_param("ddi", $previous_purchase_qty, $previous_purchase_qty, $stock_id);
    //             $stmt->execute();
    //             $stmt->close();
                
    //             // Check if purchase_qty is now zero and delete the record if it is
    //             $stmt = $conn->prepare("SELECT purchase_qty FROM stock_entries WHERE stock_id = ?");
    //             $stmt->bind_param("i", $stock_id);
    //             $stmt->execute();
    //             $result = $stmt->get_result();
                
    //             if ($result->num_rows > 0) {
    //                 $row = $result->fetch_assoc();
    //                 if ($row['purchase_qty'] <= 0) {
    //                     // Delete the stock entry if quantity is zero or negative
    //                     $stmt->close();
    //                     $stmt = $conn->prepare("DELETE FROM stock_entries WHERE stock_id = ?");
    //                     $stmt->bind_param("i", $stock_id);
    //                     $stmt->execute();
    //                     $stmt->close();
    //                 } else {
    //                     $stmt->close();
    //                 }
    //             } else {
    //                 $stmt->close();
    //             }

    //             // Check if there's a stock entry with the same prices, barcode, and branch
    //             $stmt = $conn->prepare("SELECT stock_id 
    //                     FROM stock_entries 
    //                     WHERE cost_price = ? AND wholesale_price = ? AND max_retail_price = ? 
    //                     AND super_customer_price = ? AND our_price = ? AND barcode = ? AND branch = ?");
    //             $stmt->bind_param("dddddss", $cost_price, $wholesale_price, $max_retail_price, $super_customer_price, $our_price, $barcode, $user_branch);
    //             $stmt->execute();
    //             $result = $stmt->get_result();

    //             if ($result->num_rows > 0) {
    //                 // If a matching stock entry exists, add the quantity to it
    //                 $matchingEntry = $result->fetch_assoc();
    //                 $matchingStockId = $matchingEntry['stock_id'];

    //                 $stmt = $conn->prepare("UPDATE stock_entries 
    //                             SET purchase_qty = purchase_qty + ?, available_stock = available_stock + ? 
    //                             WHERE stock_id = ?");
    //                 $stmt->bind_param("ddi", $purchase_qty, $purchase_qty, $matchingStockId);
    //                 $stmt->execute();
    //                 $stmt->close();

    //                 // Update the stock_id in stock_creations to link to the matching stock entry
    //                 $stmt = $conn->prepare("UPDATE stock_creations SET stock_id = ? WHERE id = ?");
    //                 $stmt->bind_param("ii", $matchingStockId, $id);
    //                 $stmt->execute();
    //                 $stmt->close();

    //             } else {
    //                 // Get the latest stock_id by retrieving the most recent record
    //                 // This is more efficient than MAX() if you have an index on created_at
    //                 $stmt = $conn->prepare("SELECT stock_id FROM stock_entries ORDER BY created_at DESC, id DESC LIMIT 1");
    //                 $stmt->execute();
    //                 $result = $stmt->get_result();
                    
    //                 if ($result->num_rows > 0) {
    //                     $row = $result->fetch_assoc();
    //                     $new_stock_id = $row['stock_id'] + 1;
    //                 } else {
    //                     // If no records exist yet, start with 1
    //                     $new_stock_id = 1;
    //                 }
    //                 $stmt->close();
                    
    //                 // Create a new stock entry with the new stock_id
    //                 $stmt = $conn->prepare("INSERT INTO stock_entries 
    //                                        (stock_id, supplier_id, product_name, itemcode, purchase_qty, unit, available_stock, cost_price, 
    //                                         wholesale_price, max_retail_price, super_customer_price, our_price, 
    //                                         low_stock, expire_date, discount_percent, barcode, branch) 
    //                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    
    //                 $stmt->bind_param(
    //                     "iissdssdddddisdss",
    //                     $new_stock_id,  // Use the new stock_id (latest + 1)
    //                     $supplier_id,
    //                     $product_name,
    //                     $barcode,
    //                     $purchase_qty,
    //                     $unit,
    //                     $purchase_qty, // available_stock equals purchase_qty initially
    //                     $cost_price,
    //                     $wholesale_price,
    //                     $max_retail_price,
    //                     $super_customer_price,
    //                     $our_price,
    //                     $low_stock,
    //                     $expire_date,
    //                     $discount_percent,
    //                     $barcode,
    //                     $user_branch,
    //                 );
                    
    //                 $stmt->execute();
    //                 $stmt->close();
                    
    //                 // Update the stock_creation with the new stock_id
    //                 $stmt = $conn->prepare("UPDATE stock_creations SET stock_id = ? WHERE id = ?");
    //                 $stmt->bind_param("ii", $new_stock_id, $id);
    //                 $stmt->execute();
    //                 $stmt->close();
    //             }
    //         }
    //     } else {
    //         // No existing stock entry found, create a new one
    //         $stmt = $conn->prepare("INSERT INTO stock_entries 
    //         (stock_id, supplier_id, product_name, itemcode, purchase_qty, unit, available_stock, cost_price, 
    //             wholesale_price, max_retail_price, super_customer_price, our_price, 
    //             low_stock, expire_date, discount_percent, barcode, branch, category) 
    //         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    //         $stmt->bind_param(
    //         "iissdssdddddisdsss",
    //         $stock_id,  // Use the existing stock_id 
    //         $supplier_id,
    //         $product_name,
    //         $barcode,
    //         $purchase_qty,
    //         $unit,
    //         $purchase_qty, // available_stock equals purchase_qty initially
    //         $cost_price,
    //         $wholesale_price,
    //         $max_retail_price,
    //         $super_customer_price,
    //         $our_price,
    //         $low_stock,
    //         $expire_date,
    //         $discount_percent,
    //         $barcode,
    //         $user_branch,
    //         $category
    //         );

    //         $stmt->execute();
    //         $stmt->close();
    //     }

    //     // After all the stock entry handling is complete, update the stock creation entry
    //     $query = "UPDATE stock_creations 
    //     SET product_name = ?, purchase_qty = ?, cost_price = ?, wholesale_price = ?, 
    //     max_retail_price = ?, super_customer_price = ?, our_price = ?, low_stock = ?, 
    //     expire_date = ?, discount_percent = ?, unit = ?, category = ?
    //     WHERE id = ?";
    //     $stmt = $conn->prepare($query);
    //     if ($stmt === false) {
    //         echo json_encode(['error' => 'Failed to prepare statement']);
    //         exit;
    //     }
    //     // Bind parameters
    //     $stmt->bind_param(
    //         "sddddddisdssi",
    //         $product_name,
    //         $purchase_qty,
    //         $cost_price,
    //         $wholesale_price,
    //         $max_retail_price,
    //         $super_customer_price,
    //         $our_price,
    //         $low_stock,
    //         $expire_date,
    //         $discount_percent,
    //         $unit,
    //         $category,
    //         $id
            
    //     );
    //     // Execute query
    //     if ($stmt->execute()) {
    //         // Get the stock_id from the updated record
    //         $getStockIdQuery = "SELECT stock_id FROM stock_creations WHERE id = ?";
    //         $getStockIdStmt = $conn->prepare($getStockIdQuery);
            
    //         if ($getStockIdStmt === false) {
    //             echo json_encode(['error' => 'Failed to prepare stock_id statement']);
    //             exit;
    //         }
            
    //         $getStockIdStmt->bind_param("i", $id);
    //         $getStockIdStmt->execute();
    //         $getStockIdStmt->bind_result($stock_id);
    //         $getStockIdStmt->fetch();
    //         $getStockIdStmt->close();
            
    //         // Update the low_stock value in stock_entries table
    //         $updateStockEntriesQuery = "UPDATE stock_entries SET low_stock = ?, category = ?, unit = ? WHERE stock_id = ?";
    //         $updateStockEntriesStmt = $conn->prepare($updateStockEntriesQuery);
            
    //         if ($updateStockEntriesStmt === false) {
    //             echo json_encode(['error' => 'Failed to prepare stock_entries update statement']);
    //             exit;
    //         }
            
    //         $updateStockEntriesStmt->bind_param("dssi", $low_stock, $category, $unit, $stock_id);
    //         $updateStockEntriesStmt->execute();
    //         $updateStockEntriesStmt->close();
            
    //         echo json_encode(['success' => true]);
    //     } else {
    //         echo json_encode(['success' => false, 'error' => 'Database update failed']);
    //     }
    //     exit;
    // }

    if ($_POST['action'] === 'updateStockCreation') {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Extract and validate fields
            $id = intval($_POST['id']);
            $supplier_id = intval($_POST['supplier_id']);
            $stock_id = intval($_POST['stock_id']);
            $product_name = $_POST['product_name'];
            $purchase_qty = floatval($_POST['purchase_qty']);
            $previous_purchase_qty = floatval($_POST['previous_purchase_qty']);
            $unit = $_POST['unit'];
            $cost_price = floatval($_POST['cost_price']);
            $previous_cost_price = floatval($_POST['previous_cost_price']);
            $wholesale_price = floatval($_POST['wholesale_price']);
            $max_retail_price = floatval($_POST['max_retail_price']);
            $super_customer_price = floatval($_POST['super_customer_price']);
            $our_price = floatval($_POST['our_price']);
            $low_stock = floatval($_POST['low_stock']);
            $expire_date = !empty($_POST['expire_date']) ? $_POST['expire_date'] : NULL;
            $discount_percent = floatval($_POST['discount_percent']);
            $barcode = $_POST['barcode'];
    
            // Calculate previous total cost and new total cost
            $previous_total_cost = $previous_cost_price * $previous_purchase_qty;
            $new_total_cost = $cost_price * $purchase_qty;
    
            // Update supplier's credit_balance - deduct previous cost and add new cost
            $stmt = $conn->prepare("UPDATE suppliers SET credit_balance = credit_balance - ? + ? WHERE supplier_id = ?");
            $stmt->bind_param("ddi", $previous_total_cost, $new_total_cost, $supplier_id);
            if (!$stmt->execute()) {
                throw new Exception("Failed to update supplier's credit balance: " . $stmt->error);
            }
            $stmt->close();
    
            // Get current stock entry
            $stmt = $conn->prepare("SELECT cost_price, wholesale_price, max_retail_price, super_customer_price, our_price, purchase_qty, available_stock 
                                    FROM stock_entries 
                                    WHERE stock_id = ?");
            $stmt->bind_param("i", $stock_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stockEntry = $result->fetch_assoc();
            $stmt->close();
    
            $new_stock_id = $stock_id; // Default to current stock_id
            
            if ($stockEntry) {
                // Check if prices match with the stock entry
                $pricesMatch = (
                    $cost_price == $stockEntry['cost_price'] &&
                    $wholesale_price == $stockEntry['wholesale_price'] &&
                    $max_retail_price == $stockEntry['max_retail_price'] &&
                    $super_customer_price == $stockEntry['super_customer_price'] &&
                    $our_price == $stockEntry['our_price']
                );
    
                if ($pricesMatch) {
                    // Update quantity in a single operation
                    $stmt = $conn->prepare("UPDATE stock_entries 
                                            SET purchase_qty = purchase_qty - ? + ?, 
                                                available_stock = available_stock - ? + ? 
                                            WHERE stock_id = ?");
                    $stmt->bind_param("ddddi", $previous_purchase_qty, $purchase_qty, 
                                     $previous_purchase_qty, $purchase_qty, $stock_id);
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to update stock quantities: " . $stmt->error);
                    }
                    $stmt->close();
                } else {
                    // Handle price changes - deduct from original stock entry
                    $stmt = $conn->prepare("UPDATE stock_entries 
                                            SET purchase_qty = purchase_qty - ?, 
                                                available_stock = available_stock - ? 
                                            WHERE stock_id = ?");
                    $stmt->bind_param("ddi", $previous_purchase_qty, $previous_purchase_qty, $stock_id);
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to adjust original stock quantities: " . $stmt->error);
                    }
                    $stmt->close();
                    
                    // Check if purchase_qty is now zero and delete if necessary
                    $stmt = $conn->prepare("SELECT purchase_qty FROM stock_entries WHERE stock_id = ?");
                    $stmt->bind_param("i", $stock_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        if ($row['purchase_qty'] <= 0) {
                            $stmt->close();
                            $stmt = $conn->prepare("DELETE FROM stock_entries WHERE stock_id = ?");
                            $stmt->bind_param("i", $stock_id);
                            if (!$stmt->execute()) {
                                throw new Exception("Failed to delete empty stock entry: " . $stmt->error);
                            }
                            $stmt->close();
                        } else {
                            $stmt->close();
                        }
                    } else {
                        $stmt->close();
                    }
    
                    // Check for existing stock with matching prices
                    $stmt = $conn->prepare("SELECT stock_id 
                                            FROM stock_entries 
                                            WHERE cost_price = ? AND wholesale_price = ? AND max_retail_price = ? 
                                            AND super_customer_price = ? AND our_price = ? AND barcode = ? AND branch = ?");
                    $stmt->bind_param("dddddss", $cost_price, $wholesale_price, $max_retail_price, 
                                    $super_customer_price, $our_price, $barcode, $user_branch);
                    $stmt->execute();
                    $result = $stmt->get_result();
    
                    if ($result->num_rows > 0) {
                        // Matching stock entry exists - update it
                        $matchingEntry = $result->fetch_assoc();
                        $new_stock_id = $matchingEntry['stock_id'];
                        $stmt->close();
    
                        $stmt = $conn->prepare("UPDATE stock_entries 
                                              SET purchase_qty = purchase_qty + ?, 
                                                  available_stock = available_stock + ? 
                                              WHERE stock_id = ?");
                        $stmt->bind_param("ddi", $purchase_qty, $purchase_qty, $new_stock_id);
                        if (!$stmt->execute()) {
                            throw new Exception("Failed to update matching stock entry: " . $stmt->error);
                        }
                        $stmt->close();
                    } else {
                        $stmt->close();
                        
                        // Create new stock entry with new stock_id
                        $stmt = $conn->prepare("SELECT stock_id FROM stock_entries ORDER BY created_at DESC, id DESC LIMIT 1");
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows > 0) {
                            $row = $result->fetch_assoc();
                            $new_stock_id = $row['stock_id'] + 1;
                        } else {
                            $new_stock_id = 1;
                        }
                        $stmt->close();
                        
                        // Insert new stock entry
                        $stmt = $conn->prepare("INSERT INTO stock_entries 
                                             (stock_id, supplier_id, product_name, itemcode, purchase_qty, unit, 
                                              available_stock, cost_price, wholesale_price, max_retail_price, 
                                              super_customer_price, our_price, low_stock, expire_date, 
                                              discount_percent, barcode, branch) 
                                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        
                        $stmt->bind_param(
                            "iissdssdddddisdss",
                            $new_stock_id,
                            $supplier_id,
                            $product_name,
                            $barcode,
                            $purchase_qty,
                            $unit,
                            $purchase_qty,
                            $cost_price,
                            $wholesale_price,
                            $max_retail_price,
                            $super_customer_price,
                            $our_price,
                            $low_stock,
                            $expire_date,
                            $discount_percent,
                            $barcode,
                            $user_branch
                        );
                        
                        if (!$stmt->execute()) {
                            throw new Exception("Failed to create new stock entry: " . $stmt->error);
                        }
                        $stmt->close();
                    }
                }
            } else {
                // No existing stock - create new entry with existing stock_id
                $stmt = $conn->prepare("INSERT INTO stock_entries 
                                      (stock_id, supplier_id, product_name, itemcode, purchase_qty, unit, 
                                       available_stock, cost_price, wholesale_price, max_retail_price, 
                                       super_customer_price, our_price, low_stock, expire_date, 
                                       discount_percent, barcode, branch) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->bind_param(
                    "iissdssdddddisdss",
                    $stock_id,
                    $supplier_id,
                    $product_name,
                    $barcode,
                    $purchase_qty,
                    $unit,
                    $purchase_qty,
                    $cost_price,
                    $wholesale_price,
                    $max_retail_price,
                    $super_customer_price,
                    $our_price,
                    $low_stock,
                    $expire_date,
                    $discount_percent,
                    $barcode,
                    $user_branch,
                );
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to create stock entry: " . $stmt->error);
                }
                $stmt->close();
            }
    
            // Update stock_creations record if needed
            if ($new_stock_id != $stock_id) {
                $stmt = $conn->prepare("UPDATE stock_creations SET stock_id = ? WHERE id = ?");
                $stmt->bind_param("ii", $new_stock_id, $id);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update stock_id in stock_creations: " . $stmt->error);
                }
                $stmt->close();
            }
    
            // Update stock creation entry
            $stmt = $conn->prepare("UPDATE stock_creations 
                                  SET product_name = ?, purchase_qty = ?, cost_price = ?, 
                                      wholesale_price = ?, max_retail_price = ?, super_customer_price = ?, 
                                      our_price = ?, low_stock = ?, expire_date = ?, 
                                      discount_percent = ?, unit = ?, total_cost_amount = ?
                                  WHERE id = ?");
            
            if ($stmt === false) {
                throw new Exception("Failed to prepare stock_creations update statement: " . $conn->error);
            }
            
            $stmt->bind_param(
                "sddddddisdsdi",
                $product_name,
                $purchase_qty,
                $cost_price,
                $wholesale_price,
                $max_retail_price,
                $super_customer_price,
                $our_price,
                $low_stock,
                $expire_date,
                $discount_percent,
                $unit,
                $new_total_cost,
                $id
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update stock_creations: " . $stmt->error);
            }
            $stmt->close();
            
            // Update the low_stock and unit in stock_entries table
            $stmt = $conn->prepare("UPDATE stock_entries 
                                  SET low_stock = ?, unit = ? 
                                  WHERE stock_id = ?");
            
            if ($stmt === false) {
                throw new Exception("Failed to prepare stock_entries update statement: " . $conn->error);
            }
            
            $stmt->bind_param("dsi", $low_stock, $unit, $new_stock_id);
            if (!$stmt->execute()) {
                throw new Exception("Failed to update stock_entries attributes: " . $stmt->error);
            }
            $stmt->close();
            
            // All operations successful, commit the transaction
            $conn->commit();
            echo json_encode(['success' => true]);
            
        } catch (Exception $e) {
            // An error occurred, rollback the transaction
            $conn->rollback();
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        
        exit;
    }


    // Handle deleteStockCreation
    // if ($_POST['action'] === 'deleteStockCreation') {
    //     $id = intval($_POST['id']);
        
    //     // First, get the stock_id and purchase_qty of the record to be deleted
    //     $stmt = $conn->prepare("SELECT stock_id, purchase_qty FROM stock_creations WHERE id = ?");
    //     $stmt->bind_param("i", $id);
    //     $stmt->execute();
    //     $result = $stmt->get_result();
        
    //     if ($result->num_rows > 0) {
    //         $row = $result->fetch_assoc();
    //         $stock_id = $row['stock_id'];
    //         $purchase_qty = $row['purchase_qty'];
    //         $stmt->close();
            
    //         // Begin transaction to ensure data consistency
    //         $conn->begin_transaction();
            
    //         try {
    //             // 1. Delete the record from stock_creations
    //             $stmt = $conn->prepare("DELETE FROM stock_creations WHERE id = ?");
    //             $stmt->bind_param("i", $id);
    //             $stmt->execute();
    //             $stmt->close();
                
    //             // 2. Update stock_entries to deduct the purchase_qty and available_stock
    //             $stmt = $conn->prepare("UPDATE stock_entries 
    //                                     SET purchase_qty = purchase_qty - ?, available_stock = available_stock - ? 
    //                                     WHERE stock_id = ?");
    //             $stmt->bind_param("ddi", $purchase_qty, $purchase_qty, $stock_id);
    //             $stmt->execute();
    //             $stmt->close();
                
    //             // 3. Check if purchase_qty is now zero and delete if necessary
    //             $stmt = $conn->prepare("SELECT purchase_qty FROM stock_entries WHERE stock_id = ?");
    //             $stmt->bind_param("i", $stock_id);
    //             $stmt->execute();
    //             $result = $stmt->get_result();
                
    //             if ($result->num_rows > 0) {
    //                 $row = $result->fetch_assoc();
    //                 if ($row['purchase_qty'] <= 0) {
    //                     // Delete the stock entry if quantity is zero or negative
    //                     $stmt->close();
    //                     $stmt = $conn->prepare("DELETE FROM stock_entries WHERE stock_id = ?");
    //                     $stmt->bind_param("i", $stock_id);
    //                     $stmt->execute();
    //                     $stmt->close();
    //                 } else {
    //                     $stmt->close();
    //                 }
    //             } else {
    //                 $stmt->close();
    //             }
                
    //             // Commit the transaction
    //             $conn->commit();
    //             echo json_encode(['success' => true]);
                
    //         } catch (Exception $e) {
    //             // If any operation fails, roll back the transaction
    //             $conn->rollback();
    //             echo json_encode(['success' => false, 'error' => 'Database operation failed: ' . $e->getMessage()]);
    //         }
    //     } else {
    //         echo json_encode(['success' => false, 'error' => 'Record not found']);
    //     }
    //     exit;
    // }

    // if ($_POST['action'] === 'deleteStockCreation') {
    //     $id = intval($_POST['id']);
        
    //     // First, get the stock_id, purchase_qty, supplier_id, and cost_price of the record to be deleted
    //     $stmt = $conn->prepare("SELECT sc.stock_id, sc.purchase_qty, sc.supplier_id, sc.cost_price 
    //                            FROM stock_creations sc 
    //                            WHERE sc.id = ?");
    //     $stmt->bind_param("i", $id);
    //     $stmt->execute();
    //     $result = $stmt->get_result();
        
    //     if ($result->num_rows > 0) {
    //         $row = $result->fetch_assoc();
    //         $stock_id = $row['stock_id'];
    //         $purchase_qty = $row['purchase_qty'];
    //         $supplier_id = $row['supplier_id'];
    //         $cost_price = $row['cost_price'];
    //         $stmt->close();
            
    //         // Calculate the total cost to be deducted from supplier's credit balance
    //         $total_cost = $cost_price * $purchase_qty;
            
    //         // Begin transaction to ensure data consistency
    //         $conn->begin_transaction();
            
    //         try {
    //             // 0. Update supplier's credit_balance by deducting the total cost
    //             $stmt = $conn->prepare("UPDATE suppliers SET credit_balance = credit_balance - ? WHERE supplier_id = ?");
    //             $stmt->bind_param("di", $total_cost, $supplier_id);
    //             $stmt->execute();
    //             $stmt->close();
                
    //             // 1. Delete the record from stock_creations
    //             $stmt = $conn->prepare("DELETE FROM stock_creations WHERE id = ?");
    //             $stmt->bind_param("i", $id);
    //             $stmt->execute();
    //             $stmt->close();
                
    //             // 2. Update stock_entries to deduct the purchase_qty and available_stock
    //             $stmt = $conn->prepare("UPDATE stock_entries 
    //                                     SET purchase_qty = purchase_qty - ?, available_stock = available_stock - ? 
    //                                     WHERE stock_id = ?");
    //             $stmt->bind_param("ddi", $purchase_qty, $purchase_qty, $stock_id);
    //             $stmt->execute();
    //             $stmt->close();
                
    //             // 3. Check if purchase_qty is now zero and delete if necessary
    //             $stmt = $conn->prepare("SELECT purchase_qty FROM stock_entries WHERE stock_id = ?");
    //             $stmt->bind_param("i", $stock_id);
    //             $stmt->execute();
    //             $result = $stmt->get_result();
                
    //             if ($result->num_rows > 0) {
    //                 $row = $result->fetch_assoc();
    //                 if ($row['purchase_qty'] <= 0) {
    //                     // Delete the stock entry if quantity is zero or negative
    //                     $stmt->close();
    //                     $stmt = $conn->prepare("DELETE FROM stock_entries WHERE stock_id = ?");
    //                     $stmt->bind_param("i", $stock_id);
    //                     $stmt->execute();
    //                     $stmt->close();
    //                 } else {
    //                     $stmt->close();
    //                 }
    //             } else {
    //                 $stmt->close();
    //             }
                
    //             // Commit the transaction
    //             $conn->commit();
    //             echo json_encode(['success' => true]);
                
    //         } catch (Exception $e) {
    //             // If any operation fails, roll back the transaction
    //             $conn->rollback();
    //             echo json_encode(['success' => false, 'error' => 'Database operation failed: ' . $e->getMessage()]);
    //         }
    //     } else {
    //         echo json_encode(['success' => false, 'error' => 'Record not found']);
    //     }
    //     exit;
    // }


    if ($_POST['action'] === 'deleteStockCreation') {
        $id = intval($_POST['id']);
        $response = ['success' => false];
        
        try {
            // Begin transaction
            $conn->begin_transaction();
            
            // Get details about the stock creation to be deleted
            $stmt = $conn->prepare("SELECT sc.stock_id, sc.purchase_qty, sc.supplier_id, sc.cost_price 
                                   FROM stock_creations sc 
                                   WHERE sc.id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception("Record not found");
            }
            
            $row = $result->fetch_assoc();
            $stock_id = $row['stock_id'];
            $purchase_qty = $row['purchase_qty'];
            $supplier_id = $row['supplier_id'];
            $cost_price = $row['cost_price'];
            $total_cost = $cost_price * $purchase_qty;
            $stmt->close();
            
            // Update supplier's credit_balance
            $stmt = $conn->prepare("UPDATE suppliers SET credit_balance = credit_balance - ? WHERE supplier_id = ?");
            $stmt->bind_param("di", $total_cost, $supplier_id);
            if (!$stmt->execute()) {
                throw new Exception("Failed to update supplier credit: " . $stmt->error);
            }
            $stmt->close();
            
            // Delete from stock_creations
            $stmt = $conn->prepare("DELETE FROM stock_creations WHERE id = ?");
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                throw new Exception("Failed to delete stock creation: " . $stmt->error);
            }
            $stmt->close();
            
            // Update stock_entries
            $stmt = $conn->prepare("UPDATE stock_entries 
                                    SET purchase_qty = purchase_qty - ?, 
                                        available_stock = available_stock - ? 
                                    WHERE stock_id = ?");
            $stmt->bind_param("ddi", $purchase_qty, $purchase_qty, $stock_id);
            if (!$stmt->execute()) {
                throw new Exception("Failed to update stock entries: " . $stmt->error);
            }
            $stmt->close();
            
            // Check if we need to delete the stock entry (if quantity is zero or less)
            $stmt = $conn->prepare("DELETE FROM stock_entries WHERE stock_id = ? AND purchase_qty <= 0");
            $stmt->bind_param("i", $stock_id);
            $stmt->execute();
            $stmt->close();
            
            // Commit the transaction
            $conn->commit();
            $response = ['success' => true];
            
        } catch (Exception $e) {
            // Roll back on any error
            $conn->rollback();
            $response = ['success' => false, 'error' => $e->getMessage()];
        }
        
        // Return JSON response
        echo json_encode($response);
        exit;
    }
}


if (isset($_POST['action']) && $_POST['action'] == 'updateAvailableStock') {
    updateAvailableStock();
}

/**
 * Updates the available stock for a specific item
 */
function updateAvailableStock() {
    global $conn;
    
    // Validate inputs
    if (!isset($_POST['id']) || !isset($_POST['available_stock'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        return;
    }
    
    $id = intval($_POST['id']);
    $availableStock = floatval($_POST['available_stock']);
    
    // Validate stock ID
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid stock ID']);
        return;
    }
    
    // Validate stock quantity
    if ($availableStock < 0) {
        echo json_encode(['success' => false, 'message' => 'Stock quantity cannot be negative']);
        return;
    }
    
    try {
        // Update the available stock in the database
        $stmt = $conn->prepare("UPDATE stock_entries SET available_stock = ? WHERE id = ?");
        $stmt->bind_param("di", $availableStock, $id);
        
        if ($stmt->execute()) {
            // Get updated row to return
            $getStmt = $conn->prepare("SELECT * FROM stock_entries WHERE id = ?");
            $getStmt->bind_param("i", $id);
            $getStmt->execute();
            $result = $getStmt->get_result();
            $updatedEntry = $result->fetch_assoc();
            
            echo json_encode([
                'success' => true, 
                'message' => 'Stock updated successfully',
                'data' => $updatedEntry
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update stock: ' . $stmt->error]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}