<?php
require_once '../../../models/Database.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $barcode = $_POST['barcode'];
    $new_stock = $_POST['new_stock'];

    if (!empty($barcode) && isset($new_stock)) {
        // Get current product information before update
        $info_stmt = $db_conn->prepare("SELECT * FROM production_bulks WHERE itemcode = ?");
        $info_stmt->bind_param("s", $barcode);
        $info_stmt->execute();
        $result = $info_stmt->get_result();
        
        if ($result->num_rows > 0) {
            $stock_info = $result->fetch_assoc();
            $old_stock = $stock_info['available_stock'];
            
            // Update stock_entries
            $stmt = $db_conn->prepare("UPDATE production_bulks SET available_stock = ? WHERE itemcode = ?");
            $stmt->bind_param("ds", $new_stock, $barcode);
            $update_success = $stmt->execute();
            $stmt->close();
            
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
