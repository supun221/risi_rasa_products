<?php
// Database connection
include('../../config/databade.php');

session_start(); // Start the session to access the logged-in user

$today = date('Y-m-d');
$username = $_SESSION['username']; // Assuming the username is stored in the session


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $query = '';
    $stmt = null;
    $data = [];
    if (isset($_POST['getUsername'])) {
        echo json_encode(['username' => $_SESSION['username'] ?? 'Guest']);
        exit;
    }
    elseif (isset($_POST['getOpeningBalance'])) {
        // Get yesterday's date
        $today = date('Y-m-d');

        // Query to fetch the opening balance (total gross amount from the previous day)
        $query = "
            SELECT 
                total_balance
            FROM 
                opening_balance
            WHERE 
                date = ? AND username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $today, $username);

    } elseif (isset($_POST['getDayEndData'])) {
        // Query to fetch data for the day
        $query = "
            SELECT 
            SUM(gross_amount) AS total_gross,
            SUM(net_amount) AS total_net,
            SUM(discount_amount) AS total_discount,
            COUNT(*) AS total_bills,
            SUM(gross_amount) AS total_balance,
            SUM(CASE WHEN payment_type = 'cash_payment' THEN net_amount ELSE 0 END) AS total_cash,
            SUM(CASE WHEN payment_type = 'credit_payment' THEN net_amount ELSE 0 END) AS total_credit,
            SUM(CASE WHEN payment_type = 'bill_payment' THEN net_amount ELSE 0 END) AS total_bill_payment,
            SUM(CASE WHEN payment_type = 'voucher_payment' THEN net_amount ELSE 0 END) AS total_voucher_payment,
            SUM(CASE WHEN payment_type = 'free_payment' THEN net_amount ELSE 0 END) AS total_free_payment
        FROM bill_records
        WHERE bill_date = ? AND issuer = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $today, $username);
    } elseif (isset($_POST['getTodayCashDrawerPayment'])) {
        // Query to fetch today's cash drawer payments from the payments table
        $query = "
            SELECT 
            SUM(amount) AS total_cash_drawer
            FROM payments
            WHERE date = ? AND payment_method = 'cash' AND paid_by = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $today, $username);
    } elseif (isset($_POST['getDayEndHandBalance'])) {
        // Query to fetch the day end hand balance from the day_end_balance table
        $query = "
            SELECT 
                total_balance
            FROM 
                day_end_balance
            WHERE 
                date = ? AND username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $today, $username);
    } else {
        echo json_encode(['error' => 'Invalid request']);
        exit;
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    // Handle the case when no rows are returned
    if (!$data) {
        $data = [
            'total_gross' => '0.00',
            'total_net' => '0.00',
            'total_discount' => '0.00',
            'total_bills' => 0,
            'total_balance' => '0.00',
            'total_cash' => '0.00',
            'total_credit' => '0.00',
            'total_bill_payment' => '0.00',
            'total_voucher_payment' => '0.00',
            'total_free_payment' => '0.00',
            'total_cash_drawer' => '0.00', // Default for cash drawer
            'opening_balance' => '0.00', // Default for opening balance
            'day_end_hand_balance' => '0.00', // Default for day end hand balance
        ];
    }

    // Return data as JSON
    echo json_encode($data);
    exit;
}
?>