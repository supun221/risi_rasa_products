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

// Get return bill number from query parameter
$returnBillNumber = isset($_GET['return_bill']) ? $_GET['return_bill'] : '';

// Validate the return bill number
if (!$returnBillNumber) {
    echo "<div style='color:red; padding:20px; text-align:center;'>Error: Return bill number is required.</div>";
    exit;
}

try {
    // Fetch return details
    $stmt = $conn->prepare("
        SELECT r.*, 
               CONCAT(s.username) as rep_name 
        FROM return_collections r
        LEFT JOIN signup s ON r.rep_id = s.id
        WHERE r.return_bill_number = ?
    ");
    
    $stmt->bind_param('s', $returnBillNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $return = $result->fetch_assoc();

    if (!$return) {
        echo "<div style='color:red; padding:20px; text-align:center;'>Error: Return record not found.</div>";
        exit;
    }

    // Fetch return items
    $stmt = $conn->prepare("
        SELECT * FROM return_collection_items WHERE return_id = ?
    ");
    $stmt->bind_param('i', $return['id']);
    $stmt->execute();
    $items_result = $stmt->get_result();
    $items = [];
    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }

    // Get original invoice info
    $stmt = $conn->prepare("
        SELECT * FROM pos_sales WHERE invoice_number = ?
    ");
    $stmt->bind_param('s', $return['original_invoice_number']);
    $stmt->execute();
    $invoice_result = $stmt->get_result();
    $original_invoice = $invoice_result->fetch_assoc();

    // Company info
    $companyName = "RISI RASA PRODUCTS";
    $companyAddress = "45 Main Street, Colombo";
    $companyPhone = "075 1234567";
    $companyEmail = "info@risirasa.lk";

    // Format return date
    $returnDate = date('Y-m-d', strtotime($return['created_at']));
    $returnTime = date('h:i A', strtotime($return['created_at']));

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
    <title>Return Receipt #<?php echo $returnBillNumber; ?></title>
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
            color: #cc0000; /* Red color to distinguish from regular invoice */
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
            color: #cc0000; /* Red color for return total */
        }
        .reason-section {
            margin-top: 10px;
            font-size: 10px;
            border: 1px dotted #ccc;
            padding: 5px;
        }
        .reason-title {
            font-weight: bold;
            margin-bottom: 3px;
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
            <div class="company-name"><?php echo $companyName; ?></div>
            <div class="company-info"><?php echo $companyAddress; ?></div>
            <div class="company-info">Tel: <?php echo $companyPhone; ?></div>
            <div class="company-info">Email: <?php echo $companyEmail; ?></div>
        </div>
        
        <div class="title">RETURN RECEIPT</div>
        
        <!-- Return Info -->
        <div class="bill-info">
            <table>
                <tr>
                    <td>Return #:</td>
                    <td><?php echo $returnBillNumber; ?></td>
                </tr>
                <tr>
                    <td>Date:</td>
                    <td><?php echo $returnDate; ?> <?php echo $returnTime; ?></td>
                </tr>
                <tr>
                    <td>Original Invoice:</td>
                    <td><?php echo $return['original_invoice_number']; ?></td>
                </tr>
                <tr>
                    <td>Customer:</td>
                    <td><?php echo $return['customer_name'] ?: 'Walk-in Customer'; ?></td>
                </tr>
                <tr>
                    <td>Sales Rep:</td>
                    <td><?php echo $return['rep_name'] ?: 'Unknown'; ?></td>
                </tr>
            </table>
        </div>
        
        <div class="separator"></div>
        
        <!-- Items Section -->
        <table class="items-table">
            <thead>
                <tr>
                    <th width="40%">Item</th>
                    <th width="15%">Return Qty</th>
                    <th width="20%">Unit Price</th>
                    <th width="25%">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo $item['product_name']; ?></td>
                    <td><?php echo $item['return_qty']; ?></td>
                    <td class="amount">Rs.<?php echo formatAmount($item['unit_price']); ?></td>
                    <td class="amount">Rs.<?php echo formatAmount($item['return_amount']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="separator"></div>
        
        <!-- Total Section -->
        <div class="total-section">
            <div class="total-row final">
                <div>Total Return Amount:</div>
                <div>Rs.<?php echo formatAmount($return['total_amount']); ?></div>
            </div>
        </div>
        
        <!-- Return Reason Section -->
        <div class="reason-section">
            <div class="reason-title">Return Reason:</div>
            <div><?php echo htmlspecialchars($return['reason']); ?></div>
            <?php if (!empty($return['notes'])): ?>
            <div class="reason-title" style="margin-top: 5px;">Notes:</div>
            <div><?php echo htmlspecialchars($return['notes']); ?></div>
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