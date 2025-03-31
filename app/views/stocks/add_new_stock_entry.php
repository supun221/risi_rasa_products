<?php
include '../../models/Database.php';
include '../../models/POS_Product.php';

session_start();
$user_name = $_SESSION['username'];
$user_role = $_SESSION['job_role'];
$user_branch = $_SESSION['store'];

if(!$user_name && !$user_branch){
    header("Location: ../unauthorized/unauthorized_access.php");
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || empty($data['stock_id']) || empty($data['supplier']) || empty($data['transferId'])) {
    echo json_encode(["status" => "error", "message" => "Invalid stock data."]);
    exit();
}

try {
    // Start transaction
    $db_conn->begin_transaction();

    // Get supplier_id from supplier table
    $sql = "SELECT supplier_id FROM suppliers WHERE supplier_name = ?";
    $stmt = $db_conn->prepare($sql);
    $stmt->bind_param("s", $data['supplier']);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        throw new Exception("Supplier not found.");
    }

    $stmt->bind_result($supplier_id);
    $stmt->fetch();
    $stmt->close();

    // Insert into stock_entries
    $sql = "INSERT INTO stock_entries 
        (stock_id, supplier_id, itemcode, product_name, purchase_qty, unit, available_stock, cost_price, 
        wholesale_price, max_retail_price, super_customer_price, our_price, expire_date, 
        discount_percent, barcode, deal_price, start_date, end_date, total_cost_amount, branch) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $db_conn->prepare($sql);
    $stmt->bind_param("isssssssddddsdssssds",
        $data['stock_id'], $supplier_id, $data['itemcode'], $data['product_name'],
        $data['purchase_qty'], $data['unit'], $data['available_stock'], $data['cost_price'],
        $data['wholesale_price'], $data['max_retail_price'], $data['super_customer_price'],
        $data['our_price'], $data['expire_date'], $data['discount_percent'],
        $data['barcode'], $data['deal_price'], $data['start_date'], $data['end_date'], 
        $data['total_cost_amount'], $user_branch
    );

    if (!$stmt->execute()) {
        throw new Exception("Failed to add stock entry.");
    }

    // Update stock_transfer_items state
    $sql = "UPDATE stock_transfer_items SET state = 'added' WHERE id = ?";
    $stmt = $db_conn->prepare($sql);
    $stmt->bind_param("i", $data['transferId']);

    if (!$stmt->execute()) {
        throw new Exception("Failed to update transfer state.");
    }

    // Commit transaction
    $db_conn->commit();

    echo json_encode(["status" => "success", "message" => "Stock entry added and transfer state updated."]);
} catch (Exception $e) {
    // Rollback transaction on error
    $db_conn->rollback();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
