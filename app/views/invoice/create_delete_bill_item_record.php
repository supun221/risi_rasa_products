<?php
include '../../models/Database.php';
include '../../models/POS_Checkout.php';

$checkoutHandler = new POS_CHECKOUT($db_conn);

function getCurrentDate() {
    return date('Y-m-d');
}

$currentDate = getCurrentDate();

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data["bill_id"], $data["item_name"], $data["barcode"], $data["unit_price"], $data["quantity"], $data["total_price"], $data["discount"], $data["bill_type"], $data["deleted_by"])) {
        
        $response = $checkoutHandler->createDeletedBillItem(
            $data["bill_id"], 
            $data["item_name"], 
            $data["barcode"], 
            $data["unit_price"], 
            $data["quantity"], 
            $data["total_price"], 
            $data["discount"], 
            $data["bill_type"], 
            $data["deleted_by"],
            $currentDate
        );

        echo $response;
    } else {
        echo json_encode(["status" => "error", "message" => "Missing required fields."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>
