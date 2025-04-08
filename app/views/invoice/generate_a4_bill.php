<?php
require_once '../../../config/databade.php';
session_start();
$user_name = $_SESSION['username'];
$user_role = $_SESSION['job_role'];
$user_branch = $_SESSION['store'];

$inputData = file_get_contents('php://input');
$billData = json_decode($inputData, true);

// Get customer details
function getCustomerDetails($customerID) {
    global $conn;
    $stmt = $conn->prepare("SELECT name, address, telephone FROM customers WHERE id = ?");
    $stmt->bind_param("i", $customerID);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getBranchDetails($userbranch) {
    global $conn;
    $stmt = $conn->prepare("SELECT address, phone FROM branch WHERE branch_name = ?");
    $stmt->bind_param("s", $userbranch);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function formatToTwoDecimalPoints($number) {
    return number_format($number, 2, '.', '');
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

// Translation function for Sinhala
function getSinhalaText($text) {
    $translations = [
        'From:' => 'සිට:',
        'To:' => 'දක්වා:',
        'Order No:' => 'ඇණවුම් අංකය:',
        'Date:' => 'දිනය:',
        'Weight:' => 'බර:',
        'Entire Amount:' => 'මුළු මුදල:',
        'Remark' => 'සටහන',
        'Thank you for your business!' => 'ඔබේ ව්‍යාපාරයට ස්තූතියි!',
        'Image loading...' => 'පින්තූරය පූරණය වෙමින්...'
    ];
    return $translations[$text] ?? $text;
}

$language = $billData['language'] ?? 'english';
$billId = $billData['billId'] ?? 'N/A';
$currentDate = $billData['currentDate'] ?? date("Y-m-d");
$customerID = $billData['customerID'] ?? 0;
$paymentStatus = $billData['paymentStatus'] ?? 'unpaid';
$customerDetails = getCustomerDetails($customerID);
$userBranchDetails = getBranchDetails($user_branch);
$transportFee = $billData['transportFee'] ?? 0;

$netAmount = $billData['netAmount'] ?? 0;
$totalAmount = $netAmount + $transportFee;
?>
<!DOCTYPE html>
<html lang="<?= $language === 'sinhala' ? 'si' : 'en' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: 4in 6in;
            margin: 0;
            
        }

        body {
            width: 4in;
            height: 6in;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            font-size: 8pt;
            position: relative;
            background-color: white;
            margin: 0 auto;
            padding:0.05in
        }

        /* Main content wrapper */
        .content-wrapper {
            width: 100%;
            height: 100%;
            padding: 0.1in 0; /* Vertical padding only */
            display: flex;
            justify-content: center;
            border: 1px solid #000;
            
        }

        /* Content container */
        .content-container {
            width: 3.3in; /* Fixed width for content */
            height: fit-content;
            min-height: 5.6in;
            display: flex;
            flex-direction: column;
            gap: 0.15in;
        }

        .logo {
            text-align: left;
            margin-top:0.2in;
            margin-bottom:0.1in;
        }

        .logo img {
            width: 1.2in;
            height: auto;
            max-height: 1.2in;
        }

        .logo-placeholder {
            width: 1.8in;
            height: 1.8in;
            margin: 0 auto;
            border: 1px dashed #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .order-details {
            text-align: left;
            border-top: 1px solid #000;
            padding-top: 0.1in 
        }

        .address-box {
            border: 1px dashed #000;
            padding: 0.15in;
            width: 100%;
        }

        .from-to {
            display: flex;
            justify-content: space-between;
            gap: 0.2in;
        }

        .from, .to {
            flex: 1;
            font-size: 8pt;
            line-height: 1.3;
            text-align: left;
        }

        hr {
            border: none;
            border-top: 1px dashed #000;
            width: 100%;
            margin: 0;
        }

        .section {
            line-height: 1.4;
            text-align: left;
            padding-top:0.1in;
            border-top: 1px solid #000;
        }

        .footer {
            text-align: center;
            font-style: italic;
            margin-top: auto; /* Push footer to bottom */
            padding-bottom: 0.1in;
            padding-top: 0.1in;
            border-top: 1px solid #000;
        }

        .label {
            font-weight: bold;
            display: inline-block;
            width: 0.85in;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
    
    <script>
        function checkImage() {
            var img = document.getElementById('logoImg');
            if (img.complete) {
                if (img.naturalHeight === 0) {
                    document.getElementById('logoPlaceholder').style.display = 'flex';
                    img.style.display = 'none';
                } else {
                    document.getElementById('logoPlaceholder').style.display = 'none';
                    img.style.display = 'block';
                }
            }
        }

        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</head>
<body>
    <div class="content-wrapper">
        <div class="content-container">
            <div class="logo">
                <?php if ($logoBase64): ?>
                    <img id="logoImg" src="<?= $logoBase64 ?>" alt="Ameena Logo" onload="checkImage()" onerror="checkImage()">
                <?php endif; ?>
                <div id="logoPlaceholder" class="logo-placeholder" <?= $logoBase64 ? 'style="display:none;"' : '' ?>>
                    <?= $language === 'sinhala' ? getSinhalaText('Image loading...') : 'Image loading...' ?>
                </div>
            </div>
            
            <div class="order-details">
                <span class="label"><?= $language === 'sinhala' ? getSinhalaText('Order No:') : 'Order No:' ?></span> <?= $billId ?><br>
                <span class="label"><?= $language === 'sinhala' ? getSinhalaText('Date:') : 'Date:' ?></span> <?= date('d/m/Y', strtotime($currentDate)) ?>
            </div>
            
            <hr>
            
            <div class="address-box">
                <div class="from-to">
                    <div class="from">
                        <strong><?= $language === 'sinhala' ? getSinhalaText('From:') : 'From:' ?></strong><br>
                        RisiRasa Products<br>
                        <?= htmlspecialchars($userBranchDetails['address'] ?? '') ?><br>
                        <?= htmlspecialchars($userBranchDetails['phone'] ?? '') ?>
                    </div>
                    <div class="to">
                        <strong><?= $language === 'sinhala' ? getSinhalaText('To:') : 'To:' ?></strong><br>
                        <?= htmlspecialchars($customerDetails['name'] ?? '') ?><br>
                        <?= htmlspecialchars($customerDetails['address'] ?? '') ?><br>
                        <?= htmlspecialchars($customerDetails['telephone'] ?? '') ?>
                    </div>
                </div>
            </div>
            
            
            
            <div class="section">
                <span class="label"><?= $language === 'sinhala' ? getSinhalaText('Weight:') : 'Weight:' ?></span> 
                <?= $billData['orderWeight'] ?? '30 KG' ?><br>

                <span class="label">
                    <?= $language === 'sinhala' ? getSinhalaText('Entire Amount:') : 'Entire Amount:' ?>
                </span> 
                <?= ($paymentStatus == 'unpaid' && $user_branch != 'main store') 
                    ? formatToTwoDecimalPoints($billData['netAmount'] ?? 13400.00) 
                    : '0.00' ?>

                <span class="label"><?= $language === 'sinhala' ? getSinhalaText('Remark') : 'Remark' ?></span> 
                <?= $billData['remark'] ?? ' ' ?><br>
            </div>
            
            <div class="footer">
                <?= $language === 'sinhala' ? getSinhalaText('Thank you for your business!') : 'Thank you for your business!' ?>
            </div>
        </div>
    </div>
</body>
</html>