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


// Get POST data
$supplier_id = $_POST['supplier_id'];
$supplier_name = $_POST['supplier_name'];
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$discount_type = $_POST['discountType'];
$discount_percentage = ($discount_type === 'percentage') ? $_POST['discount_percentage'] : NULL;
$discount_value = ($discount_type === 'value') ? $_POST['discount_value'] : NULL;

// Fetch all stock entries for the supplier
$stock_sql = "SELECT stock_id, our_price FROM stock_entries WHERE supplier_id = ?";
$stock_stmt = $conn->prepare($stock_sql);
$stock_stmt->bind_param("i", $supplier_id);
$stock_stmt->execute();
$stock_result = $stock_stmt->get_result();

$response = [];

if ($stock_result->num_rows > 0) {
    // Loop through each stock entry and calculate the deal_price
    while ($row = $stock_result->fetch_assoc()) {
        $stock_id = $row['stock_id'];
        $our_price = $row['our_price'];

        // Calculate the deal price based on the discount type
        if ($discount_type === 'percentage') {
            $deal_price = $our_price - ($our_price * ($discount_percentage / 100));
        } else if ($discount_type === 'value') {
            $deal_price = $our_price - $discount_value;
        }

        // Update the stock_entries table with the new deal price, start date, and end date
        $update_sql = "UPDATE stock_entries SET deal_price = ?, start_date = ?, end_date = ? WHERE supplier_id = ? AND stock_id= ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("dssis", $deal_price, $start_date, $end_date, $supplier_id, $stock_id);

        if (!$update_stmt->execute()) {
            $response["success"] = false;
            $response["message"] = "Error updating stock entry for item_code $item_code: " . $conn->error;
            echo json_encode($response);
            exit;  // Stop execution if update fails
        }
    }

    // Check if supplier_id already exists in supplier_promotions
    $check_sql = "SELECT * FROM supplier_promotions WHERE supplier_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $supplier_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Update existing promotion record
        $update_promo_sql = "UPDATE supplier_promotions 
                            SET start_date = ?, end_date = ?, discount_percentage = ?, discount_value = ? 
                            WHERE supplier_id = ?";
        $update_promo_stmt = $conn->prepare($update_promo_sql);
        $update_promo_stmt->bind_param("ssddi", $start_date, $end_date, $discount_percentage, $discount_value, $supplier_id);

        if ($update_promo_stmt->execute()) {
            $response["success"] = true;
            $response["message"] = "Promotion updated and stock entries updated successfully!";
        } else {
            $response["success"] = false;
            $response["message"] = "Error updating promotion: " . $conn->error;
        }
        $update_promo_stmt->close();
    } else {
        // Insert new promotion record
        $insert_sql = "INSERT INTO supplier_promotions (supplier_id, supplier_name, start_date, end_date, discount_percentage, discount_value, branch) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("isssdds", $supplier_id, $supplier_name, $start_date, $end_date, $discount_percentage, $discount_value, $user_branch);

        if ($insert_stmt->execute()) {
            $response["success"] = true;
            $response["message"] = "Promotion saved and stock entries updated successfully!";
        } else {
            $response["success"] = false;
            $response["message"] = "Error saving promotion: " . $conn->error;
        }
        $insert_stmt->close();
    }

    $check_stmt->close();
} else {
    $response["success"] = false;
    $response["message"] = "No stock entries found for supplier_id $supplier_id";
}

$stock_stmt->close();
$conn->close();

echo json_encode($response);
?>
