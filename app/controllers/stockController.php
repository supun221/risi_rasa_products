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
                se.itemcode,
                se.free,
                se.gift,
                se.voucher
              FROM stock_entries se
              LEFT JOIN suppliers s ON se.supplier_id = s.supplier_id
              WHERE (se.product_name LIKE ? OR se.itemcode LIKE ?) AND se.branch = ?
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
                   WHERE (se.product_name LIKE ? OR se.stock_id LIKE ?) AND se.branch = ?";

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
        $itemcode = $_POST['itemcode'];
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
            $itemcode,
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
