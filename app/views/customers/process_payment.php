<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once '../../../config/databade.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate customer ID
    if (!isset($_POST['customer_id']) || empty($_POST['customer_id'])) {
        $_SESSION['payment_error'] = "Invalid customer ID.";
        header("Location: customer_list.php");
        exit;
    }

    $customerId = mysqli_real_escape_string($conn, $_POST['customer_id']);
    $amount = mysqli_real_escape_string($conn, $_POST['amount']);
    $paymentMethod = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $reference = mysqli_real_escape_string($conn, $_POST['reference']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);
    $invoiceNumber = mysqli_real_escape_string($conn, $_POST['invoice_number']);
    $chequeNumber = isset($_POST['cheque_number']) ? mysqli_real_escape_string($conn, $_POST['cheque_number']) : '';

    // Validate amount
    if (!is_numeric($amount) || $amount <= 0) {
        $_SESSION['payment_error'] = "Please enter a valid payment amount.";
        header("Location: payment.php?id=" . urlencode($customerId));
        exit;
    }

    // Validate payment method
    $validPaymentMethods = ['cash', 'card', 'cheque', 'bank_transfer', 'online'];
    if (!in_array($paymentMethod, $validPaymentMethods)) {
        $_SESSION['payment_error'] = "Invalid payment method.";
        header("Location: payment.php?id=" . urlencode($customerId));
        exit;
    }

    // Get current credit balance before update
    $getBalanceQuery = "SELECT credit_balance FROM customers WHERE id = '$customerId'";
    $balanceResult = mysqli_query($conn, $getBalanceQuery);
    $previousCreditBalance = 0;
    
    if ($balanceResult && $customerData = mysqli_fetch_assoc($balanceResult)) {
        $previousCreditBalance = $customerData['credit_balance'];
    }
    
    // Check if the cheque_number column exists in the customer_payments table
    $columnCheckQuery = "SHOW COLUMNS FROM customer_payments LIKE 'cheque_number'";
    $columnCheckResult = mysqli_query($conn, $columnCheckQuery);
    $chequeNumberColumnExists = mysqli_num_rows($columnCheckResult) > 0;
    
    // Add the column if it doesn't exist
    if (!$chequeNumberColumnExists) {
        $alterTableQuery = "ALTER TABLE customer_payments ADD COLUMN cheque_number VARCHAR(50) DEFAULT NULL AFTER payment_method";
        $alterResult = mysqli_query($conn, $alterTableQuery);
        
        if (!$alterResult) {
            $_SESSION['payment_error'] = "Failed to update database structure. Please contact administrator.";
            header("Location: payment.php?id=" . urlencode($customerId));
            exit;
        }
    }

    // Begin transaction to ensure data consistency
    mysqli_begin_transaction($conn);

    try {
        // Insert payment record
        $insertQuery = "INSERT INTO customer_payments (customer_id, amount, payment_method, reference, notes, invoice_number, cheque_number) 
                        VALUES ('$customerId', '$amount', '$paymentMethod', '$reference', '$notes', '$invoiceNumber', '$chequeNumber')";
        
        $insertResult = mysqli_query($conn, $insertQuery);
        
        if (!$insertResult) {
            throw new Exception("Error recording payment: " . mysqli_error($conn));
        }
        
        $paymentId = mysqli_insert_id($conn);

        // Update customer credit balance
        $updateQuery = "UPDATE customers SET credit_balance = credit_balance - $amount WHERE id = '$customerId'";
        $updateResult = mysqli_query($conn, $updateQuery);
        
        if (!$updateResult) {
            throw new Exception("Error updating customer balance: " . mysqli_error($conn));
        }
        
        // Get updated credit balance
        $getNewBalanceQuery = "SELECT credit_balance FROM customers WHERE id = '$customerId'";
        $newBalanceResult = mysqli_query($conn, $getNewBalanceQuery);
        $newCreditBalance = 0;
        
        if ($newBalanceResult && $newCustomerData = mysqli_fetch_assoc($newBalanceResult)) {
            $newCreditBalance = $newCustomerData['credit_balance'];
        }

        // If we got here, both operations succeeded, so commit the transaction
        mysqli_commit($conn);
        
        // Store payment details in session for receipt
        $_SESSION['payment_details'] = [
            'payment_id' => $paymentId,
            'customer_id' => $customerId,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'cheque_number' => $chequeNumber,
            'reference' => $reference,
            'invoice_number' => $invoiceNumber,
            'payment_date' => date('Y-m-d H:i:s'),
            'previous_balance' => $previousCreditBalance,
            'new_balance' => $newCreditBalance
        ];
        
        // Redirect to receipt page
        header("Location: payment_receipt.php?payment_id=" . $paymentId);
        exit;
        
    } catch (Exception $e) {
        // An error occurred, rollback the transaction
        mysqli_rollback($conn);
        
        $_SESSION['payment_error'] = $e->getMessage();
        header("Location: payment.php?id=" . urlencode($customerId));
        exit;
    }
} else {
    // If accessed directly without POST data, redirect to customer list
    header("Location: customer_list.php");
    exit;
}
?>
