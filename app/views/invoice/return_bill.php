<?php

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get the payload from the request
$inputData = file_get_contents('php://input');
$returnData = json_decode($inputData, true);

if (!$returnData) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input data.']);
    exit;
}

// Extract data from the payload
$returnId = $returnData['returnId'] ?? '';
$currentDate = $returnData['currentDate'] ?? date("Y-m-d H:i:s");
$productList = $returnData['productList'] ?? [];
$totalAmount = $returnData['totalAmount'] ?? 0;
$supplierName = $returnData['supplierName'] ?? 'Unknown Supplier';

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
    <title>Return Bill</title>
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
        .summary td {
            font-size: 12px;
            padding: 5px 10px; 
            text-align: right;
        }
        .summary td:first-child {
            text-align: left; 
        }
        .total-box {
            text-align:center;
            margin: 0 auto;
            border: 2px solid black;
            font-size: 20px !important;
            padding:8px 20px;
            margin-top:10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="<?= $imagePath; ?>" alt="Logo">
        <h2>Return Invoice</h2>
    </div>
    <!-- <div class="highlight">Return Invoice</div> -->
    <table>
        <tr>
            <td style="text-align: left;">Date: <?= $currentDate; ?></td>
            <td style="text-align: right;">Return ID: <?= $returnId; ?></td>
        </tr>
        <tr>
            <td style="text-align: left;">Supplier: <?= htmlspecialchars($supplierName); ?></td>
        </tr>
    </table>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Item & Cost</th>
                <th>QTY</th>
                <th>Cost</th>
                <th>MRP</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php $count = 1; $itemCount = 0; ?>
            <?php foreach ($productList as $product): ?>
                <tr>
                    <td><?= $count; ?></td>
                    <td><?= htmlspecialchars(strtoupper($product['productName'])); ?></td>
                    <td><?= htmlspecialchars($product['quantity']); ?></td>
                    <td><?= formatToTwoDecimalPoints($product['unitPrice']); ?></td>
                    <td><?= formatToTwoDecimalPoints($product['MRPprice']); ?></td>
                    <td><?= formatToTwoDecimalPoints($product['subtotal']); ?></td>
                </tr>
                <?php $count++; $itemCount += $product['quantity']; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
    <table class="summary">
        <tr>
            <td>Total Items</td>
            <td><?= count($productList); ?></td>
        </tr>
        <tr>
            <td>Total Quantity</td>
            <td><?= $itemCount; ?></td>
        </tr>
        <tr>
            <td>Total Amount</td>
            <td><?= formatToTwoDecimalPoints($totalAmount); ?></td>
        </tr>
    </table>
    <div class="total-box">
        <span><b>Total Amount</b> <br><?= formatToTwoDecimalPoints($totalAmount); ?></span>
    </div>
    <div class="footer">
        <p>Thank You Come Again!</p>
    </div>
    <script>
        window.print();
    </script>
</body>
</html>
