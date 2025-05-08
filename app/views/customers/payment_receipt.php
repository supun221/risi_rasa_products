<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../../../config/databade.php'; // Database connection

// Helper function to format numbers to two decimal points
function formatAmount($amount) {
    return number_format((float)$amount, 2, '.', ',');
}

// Function to convert image to base64
function getBase64Image($imagePath) {
    if (file_exists($imagePath)) {
        $imageData = file_get_contents($imagePath);
        return 'data:image/png;base64,' . base64_encode($imageData);
    }
    return false;
}

// Get logo as base64
$logoPath = "../invoice/images/ameena_logo.png";
$logoBase64 = getBase64Image($logoPath);

// Check if payment ID is provided in URL or session
$paymentId = isset($_GET['payment_id']) ? $_GET['payment_id'] : null;

// If no payment ID, check if we have payment details in session
if (!$paymentId && !isset($_SESSION['payment_details'])) {
    echo "<script>alert('Payment information not found.'); window.location.href = 'customer_list.php';</script>";
    exit;
}

// Try to get payment details from database if we have payment ID
if ($paymentId) {
    // Check if cheque_number column exists before querying with it
    $columnCheckQuery = "SHOW COLUMNS FROM customer_payments LIKE 'cheque_number'";
    $columnCheckResult = mysqli_query($conn, $columnCheckQuery);
    $chequeNumberColumnExists = mysqli_num_rows($columnCheckResult) > 0;
    
    // Build the query based on column existence
    $paymentQuery = "SELECT cp.*, c.name, c.telephone, c.address, c.nic 
                    FROM customer_payments cp
                    JOIN customers c ON cp.customer_id = c.id
                    WHERE cp.id = ?";
                    
    $stmt = mysqli_prepare($conn, $paymentQuery);
    mysqli_stmt_bind_param($stmt, "i", $paymentId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        echo "<script>alert('Payment not found.'); window.location.href = 'customer_list.php';</script>";
        exit;
    }
    
    $paymentData = mysqli_fetch_assoc($result);
    
    // If cheque_number field doesn't exist in the query result but should exist
    if (!isset($paymentData['cheque_number']) && $chequeNumberColumnExists) {
        // Get just the cheque number with a separate query
        $chequeQuery = "SELECT cheque_number FROM customer_payments WHERE id = ?";
        $stmt = mysqli_prepare($conn, $chequeQuery);
        mysqli_stmt_bind_param($stmt, "i", $paymentId);
        mysqli_stmt_execute($stmt);
        $chequeResult = mysqli_stmt_get_result($stmt);
        if ($chequeData = mysqli_fetch_assoc($chequeResult)) {
            $paymentData['cheque_number'] = $chequeData['cheque_number'];
        }
    }
    
    // Get previous and new balance by calculating
    $customerQuery = "SELECT credit_balance FROM customers WHERE id = ?";
    $stmt = mysqli_prepare($conn, $customerQuery);
    mysqli_stmt_bind_param($stmt, "i", $paymentData['customer_id']);
    mysqli_stmt_execute($stmt);
    $customerResult = mysqli_stmt_get_result($stmt);
    $customerData = mysqli_fetch_assoc($customerResult);
    
    $currentBalance = $customerData['credit_balance'];
    $previousBalance = $currentBalance + $paymentData['amount'];
} 
// Use session data if available
else if (isset($_SESSION['payment_details'])) {
    $paymentData = $_SESSION['payment_details'];
    
    // Get customer information
    $customerId = $paymentData['customer_id'];
    $customerQuery = "SELECT name, telephone, address, nic FROM customers WHERE id = ?";
    $stmt = mysqli_prepare($conn, $customerQuery);
    mysqli_stmt_bind_param($stmt, "i", $customerId);
    mysqli_stmt_execute($stmt);
    $customerResult = mysqli_stmt_get_result($stmt);
    $customerData = mysqli_fetch_assoc($customerResult);
    
    // Merge customer data with payment data
    $paymentData = array_merge($paymentData, $customerData);
    
    $previousBalance = $paymentData['previous_balance'];
    $currentBalance = $paymentData['new_balance'];
    
    // Clear session data to prevent duplicate receipts
    unset($_SESSION['payment_details']);
}

// Company info
$companyName = "RISI RASA PRODUCTS";
$companyAddress = "45 Main Street, Colombo";
$companyPhone = "075 1234567";
$companyEmail = "info@risirasa.lk";

