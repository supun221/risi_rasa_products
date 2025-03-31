<?php

require_once '../../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

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

$stmt = $conn->prepare("
    SELECT customer_name, reason, net_amount, DATE(created_at) as bill_date
    FROM advance_payments
    WHERE advance_bill_number = ?
");

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
$customerName = $billDetails['customer_name'];
$reason = $billDetails['reason'];
$price = $billDetails['net_amount'];
$currentDate = date('Y-m-d');
$billDate = $billDetails['bill_date'];

// Validate bill header image
$imagePath = realpath(__DIR__ . '/images/image.png');
if (!$imagePath || !file_exists($imagePath)) {
    echo "Image not found!";
    exit;
}

// Prepare the HTML for the bill
$billHtml = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eggland | Advance Bill</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "sans-serif";
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
            text-align: center;
        }
        .pos-logo {
            width: 80mm;
        }
        table {
            margin-top: 10px;
            width: 100%;
            border-collapse: collapse;
        }td {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

        thead > tr > th {
            font-size: 0.8em;
            text-transform: uppercase;
            text-align: left;
            border-bottom: 1px solid black;
            padding-bottom: 5px;
        }
        tbody > tr > td {
            font-size: 0.8em;
            padding: 5px 0;
            margin-left: 8px;
        }
        .total-box {
            width: 230px;
            text-align: center;
            margin: 0 auto;
            border: 2px solid black;
            margin-bottom: 10px;
            line-height: 16px;
            font-size: 1.6em;
            padding: 15px 25px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
        }
        .thank-text {
            font-size: 1em;
            text-transform: uppercase;
            margin: 5px 0;
        }
    </style>
</head>
<body>
<img src="' . $imagePath . '" class="pos-logo"/>

<table>
    <tr>
        <td style="width: 40mm; text-align: left; padding-left: 4px;"><b>Date:</b> ' . htmlspecialchars($currentDate) . '</td>
        <td colspan="4" style="text-align: right; padding-right:14px; white-space: nowrap;">Advance Bill: '. htmlspecialchars($advanceBillNumber) . '</td>

    </tr>
    <tr>
        <td style="width: 40mm; text-align: left; padding-left: 4px;"><b>Customer:</b> ' . htmlspecialchars($customerName) . '</td>
        <td style="width: 40mm; text-align: right; padding-right: 4px;"></td>
    </tr>
</table>

<table class="table">
    <thead>
        <tr>
           <th colspan="4" style="text-align: left; padding-left:10px">Reason</th>
            <th colspan="4" style="text-align: right; padding-right:10px">Amount</th>
        </tr>
    </thead>
    <tbody>
        <tr>
           <td colspan="4" style="text-align: left; padding-left:10px">' . htmlspecialchars($reason) . '</td>
            <td colspan="4" style="text-align: right; padding-right:10px">' . htmlspecialchars(number_format($price, 2, '.', '')) . '</td>
        </tr>
    </tbody>
</table>
<br>
<div class="total-box">
    <span><b>Total</b><br><br>' . htmlspecialchars(number_format($price, 2, '.', '')) . '</span>
</div>

<div class="footer">
    <p class="thank-text">-----------------------------------</p>
    <p class="thank-text">Thank you, come again!</p>
    <p class="thank-text">-----------------------------------</p>
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

// Correct paper size and layout
$dompdf->setPaper([0, 0, 226.8, 650]); // Same size as the first code block
$dompdf->render();

// Set headers and output the PDF
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="advance_bill.pdf"');
echo $dompdf->output();
?>
