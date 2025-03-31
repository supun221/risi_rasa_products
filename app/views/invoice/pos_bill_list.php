<?php
// pos_bill_list.php
require_once '../../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get the payload from the request
$inputData = file_get_contents('php://input');
$billData = json_decode($inputData, true);

if (!$billData) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input data.']);
    exit;
}

// Extract data from the payload
$billId = $billData['bill_id'] ?? 'Unknown';
$currentDate = $billData['bill_date'] ?? date('Y-m-d');
$productList = $billData['productList'] ?? [];
$grossAmount = $billData['gross_amount'] ?? 0;
$customerID = $billData['customer_id'] ?? 'Unknown';
$cashTendered = $billData['cashTendered'] ?? 0;
$netAmount = $billData['net_amount'] ?? 0;
$discountAmount = $billData['discount_amount'] ?? 0;
$paymentType = $billData['payment_type'] ?? 'Unknown';
$balance = $billData['balance'] ?? 0;

// Ensure image file exists
$imagePath = realpath(__DIR__ . '/images/bill-header.png');
if (!$imagePath || !file_exists($imagePath)) {
    http_response_code(404);
    echo json_encode(['error' => 'Header image not found.']);
    exit;
}

// Utility function to format numbers to two decimal points
function formatToTwoDecimalPoints($number)
{
    return number_format((float)$number, 2, '.', '');
}

// Prepare the HTML for the POS bill
$billHtml = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Bill</title>
    <style>
     * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: sans-serif;
            letter-spacing: 0px;
        }
        body {
            width: 100vw;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: flex-start;
        }
        .header {
                   width: 80mm;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align:center;
        }
        .pos-logo {
           width: 80mm;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
            
        thead > tr > th:nth-child(1) {
            width: 10px;
        }
       thead > tr > th {
            padding-bottom: 4px
        }

        thead > tr  {
            border-top: 2px solid black;
            font-size: 0.6em;
            font-weight: 400;
            text-transform: uppercase;
        }
        
        tbody > tr:nth-child(1){
            border-top: 2px solid black;
        }

        tbody > tr:nth-last-child(1){
            border-bottom: 2px solid black;
        }

        tbody > tr:nth-last-child(1) > td{
            padding-bottom: 3px;
        }

        tbody > tr > td {
            font-size: 0.7em;
            text-align: center;
        }

        .thank-text {
            font-size: 1em;
            text-align: center;
            text-transform: uppercase;
        }

        .total-box {
            width: 230px;
            text-align:center;
            margin: 0 auto;
            border: 2px solid black;
            margin-bottom: 10px;
             line-height: 16px;
            font-size: 1.6em
            padding:10px 20px
        }
    </style>
</head>
<body>

    <img src="data:image/png;base64,' . base64_encode(file_get_contents($imagePath)) . '" class="pos-logo" alt="Logo" />
  
<table style="margin:0;">
   <tr style="border: none;">
        <td style="width:20mm; text-align:left; padding-left: 4px;"><strong>Date:</strong> ' . htmlspecialchars($currentDate) . '</td>
         <td style="width:20mm; text-align:right; padding-right: 4px;"><strong>Bill ID:</strong> ' . htmlspecialchars($billId) . '</td>
    </tr style="border: none;">
   <tr style="border: none;">
        <td style="width:20mm; text-align:left; padding-left: 4px;"><strong>Customer:</strong> ' . htmlspecialchars($customerID) . '</td>
         <td style="width:20mm; text-align:right; padding-right: 4px;"><strong>Payment:</strong> ' . htmlspecialchars($paymentType) . '</td>
    </tr style="border: none;">
</table>

<table class="table">
    <thead>
        <tr>
            <th>#</th>
              <th style="width:40mm">Item & M.Retail Price</th>
              <th>Qty</th>
            <th>MRP</th>
            
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>';

$count = 1;
$totalQty = 0;
foreach ($productList as $product) {
    $billHtml .= '
        <tr>
            <td>' . $count . '</td>
            <td colspan="4" style="text-align: left; padding-left:20px">' . htmlspecialchars($product['product_name'] ?? 'Unknown') . '</td>
        </tr>
        <tr> 
          <td>   </td>   
            <td style="font-weight: 600;">' . formatToTwoDecimalPoints($product['product_mrp'] ?? 0) . '</td>
            <td> x' . htmlspecialchars($product['purchase_qty'] ?? 0) . '</td>
            <td style="font-weight: 600;">' . formatToTwoDecimalPoints($product['product_mrp'] ?? 0) . '</td>
            <td>' . formatToTwoDecimalPoints($product['subtotal'] ?? 0) . '</td>
        </tr>';
    $count++;
    $totalQty += $product['purchase_qty'] ?? 0;
}

$billHtml .= '
    </tbody>
</table>
<table style="margin:0;">
    <tr style="border: none;">
        <td style="width:40mm; text-align:left; padding-left: 4px;"> No of Items: ' . count($productList) . '</td>
         <td style="width:30mm; text-align:right; padding-right: 4px;"> No of Qty: ' . $totalQty . '</td>
     </tr style="border: none;">
</table>
<br>     
 <table style="font-size: 1.2em; font-weight:500; transform: translateY(-10px);">
     <tr style="border: none;">
         <td style="width:40mm; text-align:left; padding-left: 20px;">Gross Amount</td>
        <td style="width:30mm; text-align:right; padding-right: 20px;">' . formatToTwoDecimalPoints($grossAmount) . '</td>
    </tr style="border: none;">
    <tr style="border: none;">
       <td style="width:40mm; text-align:left; padding-left: 20px;">Discount</td>
       <td style="width:30mm; text-align:right; padding-right: 20px;">'  . formatToTwoDecimalPoints($discountAmount) . '</td>
    </tr style="border: none;">
     <tr style="border: none;">
        <td style="width:40mm; text-align:left; padding-left: 20px;">Net Amount</td>
       <td style="width:30mm; text-align:right; padding-right: 20px;">'  . formatToTwoDecimalPoints($netAmount) . '</td>
    </tr style="border: none;">
     <tr style="border: none;">
         <td style="width:40mm; text-align:left; padding-left: 20px;">Paid Amount</td>
       <td style="width:30mm; text-align:right; padding-right: 20px;">'  . formatToTwoDecimalPoints($cashTendered) . '</td>
    </tr style="border: none;">
     <tr style="border: none;">
         <td style="width:40mm; text-align:left; padding-left: 20px;">Balance</td>
        <td style="width:30mm; text-align:right; padding-right: 20px;">'  . formatToTwoDecimalPoints($balance) . '</td>
    </tr style="border: none;">
</table>
<p style="text-align:center; font-size: .8em;">Payment Type: ' . str_replace("_"," ", strtoupper(htmlspecialchars($paymentType))) . '</p>

<div class="total-box">
    <span> <b>Total Net Amount</b> </br> '.formatToTwoDecimalPoints(htmlspecialchars($netAmount)).' </span>
</div>
<div class="footer">
    <p class="thank-text">-----------------------------------</p>
    <p class="thank-text">Thank you come again!</p>
    <p class="thank-text">-----------------------------------</p>
</div>
</body>
</html>';

// Generate the PDF
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($billHtml);
$dompdf->setPaper([0, 0, 226.8, 650]); // 80mm width
$dompdf->render();

// Set headers and output the PDF
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="pos_bill.pdf"');
echo $dompdf->output();

?>
