<?php
// Fetch the advance bill details from the database
require_once '../../../config/databade.php';

// Helper function to format numbers to two decimal points
function formatAmount($amount) {
    return number_format((float)$amount, 2, '.', ',');
}

// Get bill number from query parameter
$advanceBillNumber = isset($_GET['advance_bill_number']) ? $_GET['advance_bill_number'] : '';

// Validate the bill number
if (!$advanceBillNumber) {
    echo "<div style='color:red; padding:20px; text-align:center;'>Error: Advance bill number is required.</div>";
    exit;
}

// Fetch bill details
$stmt = $conn->prepare("
    SELECT ap.customer_name, ap.reason, ap.net_amount, ap.payment_type, 
           DATE(ap.created_at) as bill_date, TIME(ap.created_at) as bill_time,
           c.telephone, c.nic
    FROM advance_payments ap
    LEFT JOIN customers c ON ap.customer_id = c.id
    WHERE ap.advance_bill_number = ?
");

$stmt->bind_param('s', $advanceBillNumber);
$stmt->execute();
$result = $stmt->get_result();
$billDetails = $result->fetch_assoc();

if (!$billDetails) {
    echo "<div style='color:red; padding:20px; text-align:center;'>Error: Bill not found.</div>";
    exit;
}

// Extract bill details
$customerName = $billDetails['customer_name'];
$customerPhone = $billDetails['telephone'] ?? 'N/A';
$customerNIC = $billDetails['nic'] ?? 'N/A';
$reason = $billDetails['reason'] ?? '';
$amount = $billDetails['net_amount'];
$paymentType = ucfirst($billDetails['payment_type']);
$billDate = date('Y-m-d', strtotime($billDetails['bill_date']));
$billTime = date('h:i A', strtotime($billDetails['bill_time']));

// Company info
$companyName = "RISI RASA PRODUCTS";
$companyAddress = "45 Main Street, Colombo";
$companyPhone = "075 1234567";
$companyEmail = "info@risirasa.lk";

// Logo path (if exists)
$logoPath = "../../assets/images/logo.png";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advance Payment Receipt</title>
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
        .detail-section {
            margin: 15px 0;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            padding: 8px 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin-bottom: 5px;
        }
        .detail-label {
            font-weight: bold;
            width: 120px;
        }
        .total-section {
            font-size: 14px;
            text-align: right;
            margin: 10px 0;
            font-weight: bold;
        }
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 9px;
        }
        .thank-you {
            margin: 10px 0 5px;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
        }
        .separator {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        @media print {
            body {
                width: 80mm;
                margin: 0;
                padding: 0;
                background-color: white;
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
            <?php if (file_exists($logoPath)): ?>
            <img src="<?php echo $logoPath; ?>" alt="Company Logo" class="logo">
            <?php endif; ?>
            <div class="company-name"><?php echo $companyName; ?></div>
            <div class="company-info"><?php echo $companyAddress; ?></div>
            <div class="company-info">Tel: <?php echo $companyPhone; ?> | Email: <?php echo $companyEmail; ?></div>
        </div>
        
        <div class="title">ADVANCE PAYMENT RECEIPT</div>
        
        <!-- Bill Info -->
        <div class="bill-info">
            <table>
                <tr>
                    <td>Receipt No:</td>
                    <td><?php echo $advanceBillNumber; ?></td>
                </tr>
                <tr>
                    <td>Date:</td>
                    <td><?php echo $billDate; ?> <?php echo $billTime; ?></td>
                </tr>
                <tr>
                    <td>Customer:</td>
                    <td><?php echo $customerName; ?></td>
                </tr>
                <tr>
                    <td>Phone:</td>
                    <td><?php echo $customerPhone; ?></td>
                </tr>
                <tr>
                    <td>NIC:</td>
                    <td><?php echo $customerNIC; ?></td>
                </tr>
            </table>
        </div>
        
        <div class="separator"></div>
        
        <!-- Payment Details -->
        <div class="detail-section">
            <?php if ($reason): ?>
            <div class="detail-row">
                <div class="detail-label">Reason:</div>
                <div><?php echo $reason; ?></div>
            </div>
            <?php endif; ?>
            <div class="detail-row">
                <div class="detail-label">Payment Type:</div>
                <div><?php echo $paymentType; ?></div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Amount:</div>
                <div>Rs. <?php echo formatAmount($amount); ?></div>
            </div>
        </div>
        
        <!-- Total Section -->
        <div class="total-section">
            <div>Total: Rs. <?php echo formatAmount($amount); ?></div>
        </div>
        
        <div class="separator"></div>
        
        <!-- Footer -->
        <div class="thank-you">Thank You!</div>
        <div class="footer">
            This is a computer generated receipt and does not require a signature.
        </div>
    </div>
</body>
</html>
