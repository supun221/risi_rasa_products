<?php
session_start();
$user_name = $_SESSION['username'];
$user_role = $_SESSION['job_role'];
$user_branch = $_SESSION['store'];

$inputData = file_get_contents('php://input');
$billData = json_decode($inputData, true);

function formatToTwoDecimalPoints($number) {
    return number_format($number, 2, '.', '');
}

if (!$billData) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input data.']);
    exit;
}

$billId = $billData['bill_id'] ?? 'Unknown';
$currentDate = $billData['bill_date'] ?? date('Y-m-d');
$productList = $billData['productList'] ?? [];
$grossAmount = $billData['gross_amount'] ?? 0;
$cashTendered = $billData['cashTendered'] ?? 0;
$netAmount = $billData['net_amount'] ?? 0;
$discountAmount = $billData['discount_amount'] ?? 0;
$paymentType = strtoupper(str_replace("_", " ", $billData['payment_type'] ?? 'CASH'));
$balance = $billData['balance'] ?? 0;
function formatAmount($amount) {
    return number_format($amount, 2, '.', '');
}
$logoPath = "https://egg.land.nexarasolutions.site/app/views/invoice/images/bill-header.png";
//$freeIssuesFlag = false;
$customerID = $billData['customer_id'] ?? 'Unknown';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Bill</title>
    <style>
        * {
            font-family: Arial;
            font-size: 11px;
            margin: 0;
            padding: 0;
            text-align: center;
        }
        body {
            width: 80mm;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 5px;
        }
        .header img {
            max-width: 300px;
            max-height: 260px;
        }
        .header p {
            margin: 2px 0;
        }
        .highlight {
        background-color: black; 
        color: white; 
        font-size: 14px;
        font-weight: bold;
        padding: 5px 0;
        text-transform: uppercase;
        text-align: center; 
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
        th {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
        }
        .summary td {
        font-size: 12px;
        padding: 5px 10px; 
        text-align: right;
    }
    .summary td:first-child {
        text-align: left; 
    }
    .summary {
        margin-top: 10px; 
        font-weight: bold; 
    }
        
        .total {
            font-size: 14px;
            font-weight: bold;
            margin: 5px 0;
            border-top: 2px solid black;
            border-bottom: 2px solid black;
            padding: 5px 0;
        }
        .barcode {
            margin: 10px 0;
        }
        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 12px;
        }
        .footer p {
            margin: 5px 0;
        }
        .total-box {
            text-align:center;
            margin: 0 auto;
            border: 2px solid black;
            margin-bottom: 10px;
            font-size: 20px !important;
            padding:8px 20px;
            margin-top:10px;
        }
    </style>
</head>
<body onload="window.print();">

    <div class="header">
        <img src="<?= $logoPath; ?>" alt="Eggland Super Logo">
    <!--    <p><strong>Eggland Super</strong></p>-->
    <!--    <p>ගොඩක් හොඳ අඩුම මිල</p>-->
    <!--    <p>Dambulla Road, Yaggapitiya, Kurunegala</p>-->
    <!--    <p>037-2223457</p>-->
    <!--</div>-->
