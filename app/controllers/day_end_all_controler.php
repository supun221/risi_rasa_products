<?php
include('../../config/databade.php'); // Ensure this file exists and is correctly configured

// Ensure database connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

// Handle AJAX requests
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);

    if (isset($input['saveDayEndReport'])) {
        saveDayEndReport($conn, $input);
    }
}

function saveDayEndReport($conn, $data) {
    // Prepare the SQL query with 17 placeholders (`?`) for 17 VARCHAR columns
    $stmt = $conn->prepare("INSERT INTO day_end_reports 
        (username, opening_balance, total_gross, total_net, total_discount, total_bills, total_cash, total_credit, bill_payment, cash_drawer, voucher_payment, free_payment, total_balance, day_end_hand_balance, cash_balance, today_balance, difference_hand) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        die(json_encode(["error" => "Prepare failed: " . $conn->error]));
    }

    // Ensure all values exist and are treated as strings
    $username = isset($data['username']) ? $data['username'] : "";
    $opening_balance = isset($data['opening_balance']) ? (string) $data['opening_balance'] : "0.00";
    $total_gross = isset($data['total_gross']) ? (string) $data['total_gross'] : "0.00";
    $total_net = isset($data['total_net']) ? (string) $data['total_net'] : "0.00";
    $total_discount = isset($data['total_discount']) ? (string) $data['total_discount'] : "0.00";
    $total_bills = isset($data['total_bills']) ? (string) $data['total_bills'] : "0";
    $total_cash = isset($data['total_cash']) ? (string) $data['total_cash'] : "0.00";
    $total_credit = isset($data['total_credit']) ? (string) $data['total_credit'] : "0.00";
    $bill_payment = isset($data['bill_payment']) ? (string) $data['bill_payment'] : "0.00";
    $cash_drawer = isset($data['cash_drawer']) ? (string) $data['cash_drawer'] : "0.00";
    $voucher_payment = isset($data['voucher_payment']) ? (string) $data['voucher_payment'] : "0.00";
    $free_payment = isset($data['free_payment']) ? (string) $data['free_payment'] : "0.00";
    $total_balance = isset($data['total_balance']) ? (string) $data['total_balance'] : "0.00";
    $day_end_hand_balance = isset($data['day_end_hand_balance']) ? (string) $data['day_end_hand_balance'] : "0.00";
    $cash_balance = isset($data['cash_balance']) ? (string) $data['cash_balance'] : "0.00";
    $today_balance = isset($data['today_balance']) ? (string) $data['today_balance'] : "0.00";
    $difference_hand = isset($data['difference_hand']) ? (string) $data['difference_hand'] : "0.00";

    // ✅ Bind parameters with the CORRECT type string ("sssssssssssssssss" for all VARCHAR)
    $stmt->bind_param(
        "sssssssssssssssss",
        $username,
        $opening_balance,
        $total_gross,
        $total_net,
        $total_discount,
        $total_bills,
        $total_cash,
        $total_credit,
        $bill_payment,
        $cash_drawer,
        $voucher_payment,
        $free_payment,
        $total_balance,
        $day_end_hand_balance,
        $cash_balance,
        $today_balance,
        $difference_hand
    );

    // Execute the statement
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["error" => "Failed to save report: " . $stmt->error]);
    }

    $stmt->close();
}

$conn->close();
?>
