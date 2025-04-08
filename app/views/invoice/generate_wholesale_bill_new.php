<?php
require_once '../../../config/databade.php';
session_start();
$user_name = $_SESSION['username'];
$user_role = $_SESSION['job_role'];
$user_branch = $_SESSION['store'];

$inputData = file_get_contents('php://input');
$billData = json_decode($inputData, true);

function formatToTwoDecimalPoints($number) {
    return number_format($number, 2, '.', '');
}

// Translation function for Sinhala
function getSinhalaText($text) {
    $translations = [
        'Wholesale & Retail' => 'තොග සහ සිල්ලර',
        'Date' => 'දිනය',
        'Time' => 'වේලාව',
        'Invoice Number' => 'බිල් අංකය',
        'User' => 'විකුණුම්කරු',
        'Cashier Counter' => 'අයකැමි කවුළුව',
        'Customer' => 'පාරිභෝගික',
        'Item & Maximum Retail Price' => 'භාණ්ඩය සහ උපරිම සිල්ලර මිල',
        'QTY' => 'ප්‍රමාණය',
        'OUR PRICE' => 'අපේ මිල',
        'AMOUNT' => 'මුදල',
        'Summary' => 'සාරාංශය',
        'Total Items' => 'මුළු අයිතම',
        'Total Quantity' => 'මුළු ප්‍රමාණය',
        'Total Amount' => 'මුළු මුදල',
        'Discount' => 'වට්ටම',
        'Net Amount' => 'ශුද්ධ මුදල',
        'Paid Amount' => 'ගෙවූ මුදල',
        'Balance' => 'ඉතිරිය',
        'Payment Type' => 'ගෙවීම් ක්‍රමය',
        'Total Net Amount' => 'මුළු ශුද්ධ මුදල',
        'Thank You Come Again!' => 'ස්තූතියි නැවත එන්න!',
        'Free Issue' => 'FREE ISSUE',
        'CASH' => 'දුන් මුදල',
        'CASH2' => 'මුදල්',
        'CARD PAYMENT' => 'කාඩ්පත් ගෙවීම්',
        'VOUCHER PAYMENT' => 'වවුචර ගෙවීම්',
        'CREDIT PAYMENT' => 'ණය ගෙවීම',
        'BILL PAYMENT' => 'බිල්පත් ගෙවීම',
        'RisiRasa Products' => 'RisiRasa Products'
    ];
    
    return $translations[$text] ?? $text;
}

// Function to get Sinhala product name
function getSinhalaProductName($productName) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT sinhala_name FROM products WHERE product_name = ?");
    $stmt->bind_param("s", $productName);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['sinhala_name'] ?: $productName;
    }
    return $productName;
}

if (!$billData) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input data.']);
    exit;
}

$language = $billData['language'] ?? 'english';
$billId = $billData['billId'] ?? 'N/A';
$currentDate = $billData['currentDate'] ?? date("Y-m-d H:i:s");
$productList = $billData['productList'] ?? [];
$grossAmount = $billData['grossAmount'] ?? 0;
$cashTendered = $billData['cashTendered'] ?? 0;
$netAmount = $billData['netAmount'] ?? 0;
$discountAmount = $billData['discountAmount'] ?? 0;
$paymentType = strtoupper(str_replace("_", " ", $billData['paymentType'] ?? 'CASH'));
$balance = $billData['balance'] ?? 0;
$transportFee = $billData['transportFee'] ?? 0;

function formatAmount($amount) {
    return number_format($amount, 2, '.', '');
}
//$logoPath = "https://egg.land.nexarasolutions.site/app/views/invoice/images/image.png";
$freeIssuesFlag = false;
$customerID = $billData['customerID'] ?? 0;


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

// Add this after the existing session variables
$branch_details = [];
if ($user_branch) {
    $stmt = $conn->prepare("SELECT address, phone FROM branch WHERE branch_name = ?");
    $stmt->bind_param("s", $user_branch);
    $stmt->execute();
    $result = $stmt->get_result();
    $branch_details = $result->fetch_assoc();
}

?>

