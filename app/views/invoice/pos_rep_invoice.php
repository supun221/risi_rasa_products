<?php
// Connect to database
require_once '../../../config/databade.php';

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
$logoPath = "images/ameena_logo.png";
$logoBase64 = getBase64Image($logoPath);

// Get invoice number from query parameter
$invoiceNumber = isset($_GET['invoice']) ? $_GET['invoice'] : '';

// Validate the invoice number
if (!$invoiceNumber) {
    echo "<div style='color:red; padding:20px; text-align:center;'>Error: Invoice number is required.</div>";
    exit;
}

try {
    // Check if cheque_number column exists in pos_sales table
    $checkColumnQuery = "SHOW COLUMNS FROM pos_sales LIKE 'cheque_number'";
    $checkColumnResult = $conn->query($checkColumnQuery);
    $hasChequeNumberColumn = $checkColumnResult->num_rows > 0;
    
    // If column doesn't exist, add it
    if (!$hasChequeNumberColumn) {
        $alterTableQuery = "ALTER TABLE pos_sales ADD COLUMN cheque_number VARCHAR(50) DEFAULT NULL AFTER payment_method";
        try {
            $conn->query($alterTableQuery);
            error_log("Added cheque_number column to pos_sales table");
        } catch (Exception $e) {
            error_log("Failed to add cheque_number column: " . $e->getMessage());
        }
    }

    // Fetch invoice details with cheque_number field
    $stmt = $conn->prepare("
        SELECT ps.*, 
               CONCAT(s.username) as rep_name 
        FROM pos_sales ps
        LEFT JOIN signup s ON ps.rep_id = s.id
        WHERE ps.invoice_number = ?
    ");
    $stmt->bind_param('s', $invoiceNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $invoice = $result->fetch_assoc();

    if (!$invoice) {
        echo "<div style='color:red; padding:20px; text-align:center;'>Error: Invoice not found.</div>";
        exit;
    }

    // Debug - log raw payment method value
    error_log("Invoice payment method: " . ($invoice['payment_method'] ?? 'NULL'));
    if ($hasChequeNumberColumn) {
        error_log("Invoice cheque number: " . ($invoice['cheque_number'] ?? 'NULL'));
    }

    // Fetch invoice items
    $stmt = $conn->prepare("
        SELECT * FROM pos_sale_items WHERE sale_id = ?
    ");
    $stmt->bind_param('i', $invoice['id']);
    $stmt->execute();
    $items_result = $stmt->get_result();
    $items = [];
    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }

    // Company info
    $companyName = "RISI RASA PRODUCTS";
    $companyAddress = "45 Main Street, Colombo";
    $companyPhone = "075 1234567";
    $companyEmail = "info@risirasa.lk";

    // Format invoice date
    $invoiceDate = date('Y-m-d', strtotime($invoice['sale_date']));
    $invoiceTime = date('h:i A', strtotime($invoice['sale_date']));

    // Payment method display text - ensure it's case insensitive and has a fallback
    $paymentMethodMap = [
        'cash' => 'Cash',
        'card' => 'Card',
        'credit_card' => 'Credit Card',
        'credit' => 'Credit',
        'advance' => 'Advance',
        'cheque' => 'Cheque'
    ];

    // Get payment method and ensure it's not null (use lowercase for comparison)
    $paymentMethod = strtolower($invoice['payment_method'] ?? '');
    
    // Set default text if payment method is empty
    if (empty($paymentMethod)) {
        $paymentMethodText = 'Unknown';
    } else {
        // Get the display text from map or use the original value
        $paymentMethodText = $paymentMethodMap[$paymentMethod] ?? ucfirst($paymentMethod);
    }

    // Add cheque number text if payment method is cheque
    if ($paymentMethod == 'cheque') {
        if ($hasChequeNumberColumn && !empty($invoice['cheque_number'])) {
            $paymentMethodText .= ' #' . $invoice['cheque_number'];
        } else {
            // Try to get cheque number from rep_payments table as fallback
            $paymentStmt = $conn->prepare("
                SELECT cheque_num FROM rep_payments 
                WHERE invoice_number = ? AND payment_method = 'cheque'
                LIMIT 1
            ");
            $paymentStmt->bind_param('s', $invoiceNumber);
            $paymentStmt->execute();
            $paymentResult = $paymentStmt->get_result();
            if ($paymentResult && $paymentRow = $paymentResult->fetch_assoc()) {
                if (!empty($paymentRow['cheque_num'])) {
                    $paymentMethodText .= ' #' . $paymentRow['cheque_num'];
                }
            }
        }
    }
} catch (Exception $e) {
    echo "<div style='color:red; padding:20px; text-align:center;'>Error: " . $e->getMessage() . "</div>";
    exit;
}
?>          
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo $invoiceNumber; ?></title>
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
            text-align: left;
            margin-bottom: 10px;
            display: flex;
            flex-direction: row;
            align-items: center;
        }
        .logo {
            max-width: 30mm;
            height: auto;
            margin-right: 5px;
        }
        .company-details {
            display: inline-block;
            text-align: left;
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
        .items-table {
            width: 100%;
            font-size: 9px;
            border-collapse: collapse;
        }
        .items-table th {
            text-align: left;
            padding: 3px 0;
            border-bottom: 1px solid #ddd;
        }
        .items-table td {
            padding: 3px 0;
            border-bottom: 1px dotted #eee;
        }
        .items-table .amount {
            text-align: right;
        }
        .total-section {
            margin-top: 10px;
            font-size: 10px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .total-row.final {
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
        @media print {
            body {
                width: 80mm;
                margin: 0;
                padding: 0;
            }
            .receipt {
                width: 76mm;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="receipt">
        <!-- Header Section -->
        <div class="header">
            <?php if ($logoBase64): ?>
                <img src="<?= $logoBase64 ?>" alt="RisiRasa Logo" class="logo">
            <?php endif; ?>
            <div class="company-details">
                <div class="company-name"><?php echo $companyName; ?></div>
                <div class="company-info"><?php echo $companyAddress; ?></div>
                <div class="company-info">Tel: <?php echo $companyPhone; ?></div>
                <div class="company-info">Email: <?php echo $companyEmail; ?></div>
            </div>
        </div>
        
        <div class="title">SALES INVOICE</div>
        
        <!-- Invoice Info -->
        <div class="bill-info">
            <table>
                <tr>
                    <td>Invoice #:</td>
                    <td><?php echo $invoiceNumber; ?></td>
                </tr>
                <tr>
                    <td>Date:</td>
                    <td><?php echo $invoiceDate; ?> <?php echo $invoiceTime; ?></td>
                </tr>
                <tr>
                    <td>Customer:</td>
                    <td><?php echo $invoice['customer_name'] ?: 'Walk-in Customer'; ?></td>
                </tr>
                <tr>
                    <td>Sales Rep:</td>
                    <td><?php echo $invoice['rep_name'] ?: 'Unknown'; ?></td>
                </tr>
                <tr>
                    <td>Payment:</td>
                    <td><?php echo htmlspecialchars($paymentMethodText); ?></td>
                </tr>
            </table>
        </div>
        
        <div class="separator"></div>
        
        <!-- Items Section -->
        <table class="items-table">
            <thead>
                <tr>
                    <th width="45%">Item</th>
                    <th width="10%">Qty</th>
                    <th width="20%">Price</th>
                    <th width="25%">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo $item['product_name']; ?></td>
                    <td><?php echo $item['quantity'] + $item['free_quantity']; ?><?php if($item['free_quantity'] > 0) echo ' (' . $item['free_quantity'] . ' free)'; ?></td>
                    <td class="amount">Rs.<?php echo formatAmount($item['unit_price']); ?></td>
                    <td class="amount">Rs.<?php echo formatAmount($item['subtotal']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="separator"></div>
        
        <!-- Total Section -->
        <div class="total-section">
            <div class="total-row">
                <div>Subtotal:</div>
                <div>Rs.<?php echo formatAmount($invoice['total_amount']); ?></div>
            </div>
            <?php if ($invoice['discount_amount'] > 0): ?>
            <div class="total-row">
                <div>Discount:</div>
                <div>Rs.<?php echo formatAmount($invoice['discount_amount']); ?></div>
            </div>
            <?php endif; ?>
            <div class="total-row final">
                <div>Total:</div>
                <div>Rs.<?php echo formatAmount($invoice['net_amount']); ?></div>
            </div>
            <?php if ($invoice['advance_used'] > 0): ?>
            <div class="total-row">
                <div>Advance Used:</div>
                <div>Rs.<?php echo formatAmount($invoice['advance_used']); ?></div>
            </div>
            <?php endif; ?>
            <?php if ($invoice['payment_method'] != 'credit'): ?>
            <div class="total-row">
                <div>Amount Paid:</div>
                <div>Rs.<?php echo formatAmount($invoice['paid_amount']); ?></div>
            </div>
            <?php if ($invoice['change_amount'] > 0): ?>
            <div class="total-row">
                <div>Change:</div>
                <div>Rs.<?php echo formatAmount($invoice['change_amount']); ?></div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
            <?php if ($invoice['credit_amount'] > 0): ?>
            <div class="total-row">
                <div>Credit Amount:</div>
                <div>Rs.<?php echo formatAmount($invoice['credit_amount']); ?></div>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="thankyou">Thank You!</div>
        <div class="footer">
            <p>This is a computer generated receipt.</p>
            <p>No signature required.</p>
        </div>
    </div>
</body>
</html>
