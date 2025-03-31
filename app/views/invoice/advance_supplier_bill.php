<?php

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the bill number is passed via GET or POST
$advanceBillNumber = $_GET['advance_bill_number'] ?? '';
if (!$advanceBillNumber) {
    $inputData = file_get_contents('php://input');
    $billData = json_decode($inputData, true);
    $advanceBillNumber = $billData['advance_bill_number'] ?? '';
}

// Validate the advance bill number
if (!$advanceBillNumber) {
    http_response_code(400);
    echo json_encode(['error' => 'Bill number is required.']);
    exit;
}

// Fetch the advance bill details from the database
require_once '../../../config/databade.php';

$stmt = $conn->prepare("SELECT supplier_name, reason, net_amount, DATE(created_at) as bill_date FROM advance_payments_supplier WHERE advance_bill_number = ?");
$stmt->bind_param('s', $advanceBillNumber);
$stmt->execute();
$result = $stmt->get_result();
$billDetails = $result->fetch_assoc();

if (!$billDetails) {
    http_response_code(404);
    echo json_encode(['error' => 'Bill not found.']);
    exit;
}

// Extract details
$supplierName = $billDetails['supplier_name'];
$reason = $billDetails['reason'];
$price = $billDetails['net_amount'];
$currentDate = date('Y-m-d');
$billDate = $billDetails['bill_date'];

$imagePath = "https://egg.land.nexarasolutions.site/app/views/invoice/images/bill-header.png";

function formatToTwoDecimalPoints($number) {
    return number_format($number, 2, '.', '');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advance Bill</title>
    <style>
        * {
            font-family: Arial, sans-serif;
            font-size: 11px;
            text-align: center;
        }
        body {
            width: 80mm;
            margin: 0 auto;
        }
        .header img {
            max-width: 300px;
            max-height: 260px;
        }
        .highlight {
            background-color: black; 
            color: white; 
            font-size: 14px;
            font-weight: bold;
            padding: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        th, td {
            font-size: 11px;
            padding: 2px;
            border-bottom: 1px dashed black;
        }
        .total-box {
            text-align:center;
            margin: 0 auto;
            border: 2px solid black;
            font-size: 20px !important;
            padding:8px 20px;
            margin-top:10px;
        }
        .thank-text {
            font-size: 1em;
            text-transform: uppercase;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="<?= $imagePath; ?>" alt="Logo">
        <h2>Advance Invoice</h2>
    </div>
    <!-- <div class="highlight">Advance Invoice</div> -->
    <table>
        <tr>
            <td style="text-align: left;">Date: <?= $currentDate; ?></td>
            <td style="text-align: right;">Advance Bill: <?= htmlspecialchars($advanceBillNumber); ?></td>
        </tr>
        <tr>
            <td style="text-align: left;">Supplier: <?= htmlspecialchars($supplierName); ?></td>
        </tr>
    </table>
    <table>
        <thead>
            <tr>
                <th>Reason</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= htmlspecialchars($reason); ?></td>
                <td><?= formatToTwoDecimalPoints($price); ?></td>
            </tr>
        </tbody>
    </table>
    <div class="total-box">
        <span><b>Total</b> <br><?= formatToTwoDecimalPoints($price); ?></span>
    </div>
    <div class="footer">
    <p class="thank-text">-----------------------------------</p>
    <p class="thank-text">Thank you, come again!</p>
    <p class="thank-text">-----------------------------------</p>
</div>
    <script>
        window.print();
    </script>
</body>
</html>