<div class="highlight">Wholesale & Retail</div>
    <table style="width: 100%; margin-bottom: 10px;">
        <tr>
            <td style="text-align: left; width: 50%;">
                Date: <?= date('Y-m-d', strtotime($currentDate)); ?>
            </td>
            <td style="text-align: right; width: 50%;">
                Time: <?= date('h:iA', strtotime($currentDate)); ?>
            </td>
        </tr>
        <tr>
            <td style="text-align: left;">
                User: <?=$user_name;?>
            </td>
            <td style="text-align: right;">
                Cashier Counter: <?= str_pad('1', 3, '0', STR_PAD_LEFT); ?>
            </td>
        </tr>
        <tr>
            <td style="text-align: left;" colspan="2">
                Customer : <?=$customerID;?>
            </td>
        </tr>
        <tr>
            <td style="text-align: left;" colspan="2">
                Invoice Number: <?= $billId; ?>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Item & Maximum Retail Price</th>
                <th>QTY</th>
                <th>OUR PRICE</th>
                <th>AMOUNT</th>
            </tr>
        </thead>
        <tbody>
            <?php $totalItems = 0; ?>
            <?php foreach ($productList as $index => $product): ?>
                <?php
                // if($product['freeIssueAmount'] > 0){
                //     $freeIssuesFlag = true;
                // }?>
                <tr>
                    <td style="text-align: center; border-bottom: none;">(<?= $index + 1; ?>.)</td> <!-- Numbering -->
                    <td style="text-align: left; border-bottom: none;">
                        <?= htmlspecialchars($product['product_name']); ?>
                    </td>
                    <td style="text-align: center; font-weight: bold; border-bottom: none;"></td>
                    <td style="text-align: center; font-weight: bold; border-bottom: none;"></td>
                    <td style="text-align: center; font-weight: bold; border-bottom: none;"></td>
                </tr>
                <tr>
                    <td style="text-align: left;margin-top: -14px; vertical-align: top;"></td>
                    <td style="text-align: left; font-size: 14px; margin-top: -14px; vertical-align: top;">
                        <strong><?= formatAmount($product['price']); ?></strong> <!-- Unit Price Below Product Name -->
                    </td>
                    <td style="text-align: center; margin-top: -14px; vertical-align: top;"><?= htmlspecialchars($product['purchase_qty']); ?>      X  </td>
                    <td style="text-align: center; font-weight: bold; vertical-align: top;"><?= formatAmount($product['price']); ?></td>
                    <td style="text-align: center; vertical-align: top;"><?= formatAmount($product['subtotal']); ?></td>
                </tr>
                <?php $totalItems += $product['purchase_qty']; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
    // if($freeIssuesFlag){
    //     echo '<h3 style="width:100%; text-align:center;">Free Issue</h3>';
    //     echo '<p class="thank-text">----------------------------------------------------------------------</p>';
    //     echo '<table class="fr-issue-table">';
    //     foreach ($productList as $product) {
    //         if($product['freeIssueAmount'] > 0){
    //             echo '
    //             <tr style="border:none; text-align:left;">
    //                 <td style="border:none; text-align:left; padding-left:20px;">' . htmlspecialchars(strtoupper($product['productName'])) . '</td>
    //                 <td style="border:none; text-align:left;"> x' . htmlspecialchars($product['freeIssueAmount']) . '</td>
    //             </tr style="border:none; text-align:left;">';
    //         }
    //     }
    //     echo '</table>'; 
    // }?>

    <p class="section-title">Summary</p>
<table class="summary">
    <tr>
        <td>Total Items</td>
        <td><?= count($productList); ?></td>
    </tr>
    <tr>
        <td>Total Quantity</td>
        <td><?= $totalItems; ?></td>
    </tr>
    <tr>
        <td>Total Amount</td>
        <td><?= formatAmount($grossAmount); ?></td>
    </tr>
    <tr>
        <td>Discount</td>
        <td><?= formatAmount($grossAmount-$netAmount); ?></td>
    </tr>
    <tr>
        <td>Net Amount</td>
        <td><?= formatAmount($netAmount); ?></td>
    </tr>
    <tr>
        <td>Paid Amount</td>
        <td><?= formatAmount($cashTendered); ?></td>
    </tr>
    <tr>
        <td>Balance</td>
        <td><?= formatAmount($balance); ?></td>
    </tr>
</table>
<p style="text-align:center; font-size: .8em;">Payment Type: <?php echo str_replace("_"," ", strtoupper(htmlspecialchars($paymentType)))?></p>

<div class="total-box">
    <span style="font-size:20px"> <b style="font-size:20px; font-family:Bookman Old Style;">Total Net Amount</b> </br> <?php echo formatToTwoDecimalPoints(htmlspecialchars($netAmount))?> </span>
</div>

    <div class="barcode">
        <img src="https://barcode.tec-it.com/barcode.ashx?data=<?= $billId; ?>&code=Code128&translate-esc=true" alt="Barcode">
    </div>

    <div class="footer">
        <p>Thank You Come Again!</p>
        <p><strong>RisiRasa Products</strong></p>
    </div>
</body>
</html>