<!DOCTYPE html>
<html lang="<?= $language === 'sinhala' ? 'si' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Bill</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Sinhala:wght@400;700&display=swap" rel="stylesheet">
    <link rel="preload" href="FM-DeranaX.ttf" as="font" type="font/ttf" crossorigin>
    <style>
        /* First, let's import additional Sinhala fonts */
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans+Sinhala:wght@400;700&family=Abhaya+Libre:wght@400;700&family=Iskoola+Pota&display=swap');

        @font-face {
            font-family: 'FMDerana';
            src: url('FM-DeranaX.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        /* Regular text - using Noto Sans Sinhala for clean, modern look */
        .sinhala-text {
            font-family: 'Noto Sans Sinhala', sans-serif !important;
            font-size: 11px;
            font-weight: 400;
        }

        /* Product names - using Abhaya Libre for traditional, elegant look */
        .sinhala-product {
            font-family: 'Abhaya Libre', serif !important;
            font-size: 14px;
            font-weight: 400;
        }

        /* Free issue text - using Iskoola Pota for distinctive appearance */
        .sinhala-free-issue {
            font-family: 'FMDerana', sans-serif !important;
            font-size: 15px;
            font-weight: normal;
        }
  
        * {
            margin: 0;
            padding: 0;
            text-align: center;
        }
        
        .english-text {
            font-family: Arial, sans-serif;
            font-size: 11px;
        }
        
        
        * {
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
            padding: 2px;
            border-bottom: 1px dashed black;
            font-size: 11px;
        }
        
        th {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
        }
        
        .summary td {
            font-size: 14px;
            padding: 5px 10px;
            text-align: right;
        }
        
        .summary td:first-child {
            text-align: left;
        }
        
        .summary {
            margin-top: 10px;
        }
        
        .total {
            font-size: 15px;
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
            font-size: 25px;
        }
        
        .footer p {
            margin: 5px 0;
        }
        
        .total-box {
            text-align: center;
            margin: 0 auto;
            border: 2px solid black;
            margin-bottom: 10px;
            font-size: 21px !important;
            padding: 8px 20px;
            margin-top: 10px;
        }

        .logo {
            text-align: left;
            
            margin-bottom:-100px;
        }

        .logo img {
            width: 0.4in;
            height: auto;
            max-height: 0.4in;
            margin-bottom:-100px;
        }

        .header {
            width: 100%;
            padding: 10px 0;
        }

        .header-content {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            margin-left: 20px;  /* Adjust this value to match the image alignment */
        }

        .logo-section {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
        }

        .company-logo {
            width: 80px;  /* Adjust size as needed */
            height: auto;
            margin-right: 10px;
        }

        .company-name {
            font-size: 32px;
            font-weight: bold;
            font-family: serif;
            margin: 0 auto;
        }

        .info-section {
            margin-left: -6px;  /* Aligns with the end of the cart logo */
        }

        .address-section {
            display: flex;
            align-items: center;
            margin-bottom: 3px;
        }

        .phone-section {
            display: flex;
            align-items: center;
            justify-content:center;
        }

        .location-icon, .phone-icon {
            font-size: 14px;
            margin-right: 0px;
        }

        .address, .telephone {
            font-size: 14px;
        }

        .header-line {
            width: 100%;
            height: 1px;
            background: #000;
            margin-top: 2px;
        }

        /* Font styles for better matching */
        @font-face {
            font-family: 'FMAbhaya';
            src: url('fonts/FMAbhaya.ttf') format('truetype');
        }

        .sinhala-text {
            font-family: 'FMAbhaya', 'Noto Sans Sinhala', sans-serif;
        }

        #theme{
            font-family: Comic Sans MS, Comic Sans, cursive;
            font-size:20px;
        }

        .headerDetails tr td{
            font-size:12px;
        }

    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <div class="header-content">
            <!-- Logo and Company Name on same line -->
            <span class="company-name">RisiRasa Products</span>

            <div style="display:flex">
                <div class="logo-section">
                    <?php if ($logoBase64): ?>
                        <img id="logoImg" src="<?= $logoBase64 ?>" alt="Eggland Super Logo" class="company-logo">
                    <?php endif; ?>
                    
                </div>
                
                <!-- Sinhala text and address section -->
                <div class="info-section">
                    <div><span id="theme"></span></div>
                    <?php if ($branch_details): ?>
                        <div class="address-section">
                            <!-- <span class="location-icon"></span> -->
                            <span class="address">◎ <?= htmlspecialchars($branch_details['address']) ?></span>
                        </div>
                        <div class="phone-section">
                            <span class="phone-icon">☎</span>
                            <span class="telephone"><?= htmlspecialchars($branch_details['phone']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="header-line"></div>
    </div>

    <div class="highlight <?= $language === 'sinhala' ? 'sinhala-text' : 'english-text' ?>">
        <?= $language === 'sinhala' ? getSinhalaText('Wholesale & Retail') : 'Wholesale & Retail' ?>
    </div>

    <table style="width: 100%; margin-bottom: 10px; font-size:12px;" class="headerDetails">
        <tr>
            <td style="text-align: left; font-size:12px;" class="<?= $language === 'sinhala' ? 'sinhala-text' : 'english-text' ?>">
                <?= $language === 'sinhala' ? getSinhalaText('Date') : 'Date' ?> : <?= date('Y-m-d', strtotime($currentDate)); ?>
            </td>
            <td style="text-align: right; font-size:12px;" class="<?= $language === 'sinhala' ? 'sinhala-text' : 'english-text' ?>">
                <?= $language === 'sinhala' ? getSinhalaText('Time') : 'Time' ?> : <?= date('h:iA', strtotime($currentDate)); ?>
            </td>
        </tr>
        <tr>
            <td style="text-align: left;" class="<?= $language === 'sinhala' ? 'sinhala-text' : 'english-text' ?>">
                <?= $language === 'sinhala' ? getSinhalaText('User') : 'User' ?> : <?=$user_name;?>
            </td>
            <td style="text-align: right;" class="<?= $language === 'sinhala' ? 'sinhala-text' : 'english-text' ?>">
                <?= $language === 'sinhala' ? getSinhalaText('Cashier Counter') : 'Cashier Counter' ?>: <?= str_pad('1', 3, '0', STR_PAD_LEFT); ?>
            </td>
        </tr>
        <tr>
            <td style="text-align: left;" colspan="2" class="<?= $language === 'sinhala' ? 'sinhala-text' : 'english-text' ?>">
                <?= $language === 'sinhala' ? getSinhalaText('Customer') : 'Customer' ?> : <?=$customerID;?>
            </td>
        </tr>
        <tr>
            <td style="text-align: left;" colspan="2" class="<?= $language === 'sinhala' ? 'sinhala-text' : 'english-text' ?>">
                <?= $language === 'sinhala' ? getSinhalaText('Invoice Number') : 'Invoice Number' ?>: <?= $billId; ?>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th class="<?= $language === 'sinhala' ? 'sinhala-text' : 'english-text' ?>">#</th>
                <th class="<?= $language === 'sinhala' ? 'sinhala-text' : 'english-text' ?>">
                    <?= $language === 'sinhala' ? getSinhalaText('Item & Maximum Retail Price') : 'Item & Maximum Retail Price' ?>
                </th>
                <th class="<?= $language === 'sinhala' ? 'sinhala-text' : 'english-text' ?>">
                    <?= $language === 'sinhala' ? getSinhalaText('QTY') : 'QTY' ?>
                </th>
                <th class="<?= $language === 'sinhala' ? 'sinhala-text' : 'english-text' ?>">
                    <?= $language === 'sinhala' ? getSinhalaText('OUR PRICE') : 'OUR PRICE' ?>
                </th>
                <th class="<?= $language === 'sinhala' ? 'sinhala-text' : 'english-text' ?>">
                    <?= $language === 'sinhala' ? getSinhalaText('AMOUNT') : 'AMOUNT' ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php $totalItems = 0; ?>
            <?php foreach ($productList as $index => $product): ?>
                <?php
                if($product['freeIssueAmount'] > 0){
                    $freeIssuesFlag = true;
                }
                $productName = $language === 'sinhala' ? getSinhalaProductName($product['productName']) : $product['productName'];
                ?>
                <tr>
                    <td style="text-align: center; border-bottom: none;">(<?= $index + 1; ?>.)</td>
                    <td style="text-align: left; border-bottom: none;" class="<?= $language === 'sinhala' ? 'sinhala-product' : 'english-text' ?>">
                        <?= htmlspecialchars($productName); ?>
                    </td>
                    <td style="text-align: center; font-weight: bold; border-bottom: none;"></td>
                    <td style="text-align: center; font-weight: bold; border-bottom: none;"></td>
                    <td style="text-align: center; font-weight: bold; border-bottom: none;"></td>
                </tr>
                <tr>
                    <td style="text-align: left; margin-top: -14px; vertical-align: top;"></td>
                    <td style="text-align: left; font-size: 14px; margin-top: -14px; vertical-align: top;">
                        <strong><?= formatAmount($product['unitPrice']); ?></strong>
                    </td>
                    <td style="text-align: center; margin-top: -14px; vertical-align: top;">
                        <?= htmlspecialchars($product['quantity']); ?>X
                    </td>
                    <td style="text-align: center; font-weight: bold; vertical-align: top;">
                        <?= formatAmount($product['ourPrice']); ?>
                    </td>
                    <td style="text-align: center; vertical-align: top;">
                        <?= formatAmount($product['subtotal']); ?>
                    </td>
                </tr>
                <?php $totalItems += $product['quantity']; ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if($freeIssuesFlag): ?>
        <h3 style="width:34%; text-align:left; margin-top:12px; font-size: 16px; border-bottom: 1px solid black; font-weight:600;" class="<?= $language === 'sinhala' ? 'sinhala-text' : 'english-text' ?>">
            <?= $language === 'sinhala' ? getSinhalaText('Free Issue') : 'FREE ISSUE' ?>:
        </h3>
        <!-- <p class="thank-text">----------------------------------------------------------------------</p> -->
        <table class="fr-issue-table" style="border-bottom: 2px solid black;">
            <?php foreach ($productList as $product): ?>
                <?php if($product['freeIssueAmount'] > 0): 
                    $productName = $language === 'sinhala' ? getSinhalaProductName($product['productName']) : $product['productName'];
                ?>
                    <tr style="border:none; text-align:left;">
                        <td style="border:none; text-align:left; padding-left:20px; font-family: 'FMDerana';" class="<?= $language === 'sinhala' ? 'sinhala-free-issue' : 'english-text' ?>">
                            <?= htmlspecialchars(strtoupper($productName)) ?>
                        </td>
                        <td style="border:none; text-align:left;">
                            <?= htmlspecialchars($product['freeIssueAmount']) ?>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    
    <table class="summary">
        <tr>
            <td class="<?= $language === 'sinhala' ? 'sinhala-text' : 'english-text' ?>">
                <?= $language === 'sinhala' ? getSinhalaText('Total Items') : 'Total Items' ?>
            </td>
            <td><?= count($productList); ?></td>
        </tr>
        <tr>
            <td class="<?= $language === 'sinhala' ? 'sinhala-text' : 'english-text' ?>">
                <?= $language === 'sinhala' ? getSinhalaText('Total Quantity') : 'Total Quantity' ?>
            </td>
            <td><?= $totalItems; ?></td>
        </tr>
        <tr>
            <td class="<?= $language === 'sinhala' ? 'sinhala-text' : 'english-text' ?>">
                <?= $language === 'sinhala' ? getSinhalaText('Total Amount') : 'Total Amount' ?>
            </td>
            <td><?= formatAmount($grossAmount); ?></td>
        </tr>
        <tr>
            <td class="<?= $language === 'sinhala' ? 'sinhala-text' : 'english-text' ?>">
                <?= $language === 'sinhala' ? getSinhalaText('Discount') : 'Discount' ?>
            </td>
            <td><?= formatAmount($grossAmount-$netAmount+$transportFee); ?></td>
        </tr>
        <tr>
            <td style="text-decoration: underline; font-weight: bold; border-bottom:none;" class="<?= $language === 'sinhala' ? 'sinhala-text' : 'english-text' ?>">
                <?= $language === 'sinhala' ? getSinhalaText('Paid Amount') : 'PAID AMOUNT' ?>
            </td>
            <td style="border-bottom:none;"><?= formatAmount($netAmount); ?></td>
        </tr>

        <?php 
            $payments = json_decode($billData['payments'], true);
            $totalPaid = 0;
            $paymentsByType = [];
            
            // Group payments by type
            if ($payments) {
                foreach ($payments as $payment) {
                    $totalPaid += floatval($payment['amount']);
                    $type = $payment['type'];
                    if (!isset($paymentsByType[$type])) {
                        $paymentsByType[$type] = [];
                    }
                    $paymentsByType[$type][] = $payment;
                }
                
                // Display cash payments
                if (isset($paymentsByType['cash_payment'])) {
                    foreach ($paymentsByType['cash_payment'] as $payment) {
                        ?>
                        <tr>
                            <td style="border-bottom:none;" class="<?= $language === 'sinhala' ? 'sinhala-text' : 'english-text' ?>">
                                <?= $language === 'sinhala' ? getSinhalaText('CASH') : 'CASH' ?>
                            </td>
                            <td style="border-bottom:none;"><?= formatAmount($payment['amount']); ?></td>
                        </tr>
                        <?php
                    }
                }

                // Display other payment types
                foreach ($paymentsByType as $type => $payments) {
                    if ($type !== 'cash_payment' && $type !== 'card_payment') {
                        $displayType = strtoupper(str_replace('_', ' ', $type));
                        foreach ($payments as $payment) {
                            ?>
                            <tr>
                                <td style="border-bottom:none;" class="<?= $language === 'sinhala' ? 'sinhala-text' : 'english-text' ?>">
                                    <?= $language === 'sinhala' ? getSinhalaText($displayType) : $displayType ?>
                                </td>
                                <td style="border-bottom:none;"><?= formatAmount($payment['amount']); ?></td>
                            </tr>
                            <?php
                        }
                    }
                }
                
                // Display card payments under one heading
                if (isset($paymentsByType['card_payment'])) {
                    ?>
                    <tr>
                        <td colspan="2" class="<?= $language === 'sinhala' ? 'sinhala-text' : 'english-text' ?>" 
                            style="text-align: left; text-decoration: underline; font-weight: bold; border-bottom:none;">
                            <?= $language === 'sinhala' ? getSinhalaText('CARD PAYMENT') : 'CARD PAYMENT' ?>
                        </td>
                    </tr>
                    <?php
                    foreach ($paymentsByType['card_payment'] as $payment) {
                        $cardType = $payment['cardDetails']['cardType'] ?? 'CARD';
                        $lastFourDigits = $payment['cardDetails']['lastFourDigits'] ?? '';
                        ?>
                        <tr>
                            <td style="border-bottom:none;" class="<?= $language === 'sinhala' ? 'sinhala-text' : 'english-text' ?>" style="text-align: left; padding-left: 20px;">
                                <span style="font-size: 11px;">(<?= $cardType ?> *<?= $lastFourDigits ?>)</span>
                            </td>
                            <td style="border-bottom:none;" ><?= formatAmount($payment['amount']); ?></td>
                        </tr>
                        <?php
                    }
                }
                

            }
            
            // Calculate actual balance (Net Amount - Total Payments)
            $actualBalance = $totalPaid - $netAmount;
            ?>
            
            <tr>
                <td class="<?= $language === 'sinhala' ? 'sinhala-text' : 'english-text' ?>">
                    <?= $language === 'sinhala' ? getSinhalaText('Balance') : 'BALANCE' ?>
                </td>
                <td><?= formatAmount($actualBalance); ?></td>
            </tr>
        </table>

        <p style="text-align:center; font-size:13px; margin-top:12px;" class="<?= $language === 'sinhala' ? 'sinhala-text' : 'english-text' ?>">
            <?= $language === 'sinhala' ? getSinhalaText('Payment Type') : 'Payment Type' ?>: 
            <?php
            if ($payments) {
                $paymentTypes = array_map(function($type) use ($language) {
                    if ($type === 'cash_payment') {
                        return $language === 'sinhala' ? getSinhalaText('CASH2') : 'Cash';
                    }
                    if ($type === 'card_payment') {
                        return $language === 'sinhala' ? getSinhalaText('CARD PAYMENT') : 'Card';
                    }
                    $displayType = ucfirst(str_replace('_', ' ', $type));
                    return $language === 'sinhala' ? getSinhalaText(strtoupper($displayType)) : $displayType;
                }, array_keys($paymentsByType));
                echo implode('/', $paymentTypes);
            } else {
                echo $language === 'sinhala' ? getSinhalaText($paymentType) : $paymentType;
            }
            ?>
        </p>

    <div class="total-box">
        <span style="font-size:20px">
            <b style="font-size:21px; font-family: <?= $language === 'sinhala' ? 'Noto Sans Sinhala' : 'Bookman Old Style' ?>;">
                <?= $language === 'sinhala' ? getSinhalaText('Total Net Amount') : 'Total Net Amount' ?>
            </b>
            <br/>
            <?php echo formatToTwoDecimalPoints(htmlspecialchars($netAmount))?>
        </span>
    </div>

    <div class="barcode">
        <img style="height:60px; width:fit-content;" 
             src="https://barcode.tec-it.com/barcode.ashx?data=<?= $billId; ?>&code=Code128&translate-esc=true" 
             alt="Barcode">
    </div>

    <div class="footer">
        <p style="font-size: 26px; font-weight:600;" class="<?= $language === 'sinhala' ? 'sinhala-text' : 'english-text' ?>">
            <?= $language === 'sinhala' ? getSinhalaText('Thank You Come Again!') : 'Thank You Come Again!' ?>
        </p>
        <p>
            <strong style="font-size: 15px; font-weight:600;" class="<?= $language === 'sinhala' ? 'sinhala-text' : 'english-text' ?>">
                <?= $language === 'sinhala' ? getSinhalaText('Eggland Super') : 'Eggland Super' ?>
            </strong>
        </p>
    </div>
</body>
</html>