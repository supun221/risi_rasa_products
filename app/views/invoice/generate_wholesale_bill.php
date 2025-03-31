<?php

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
$billId = $billData['billId'] ?? '';
$currentDate = $billData['currentDate'] ?? '';
$productList = $billData['productList'] ?? [];
$grossAmount = $billData['grossAmount'] ?? 0;
$customerID = $billData['customerID'] ?? 0;
$cashTendered = $billData['cashTendered'] ?? 0;
$netAmount = $billData['netAmount'] ?? 0;
$discountAmount = $billData['discountAmount'] ?? 0;
$paymentType = $billData['paymentType'] ?? '';
$balance = $billData['balance'] ?? '';
$billDate = $billData['billDate'] ?? $currentDate;
$freeIssuesFlag = false;


$imagePath = realpath(__DIR__ . '/images/bill-header.png');
if (!$imagePath || !file_exists($imagePath)) {
    echo "Image not found!";
    exit;
}

// Prepare the HTML for the POS bill
$billHtml = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eggland | POS Bill</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "sans-serif;
            letter-spacing: 0px;
            font-family: sans-serif;
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
            margin-top: 10px;
            width: 100%;
            border-collapse: collapse;
        }

        fr-issue-table {
            margin-top: 10px;
            width: 100%;
            border-collapse: collapse;
            border:none;
        }

        fr-issue-table > tr {
            border"none
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
<img src="' . $imagePath . '" class="pos-logo"/>

<table style="margin:0;">
    <tr style="border: none;">
        <td style="width:20mm; text-align:left; padding-left: 4px;"> <b>Date:</b> ' . $currentDate . '</td>
        <td style="width:20mm; text-align:right; padding-right: 4px;"> <b>Bill ID:</b> ' . $billId . '</td>
    </tr style="border: none;">
    <tr style="border: none;">
        <td style="width:20mm; text-align:left; padding-left: 4px;"> <b>Cashier: </b> Unknown</td>
        <td style="width:20mm; text-align:right; padding-right: 4px;"> <b>Customer:</b> ' . $customerID . '</td>
    </tr style="border: none;">
</table>


<table class="table">
    <thead>
        <tr>
            <th>No</th>
            <th style="width:40mm">Wholesale Price</th>
            <th>QTY</th>
            <th>Our Price</th>
            <th>Amount</th>
        </tr>
    </thead>

    <tbody>';

    function formatToTwoDecimalPoints($number) {
        return number_format($number, 2, '.', '');
    }

    $count = 1;
    $itemCount = 0;
    foreach ($productList as $product) {
        if($product['freeIssueAmount'] > 0){
            $freeIssuesFlag = true;
        }
        $billHtml .= '
        <tr>
            <td>' . htmlspecialchars($count) . '</td>
            <td colspan="4" style="text-align: left; padding-left:20px">' . htmlspecialchars(strtoupper($product['productName'])) . '' . htmlspecialchars(formatToTwoDecimalPoints($product['unitPrice'])) . '/=' . '</td>
        </tr>
        <tr>
            <td>   </td>
            <td style="font-weight: 600;">' . htmlspecialchars(formatToTwoDecimalPoints($product['unitPrice'])) . '</td>
            <td> x' . htmlspecialchars($product['quantity']) . '</td>
            <td style="font-weight: 600;">' . htmlspecialchars(formatToTwoDecimalPoints($product['ourPrice'])) . '</td>
            <td>' . htmlspecialchars(formatToTwoDecimalPoints($product['subtotal'])) . '</td>
        </tr>';
        $count += 1;
        $itemCount += $product['quantity'];
    }

$billHtml .= '


    </tbody>
</table>

<table style="margin:0;">
    <tr style="border: none;">
        <td style="width:40mm; text-align:left; padding-left: 4px;"> No of Items: ' . count($productList) . '</td>
        <td style="width:30mm; text-align:right; padding-right: 4px;"> No of Qty: ' . $itemCount . '</td>
    </tr style="border: none;">
</table>

<table style="font-size: 1.2em; font-weight:500; transform: translateY(-10px);">
    <tr style="border: none;">
        <td style="width:40mm; text-align:left; padding-left: 20px;">Gross Amount</td>
        <td style="width:30mm; text-align:right; padding-right: 20px;">' . formatToTwoDecimalPoints(htmlspecialchars($grossAmount)) . '</td>
    </tr style="border: none;">
    <tr style="border: none;">
        <td style="width:40mm; text-align:left; padding-left: 20px;">Discount</td>
        <td style="width:30mm; text-align:right; padding-right: 20px;">' . formatToTwoDecimalPoints(htmlspecialchars($discountAmount)) . '</td>
    </tr style="border: none;">
    <tr style="border: none;">
        <td style="width:40mm; text-align:left; padding-left: 20px;">Net Amount</td>
        <td style="width:30mm; text-align:right; padding-right: 20px;">' . formatToTwoDecimalPoints(htmlspecialchars($netAmount)) . '</td>
    </tr style="border: none;">
    <tr style="border: none;">
        <td style="width:40mm; text-align:left; padding-left: 20px;">Paid Amount</td>
        <td style="width:30mm; text-align:right; padding-right: 20px;">' . formatToTwoDecimalPoints(htmlspecialchars($cashTendered)) . '</td>
    </tr style="border: none;">
    <tr style="border: none;">
        <td style="width:40mm; text-align:left; padding-left: 20px;">Balance</td>
        <td style="width:30mm; text-align:right; padding-right: 20px;">' . formatToTwoDecimalPoints(htmlspecialchars($balance)) . '</td>
    </tr style="border: none;">
</table>

<p style="text-align:center; font-size: .8em;">Payment Type: ' . str_replace("_"," ", strtoupper(htmlspecialchars($paymentType))) . '</p>

<div class="total-box">
    <span> <b>Total Net Amount</b> </br> '.formatToTwoDecimalPoints(htmlspecialchars($netAmount)).' </span>
</div>';


if($freeIssuesFlag){
    $billHtml .= '<h3 style="width:100%; text-align:center;">Free Issue</h3>';
    $billHtml .= '<p class="thank-text">----------------------------------------------------------------------</p>';
    $billHtml .= '<table class="fr-issue-table">';
    foreach ($productList as $product) {
        if($product['freeIssueAmount'] > 0){
            $billHtml .= '
            <tr style="border:none; text-align:left;">
                <td style="border:none; text-align:left; padding-left:20px;">' . htmlspecialchars(strtoupper($product['productName'])) . '</td>
                <td style="border:none; text-align:left;"> x' . htmlspecialchars($product['freeIssueAmount']) . '</td>
            </tr style="border:none; text-align:left;">';
        }
    }
    $billHtml .= '</table>';
}


$billHtml .= '<div class="footer">
    <p class="thank-text">-------------------------------------------------------------------------------</p>
    <p class="thank-text">Thank you come again!</p>
    <p class="thank-text">-------------------------------------------------------------------------------</p>
</div>
</body>
</html>';

// Generate the PDF
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$options->set('isImageEnabled', true);
$options->set('chroot', __DIR__);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($billHtml);
$dompdf->setPaper([0, 0, 226.8, 650]);
$dompdf->render();

// Set headers and output the PDF
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="pos_bill.pdf"');
echo $dompdf->output();


?>