// Format payment date
$paymentDate = date('Y-m-d', strtotime($paymentData['payment_date']));
$paymentTime = date('h:i A', strtotime($paymentData['payment_date']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt #<?php echo htmlspecialchars($paymentData['invoice_number']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        body {
            width: 80mm;
            max-width: 80mm;
            margin: 0;
            padding: 0;
            background-color: white;
        }
        .receipt {
            width: 76mm;
            padding: 5mm 2mm;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .logo {
            max-width: 60mm;
            height: auto;
            margin: 0 auto 5px;
            display: block;
        }
        .logo12 {
            max-width: 30mm; /* Reduced size for logo */
            height: auto;
            margin: 0 auto 5px;
            display: block;
        }
        .company-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .company-info {
            font-size: 9px;
            margin-bottom: 2px;
            color: #333;
        }
        .title {
            font-size: 12px;
            font-weight: bold;
            margin: 10px 0 5px;
            text-align: center;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 5px 0;
        }
        .bill-info {
            margin: 10px 0;
            font-size: 10px;
        }
        .bill-info table {
            width: 100%;
        }
        .bill-info td {
            padding: 2px 0;
        }
        .bill-info td:first-child {
            font-weight: bold;
            width: 100px;
        }
        .separator {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        .balance-section {
            margin-top: 10px;
            font-size: 10px;
        }
        .balance-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .balance-row.final {
            font-weight: bold;
            font-size: 12px;
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 5px;
        }
        .thankyou {
            margin-top: 15px;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
        }
        .footer {
            margin-top: 10px;
            font-size: 9px;
            text-align: center;
            color: #666;
        }
        .controls {
            position: fixed;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 10px;
        }
        .control-btn {
            padding: 8px 15px;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            font-size: 14px;
            color: white;
        }
        .print-btn {
            background-color: #3498db;
        }
        .back-btn {
            background-color: #7f8c8d;
        }
        @media print {
            body {
                width: 80mm;
                margin: 0;
                padding: 0;
            }
            .receipt {
                width: 76mm;
            }
            .controls {
                display: none;
            }
        }
    </style>
</head>

<body onload="window.print()">
    <div class="controls">
        <button class="control-btn print-btn" onclick="window.print()">
            <i class="fas fa-print"></i> Print
        </button>
        <a href="payment.php?id=<?php echo urlencode($paymentData['customer_id']); ?>" class="control-btn back-btn">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="receipt">
        <!-- Header Section -->
        <div class="header">
            <?php if ($logoBase64): ?>
                <img src="<?= $logoBase64 ?>" alt="RisiRasa Logo" class="logo12">
            <?php endif; ?>
            <div class="company-name"><?php echo $companyName; ?></div>
            <div class="company-info"><?php echo $companyAddress; ?></div>
            <div class="company-info">Tel: <?php echo $companyPhone; ?></div>
            <div class="company-info">Email: <?php echo $companyEmail; ?></div>
        </div>
        
        <div class="title">PAYMENT RECEIPT</div>
        
        <!-- Payment Info -->
        <div class="bill-info">
            <table>
                <tr>
                    <td>Receipt #:</td>
                    <td><?php echo htmlspecialchars($paymentData['invoice_number']); ?></td>
                </tr>
                <tr>
                    <td>Date:</td>
                    <td><?php echo $paymentDate; ?> <?php echo $paymentTime; ?></td>
                </tr>
                <tr>
                    <td>Customer:</td>
                    <td><?php echo htmlspecialchars($paymentData['name']); ?></td>
                </tr>
                <?php if (!empty($paymentData['telephone'])): ?>
                <tr>
                    <td>Telephone:</td>
                    <td><?php echo htmlspecialchars($paymentData['telephone']); ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td>Payment Method:</td>
                    <td>
                        <?php 
                            $methodText = ucfirst(str_replace('_', ' ', $paymentData['payment_method'])); 
                            echo htmlspecialchars($methodText);
                            if ($paymentData['payment_method'] == 'cheque' && !empty($paymentData['cheque_number'])) {
                                echo ' #' . htmlspecialchars($paymentData['cheque_number']);
                            }
                        ?>
                    </td>
                </tr>
                <?php if (!empty($paymentData['reference'])): ?>
                <tr>
                    <td>Reference:</td>
                    <td><?php echo htmlspecialchars($paymentData['reference']); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        
        <div class="separator"></div>
        
        <!-- Balance Section -->
        <div class="balance-section">
            <div class="balance-row">
                <div>Previous Credit Balance:</div>
                <div>Rs.<?php echo formatAmount($previousBalance); ?></div>
            </div>
            <div class="balance-row">
                <div>Amount Paid:</div>
                <div>Rs.<?php echo formatAmount($paymentData['amount']); ?></div>
            </div>
            <div class="balance-row final">
                <div>Current Credit Balance:</div>
                <div>Rs.<?php echo formatAmount($currentBalance); ?></div>
            </div>
        </div>
        
        <div class="thankyou">Thank You For Your Payment!</div>
        
        <div class="footer">
            <p>This receipt was generated on <?php echo date('Y-m-d H:i:s'); ?></p>
            <p>This is a computer-generated receipt and doesn't require a signature.</p>
        </div>
    </div>
</body>
</html>
