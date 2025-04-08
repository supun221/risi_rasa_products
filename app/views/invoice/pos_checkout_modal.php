<?php

include '../../models/Database.php';
include '../../models/POS_Checkout.php';

$checkoutHandler = new POS_CHECKOUT($db_conn);

session_start();
$user_name = $_SESSION['username'];
$user_role = $_SESSION['job_role'];
$user_branch = $_SESSION['store'];

function getCurrentDate() {
    date_default_timezone_set('Asia/Colombo');
    return date('Y-m-d h:iA');
}


function formatToTwoDecimals($number) {
    return number_format((float)$number, 2, '.', '');
}

$currentDate = getCurrentDate();

$billID = isset($_GET['billID']) ? $_GET['billID'] : null;
$grossTotal = isset($_GET['grossTotal']) ? $_GET['grossTotal'] : null;
$totalValue = isset($_GET['totalValue']) ? $_GET['totalValue'] : null;
$totalDiscount = isset($_GET['totalDiscount']) ? $_GET['totalDiscount'] : null;
$totalCashTendered = isset($_GET['totalCashTendered']) ? $_GET['totalCashTendered'] : null;
$productList = isset($_GET['productList']) ? $_GET['productList'] : null;
$customerName = isset($_GET['customerName']) ? $_GET['customerName'] : "Default";
$customerId = isset($_GET['customerId']) ? (INT)$_GET['customerId'] : "Default";
$invoiceType = isset($_GET['invoiceType']) ? $_GET['invoiceType'] : null;
$cashierName = isset($_GET['cashierName']) ? $_GET['cashierName'] : null;


if($customerId != "Default"){
    $advancePaymentRecord = $checkoutHandler->retrieveAdvancePaymentRecord($customerId);
}else{
    $advancePaymentRecord = null;
}


if($totalCashTendered != null && $totalValue != null){
    $totalBalance = $totalCashTendered - $totalValue;
}else{
    $totalBalance = 0;
}

if ($productList) {
    $productList = json_decode($productList, true);
}

if($customerName == ""){
    $customerName = "Default";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RisiRasa Products | Payment</title>
    <link rel="stylesheet" href="./create-invoice.styles.css">
    <link rel="stylesheet" href="./pos_checkout.styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Maname&family=Noto+Serif:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Yaldevi:wght@200..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/notifier/style.css"></link>
    <script src="../../assets/notifier/index.var.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="PaymentHandler.js"></script>
</head>
<body>

    <!-- bill cancel confirmation -->
     <div class="bill-cancellation-modal" id="bill-cancellation-modal">
         <div class="bc-form-area">
            <button class="bc-close-btn" onclick="closeBcModal()">
             <i class="fa-solid fa-xmark"></i>
            </button>
            <span class="bc-title">Cancel The Bill ?</span>
            <p class="cancellation-prompt">
                You are about the cancell the current bill. Please enter a valid reason for cancel current bill.
            </p>
            <div class="payment-inp-cont" style="width: 100%; margin:0;">
                <label class="payment-inp-label" style="font-size: .8em;">reason for cancellation:</label>
                <input style="width: 100%;" type="text" id="cancellation_reason" class="payment-inp-field large">
            </div>

            <button class="bc-btn" onclick="createDeleteBillRecord()">
                Cancel Bill
            </button>
        </div>
     </div>

    <!-- payment portal element -->
     <div class="payment-portal-container">

        <?php
            if($advancePaymentRecord != null){
                echo "
                    <div class='advance-payment-notifier'>
                        <div class='alert-icon'>
                            <i class='fa-solid fa-money-bill-1-wave'></i>
                        </div>
                        <div class='alert-text-content-container'>
                            <span class='alert-heading'>Advance Payment Found!</span>
                            <p class='alert-text'>Advance payment of LKR ". number_format($advancePaymentRecord['net_amount'], 2) ." made on ". explode(" ",$advancePaymentRecord['created_at'])[0] .". </p>
                        </div>
                    </div>
                ";
            };
        ?>

        <div class="payment-portal-form">
            <div class="total-value-displayer">
                <div class="total-val-indicator-main">
                    <span class="total-val-label-main">Total Amount:</span>
                    LKR:<input style="color: #26de81;" type="hidden" class="total-val-input-main" id="total_amount2" value="<?php echo formatToTwoDecimals($totalValue) ?>" disabled>
                    <input style="color: #26de81;" type="text" class="total-val-input-main" id="total_amount" value="<?php echo formatToTwoDecimals($totalValue) ?>" disabled>
                </div>
            </div>

            <div class="payment-form-columns">
                <!-- Left Column - Main Input Elements -->
                <div class="payment-column-left">
                    <div class="input-elements-payment">
                        <!-- input elements -->
                        <div class="payment-inp-cont">
                            <label class="payment-inp-label">customer name:</label>
                            <input type="text" id="customer_id" class="payment-inp-field large" value="<?php echo $customerName ?>">
                        </div>

                 <div class="payment-inp-cont">
                    <label class="payment-inp-label">discount:</label>
                    <div class="horizontal-cont">
                        <!-- <input type="text" id="discount_percentage" class="payment-inp-field short" placeholder="%"> -->
                        <input type="number" id="discount_amount" class="payment-inp-field short" placeholder="Amount" value="<?php echo formatToTwoDecimals($totalDiscount) ?>">
                    </div>
                 </div>
                 
                 <div class="payment-inp-cont">
                    <label class="payment-inp-label">payment type:</label>
                    <select id="payment_type" class="payment-inp-field large">
                        <option value="cash_payment">Cash Payment</option>
                        <option value="credit_payment">Credit Payment</option>
                        <option value="bill_payment">Bill Payment</option>
                        <option value="voucher_payment">Voucher Payment</option>
                        <option value="free_payment">Free Payment</option>
                    </select>
                 </div>

                        <div class="payment-inp-cont">
                            <label class="payment-inp-label">gross amount:</label>
                            <input type="text" id="gross_amount" class="payment-inp-field large" value="<?php echo formatToTwoDecimals($grossTotal) ?>">
                        </div>
                        

                        <!-- <div class="payment-inp-cont">
                            <label class="payment-inp-label">bill type:</label>
                            <select id="bill_type" class="payment-inp-field large">
                                <option value="80mm">80mm - Bill</option>
                                <option value="50mm">50mm - Bill</option>
                            </select>
                        </div> -->

                 <div class="payment-inp-cont" style="position: relative;">
                    <label class="payment-inp-label">net amount:</label>
                    <input type="number" id="net_amount" class="payment-inp-field large" value="<?php echo formatToTwoDecimals($totalValue) ?>">
                    <input type="text" id="net_amount_permanent" value="<?php echo formatToTwoDecimals($totalValue) ?>" hidden>
                    <span class="deduction-alert" id="deduction-alert">-<?php echo number_format($advancePaymentRecord['net_amount'] ?? 0, 2) ?></span>
                 </div>

                        <div class="payment-inp-cont">
                            <label class="payment-inp-label">balance:</label>
                            <input type="text" id="balance" class="payment-inp-field large" value="<?php echo formatToTwoDecimals($totalBalance) ?>" disabled>
                        </div>
                        
                        <div class="payment-inp-cont">
                            <label class="payment-inp-label">Weight(kg):</label>
                            <input type="number" id="order_weight" class="payment-inp-field large">
                        </div>
            
                        <div class="payment-inp-cont">
                            <label class="payment-inp-label">Transport Fee:</label>
                            <input type="number" id="transport_fee" class="payment-inp-field large" value="0" min="0" step="0.01">
                        </div>

                        <div class="payment-inp-cont">
                            <label class="payment-inp-label">Remark:</label>
                            <input type="text" id="remark" class="payment-inp-field large">
                        </div>

                        <div class="payment-inp-cont-exception" id="received_cash">
                            <label class="payment-inp-label">cash tendered:</label>
                            <input type="text" id="cash_tendered" class="payment-inp-field large" onkeyup="checkoutBalanceHandler()">
                        </div>


                        <!-- <div class="payment-inp-cont bill-checker" style="transform: translateY(12px);">
                            <?php 
                                // if($advancePaymentRecord != null){
                                //     echo "
                                //         <div>
                                //             <input type='checkbox' id='deductAdvancePayment' class='payment-inp-field' onchange='handleDeductAdvancePayment()'>
                                //             <label class='payment-inp-label'>Deduct Advance Payment</label>
                                //         </div>
                                //     ";
                                // }
                            ?>
                            <div>
                                <input type="checkbox" id="printEnabled" class="payment-inp-field">
                                <label class="payment-inp-label">print bill</label>
                            </div>
                        </div> -->


                        <div class="payment-inp-cont bill-checker" style="transform: translateY(12px);">
                            <?php 
                                if($advancePaymentRecord != null){
                                    echo "
                                        <div>
                                            <input type='checkbox' id='deductAdvancePayment' class='payment-inp-field' onchange='handleDeductAdvancePayment()'>
                                            <label class='payment-inp-label'>Deduct Advance Payment</label>
                                        </div>
                                    ";
                                }
                            ?>
                            <div>
                                <div class="language-selection" style="margin-bottom: 10px;">
                                    <input type="radio" id="langEnglish" name="billLanguage" style="margin-left: 10px;" value="english" checked>
                                    <label for="langEnglish" class="payment-inp-label">English</label>
                                    <input type="radio" id="langSinhala" name="billLanguage" value="sinhala">
                                    <label for="langSinhala" class="payment-inp-label">සිංහල</label>

                                </div>
                                <div style="margin-left:10px; margin-top:-9px;" className="bill-size-selection mb-2">
                                    <input 
                                        type="radio" 
                                        id="posBill" 
                                        name="billSize" 
                                        value="pos" 
                                        <?php echo ($user_branch != 'Main Store') ? 'checked' : ''; ?> 
                                        className="mr-2"
                                    />
                                    <span className="mr-4">POS</span>
                                    
                                    <input 
                                        type="radio" 
                                        id="a4Bill" 
                                        name="billSize" 
                                        value="a4" 
                                        <?php echo ($user_branch == 'Main Store') ? 'checked' : ''; ?> 
                                        className="mr-2"
                                    />
                                    <span>Main</span>
                                </div>
                                <!-- <input type="checkbox" id="printEnabled" class="payment-inp-field">
                                <label class="payment-inp-label">print bill</label> -->
                            </div>
                            <div class="payment-status-selection" style="margin-bottom: 10px;">
                                <input type="radio" id="paidStatus" name="paymentStatus" style="margin-left: 10px;" value="paid" checked>
                                <label for="paidStatus" class="payment-inp-label">Paid</label>
                                <input type="radio" id="unpaidStatus" name="paymentStatus" value="unpaid">
                                <label for="unpaidStatus" class="payment-inp-label">Unpaid</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Payment Type and Multiple Payments -->
                <div class="payment-column-right">
                    <!-- Payment Type -->
                    <div style="display:flex;">
                        <div class="payment-inp-cont">
                            <label class="payment-inp-label">payment type:</label>
                            <select id="payment_type" class="payment-inp-field large">
                                <option value="cash_payment">Cash Payment</option>
                                <option value="card_payment">Card Payment</option>
                                <option value="credit_payment">Credit Payment</option>
                                <option value="bill_payment">Bill Payment</option>
                                <option value="voucher_payment">Voucher Payment</option>
                                <option value="free_payment">Free Payment</option>
                            </select>
                        </div>
                        <div class="payment-inp-cont">
                            <label class="payment-inp-label">Due:</label>
                            <input type="text" id="payment_due" class="payment-inp-field large" value="<?php echo formatToTwoDecimals(max(0, $totalValue)) ?>" disabled>
                        </div>
                    </div>
                    <!-- Multiple Payments Container -->
                    <div class="multiple-payments-container">
                        <div class="payment-input-group">
                            <input type="number" id="payment_amount" class="payment-inp-field" placeholder="Amount">
                            <button onclick="addNewPayment()" class="payment-btn">Add Payment</button>
                        </div>
                        
                        <div id="card_details_container">
                            <div class="payment-inp-cont">
                                <label class="payment-inp-label">Last 4 Digits:</label>
                                <input type="text" id="card_last_four" class="payment-inp-field" maxlength="4">
                            </div>
                            <div class="payment-inp-cont">
                                <label class="payment-inp-label">Card Type:</label>
                                <select id="card_type" class="payment-inp-field">
                                    <option value="VISA">Visa</option>
                                    <option value="MASTER">Master</option>
                                </select>
                            </div>
                        </div>
                        
                        <div id="payments_list"></div>
                    </div>
                </div>
            </div>

                 

                 <!-- payment page btn container -->
                  <div class="payment-btn-cont">
                    <button class="payment-btn pay-pos" id="pay-bill-btn" onclick="executePaymentProcedure()">Pay Bill</button>
                    <button class="payment-btn close-pos" onclick="openBcModal()">Close Bill</button>
                  </div>
            </div>
        </div>
     </div>
</body>

<script>
        let notifier = new AWN();


        const discountHandlerCheckout = (discountAmount) => {
            const totalNetAmount = document.getElementById('net_amount_permanent').value
            const discount = parseFloat(discountAmount) || 0;
            const totalDue = (parseFloat(totalNetAmount) - discount).toFixed(2);
            document.getElementById('net_amount').value = totalDue;
            console.log('executing');
            
        };
        document.getElementById('discount_amount').addEventListener('input', function (event) {
    discountHandlerCheckout(event.target.value);
});




        const checkoutBalanceHandler = () => {
            const netAmountValue = parseFloat(document.getElementById('net_amount').value)
            const balanceValueHolder = document.getElementById('balance')
            const receivedCashValue = parseFloat(document.getElementById('cash_tendered').value)
            const newBalance = receivedCashValue - netAmountValue
            balanceValueHolder.value = newBalance.toFixed(2)
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });

        function updateDateTime() {
            const now = new Date();
            const timeElement = document.getElementById('current-time');
            timeElement.textContent = now.toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });

            const dateElement = document.getElementById('current-date');
            dateElement.textContent = now.toLocaleDateString('en-US', {
                month: 'long',
                day: 'numeric',
                year: 'numeric'
            });
        }

        const handleDeductAdvancePayment = () => {
            const receivedCashComp = document.getElementById('received_cash')
            const deductionAlert = document.getElementById('deduction-alert')
            const advancePaymentCheckbox = document.getElementById('deductAdvancePayment')
            const netAmountValueHolder = document.getElementById('net_amount')
            const balanceValueHolder = document.getElementById('balance')
            let newNetAmount = 0;
            let newBalanceAmount = 0;
            if(advancePaymentCheckbox && advancePaymentCheckbox.checked){
                newNetAmount = parseFloat(netAmountValueHolder.value) - parseFloat(<?php echo $advancePaymentRecord['net_amount'] ?? 0 ?>)
                deductionAlert.classList.add("show-adv-pay-alert")
                receivedCashComp.classList.add("show-adv-pay-alert")

                if(newNetAmount < 0){
                    balanceValueHolder.value = (parseFloat(<?php echo $totalBalance ?>) - newNetAmount).toFixed(2)
                }
            }else{
                balanceValueHolder.value = (parseFloat(<?php echo $totalBalance ?>)).toFixed(2)
                deductionAlert.classList.remove("show-adv-pay-alert")
                receivedCashComp.classList.remove("show-adv-pay-alert")
                newNetAmount = <?php echo formatToTwoDecimals($totalValue) ?>
            }
            netAmountValueHolder.value = newNetAmount.toFixed(2)
        }


        const executePaymentProcedure = () => {
            const selectedLanguage = document.querySelector('input[name="billLanguage"]:checked');
            const selectedBillSize = document.querySelector('input[name="billSize"]:checked');
            
            // Validation check
            if (!selectedLanguage || !selectedBillSize) {
                Swal.fire({
                    title: "Please select the bill type and the language",
                    icon: "warning",
                    confirmButtonText: "OK"
                });
                return;
            }

            const billSize = document.querySelector('input[name="billSize"]:checked').value;
            const advancePaymentCheckbox = document.getElementById('deductAdvancePayment');
            const invoice_type = '<?php echo $invoiceType ?>';
            const billId = '<?php echo $billID; ?>';
            let advancePaymentAmount = 0;
            try {
                if(advancePaymentCheckbox && advancePaymentCheckbox.checked){
                    const recordId = '<?php echo $advancePaymentRecord['id'] ?? 0; ?>';
                    advancePaymentAmount = parseFloat("<?php echo isset($advancePaymentRecord['net_amount']) ? number_format($advancePaymentRecord['net_amount'], 2, '.', '') : 0; ?>");
                    deleteAdvancePaymentRecord(recordId);
                }
                createBillRecord();
                updateStockEntries();
                createPurchaseRecords();
                deleteHoldBill(billId);
                
                if(billSize === 'pos') {
                    if(invoice_type === 'retail') {
                        generate_pos_bill(advancePaymentAmount);
                    } else if(invoice_type === 'wholesale') {
                        generate_wholesale_bill();
                    }
                } else {
                    generate_a4_bill();
                }
                
                clearCartState();
                
                
                // Show success message
                Swal.fire({
                    title: "Payment Successful!",
                    icon: "success",
                    timer: 2000,
                    showConfirmButton: false
                });
                
                // Close modal after 3 seconds
                setTimeout(() => {
                    if (window.parent && window.parent !== window) {
                        window.parent.document.getElementById('payment-modal').style.display = 'none';
                        window.parent.document.getElementById('payment-iframe').src = '';
                        window.parent.location.reload();
                    }
                }, 2500);
            } catch(error) {
                console.log(error);
                Swal.fire({
                    title: "Payment Unsuccessful!",
                    icon: "error",
                });
            }
        }

        const deleteHoldBill = (billId) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'delete_held_invoice.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    console.log('Successfully deleted hold bill records');
                }
            };

            xhr.send(`bill_no=${encodeURIComponent(billId)}`);
        };

        const createBillRecord = () => {
            const billId = '<?php echo $billID; ?>';
            const currentDate = '<?php echo $currentDate; ?>';
            const productList = <?php echo json_encode($productList); ?>;
            const customerID = document.getElementById('customer_id').value;
            const grossAmount = document.getElementById('gross_amount').value;
            const netAmount = document.getElementById('net_amount').value;
            const discountAmount = document.getElementById('discount_amount').value;
            const numOfProducts = productList.length;
            const paymentType = document.getElementById('payment_type').value;
            const balance = document.getElementById('balance').value;
            const billDate = currentDate;
            const customerNo = '<?php echo $customerId; ?>';
            const orderWeight= document.getElementById('order_weight').value;
            const transportFee = document.getElementById('transport_fee').value;
            const remark = document.getElementById('remark').value;
            // Get the payment status from radio buttons
            const paymentStatus = document.querySelector('input[name="paymentStatus"]:checked').value;
            const payments = paymentHandler.getPaymentsData();

            if (billId && customerID && grossAmount && netAmount && discountAmount && numOfProducts && paymentType && balance && billDate) {
                const data = {
                    billId: billId,
                    customer_id: customerID,
                    gross_amount: grossAmount,
                    net_amount: netAmount,
                    discount_amount: discountAmount,
                    num_of_products: numOfProducts,
                    payment_type: paymentType,
                    balance: balance,
                    bill_date: billDate,
                    customerNo: customerNo,
                    orderWeight: orderWeight,
                    transportFee: transportFee,
                    remark: remark,
                    paymentStatus: paymentStatus, // Add payment status to data
                    payments: JSON.stringify(payments)
                };
                console.log(data);
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'create_bill_record.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        console.log('success bill record!');
                        // After successful bill record creation, create payment tracker record
                        createPaymentTrackerRecord(billId, paymentStatus);
                    }
                };

                const urlEncodedData = Object.keys(data)
                    .map(key => `${key}=${encodeURIComponent(data[key])}`)
                    .join('&');

                xhr.send(urlEncodedData);
            } else {
                Swal.fire("Error: Missing required data.")
            }
        }

        // Function to create payment tracker record
        const createPaymentTrackerRecord = (billId, paymentStatus) => {
            const data = {
                billId: billId,
                paymentStatus: paymentStatus
            };
            
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'create_payment_status_record.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    console.log('Payment tracker record created successfully!');
                }
            };

            const urlEncodedData = Object.keys(data)
                .map(key => `${key}=${encodeURIComponent(data[key])}`)
                .join('&');

            xhr.send(urlEncodedData);
        }

        const deleteAdvancePaymentRecord = (recordId) => {
            if (!recordId) {
                console.error("Record ID is required.");
                return;
            }
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "settle_advance_payment.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if (xhr.status === 200) {
                    notifier.success(xhr.responseText);
                } else {
                    notifier.alert("Error updating records: " + xhr.status);
                }
            };

            xhr.send(`record_id=${recordId}`);
        }

        const createPurchaseRecords = () => {
            const billID = '<?php echo $billID; ?>';
            const currentDate = '<?php echo $currentDate; ?>';
            const productList = <?php echo json_encode($productList); ?>;
            
            if (billID && productList && productList.length > 0) {
                productList.forEach(product => {
                    const data = {
                        billId: billID,
                        stockId:product.stockId,
                        barcode: product.barcode,
                        product_name: product.productName,
                        price: product.unitPrice,
                        qty: product.quantity,
                        disc_percentage: product.discount || 0,
                        subtotal: product.subtotal,
                        date: currentDate,
                        sellPrice:product.sellPrice
                    };
                    console.log(data);
                    

                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'create_purchase_item_record.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            console.log('Response from server:', xhr.responseText);
                        }
                    };

                    const urlEncodedData = Object.keys(data).map(key => `${key}=${encodeURIComponent(data[key])}`).join('&');
                    xhr.send(urlEncodedData);
                });
            } else {
                Swal.fire('Error: Missing Bill ID or Product List.');
                return
            }
        }

        const updateStockEntries = () => {
            const productList = <?php echo json_encode($productList); ?>;

            if (productList.length > 0) {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'update_stock_entries.php', true);
                xhr.setRequestHeader('Content-Type', 'application/json');

                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        console.log('Response from server:', xhr.responseText);
                    } else if (xhr.readyState === 4) {
                        console.log('Error updating stock entries:', xhr.responseText);
                    }
                };
                xhr.send(JSON.stringify(productList));
            } else {
                Swal.fire('Error: Product list is empty.');
                return;
            }
        };


        const generate_pos_bill = (advancePaymentAmount) => {
            const billId = '<?php echo $billID; ?>';
            const currentDate = '<?php echo $currentDate; ?>';
            const productList = <?php echo json_encode($productList); ?>;
            const cashTendered = <?php echo json_encode($totalCashTendered); ?>;
            const customerID = document.getElementById('customer_id').value;
            const grossAmount = document.getElementById('gross_amount').value;
            const netAmount = document.getElementById('net_amount').value;
            const discountAmount = document.getElementById('discount_amount').value;
            const numOfProducts = productList.length;
            const paymentType = document.getElementById('payment_type').value;
            const balance = document.getElementById('balance').value;
            const billDate = currentDate;
            const language = document.querySelector('input[name="billLanguage"]:checked').value;
            const payments = paymentHandler.getPaymentsData();
            const transportFee = document.getElementById('transport_fee').value;

            const payload = {
                billId,
                currentDate,
                productList,
                customerID,
                grossAmount,
                netAmount,
                discountAmount,
                paymentType,
                balance,
                billDate,
                cashTendered,
                advancePaymentAmount,
                language,
            };

            console.log(payload);
            
            fetch('./generate_pos_bill_new.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload),
            })
            .then((response) => response.blob())
            .then((blob) => {
                const url = window.URL.createObjectURL(blob);
                window.open(url);
            })
            .catch((error) => console.error('Error generating POS bill:', error));
        }

        const generate_wholesale_bill = () => {
            const billId = '<?php echo $billID; ?>';
            const currentDate = '<?php echo $currentDate; ?>';
            const productList = <?php echo json_encode($productList); ?>;
            const cashTendered = <?php echo json_encode($totalCashTendered); ?>;
            const customerID = document.getElementById('customer_id').value;
            const grossAmount = document.getElementById('gross_amount').value;
            const netAmount = document.getElementById('net_amount').value;
            const discountAmount = document.getElementById('discount_amount').value;
            const numOfProducts = productList.length;
            const paymentType = document.getElementById('payment_type').value;
            const balance = document.getElementById('balance').value;
            const billDate = currentDate;
            const language = document.querySelector('input[name="billLanguage"]:checked').value;
            const payments = paymentHandler.getPaymentsData();
            const transportFee = document.getElementById('transport_fee').value;

            const payload = {
                billId,
                currentDate,
                productList,
                customerID,
                grossAmount,
                netAmount,
                discountAmount,
                paymentType,
                balance,
                billDate,
                cashTendered,
                language,
                payments: JSON.stringify(payments),
                transportFee
            };

            console.log(payload);
            
            fetch('./generate_wholesale_bill_new.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload),
            })
            .then((response) => response.blob())
            .then((blob) => {
                const url = window.URL.createObjectURL(blob);
                window.open(url);
            })
            .catch((error) => console.error('Error generating POS bill:', error));
        }

        const openBcModal = () => {
            const bcModal = document.getElementById('bill-cancellation-modal')
            bcModal.classList.add('display-bc-modal')
        }

        const closeBcModal = () => {
            const bcModal = document.getElementById('bill-cancellation-modal')
            bcModal.classList.remove('display-bc-modal')
        }

        function getSelectedPaymentStatus() {
            const selectedValue = document.querySelector('input[name="paymentStatus"]:checked').value;
            console.log("Selected Payment Status:", selectedValue);
            return selectedValue;
        }

        const createDeleteBillRecord = async () => {
            let notifier = new AWN()
            const bill_id = '<?php echo $billID ?>'
            const reason = document.getElementById('cancellation_reason').value.trim()
            const billAmount = parseFloat(<?php echo $totalValue ?>)
            const cancelled_by = '<?php echo $cashierName ?>'
            try {
                const response = await fetch('./create_delete_bill_record.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        bill_id: bill_id,
                        reason: reason,
                        bill_amount: billAmount,
                        cancelled_by: cancelled_by
                    })
                });

                const result = await response.json();
                if (result.success) {
                    closeBcModal()
                    notifier.success(result.message)
                    clearCartState()
                    setTimeout(()=>{
                        window.location.href = './create_invoice2.php'
                    },1000)
                } else {
                    notifier.alert(result.message)
                }
            } catch (error) {
                console.log('Error:', error);
                alert('Failed to create a record of cancellation. Please try again.');
            }
        };

        const clearCartState = () => {
            localStorage.removeItem('shopping_cart')
        }



        const generate_a4_bill = () => {
            const billId = '<?php echo $billID; ?>';
            const currentDate = '<?php echo $currentDate; ?>';
            const productList = <?php echo json_encode($productList); ?>;
            //const customerID = document.getElementById('customer_id').value;
            const grossAmount = document.getElementById('gross_amount').value;
            const netAmount = document.getElementById('net_amount').value;
            const discountAmount = document.getElementById('discount_amount').value;
            const paymentType = document.getElementById('payment_type').value;
            const balance = document.getElementById('balance').value;
            const language = document.querySelector('input[name="billLanguage"]:checked').value;
            const cashTendered = document.getElementById('cash_tendered').value;
            const customerID = '<?php echo $customerId; ?>';
            const orderWeight= document.getElementById('order_weight').value;
            const transportFee = document.getElementById('transport_fee').value;
            const remark = document.getElementById('remark').value;
            const paymentStatus = getSelectedPaymentStatus()

            const payload = {
                billId,
                currentDate,
                productList,
                customerID,
                grossAmount,
                netAmount,
                discountAmount,
                paymentType,
                balance,
                cashTendered,
                language,
                orderWeight,
                transportFee,
                remark,
                paymentStatus
            };

            fetch('./generate_a4_bill.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload),
            })
            .then((response) => response.blob())
            .then((blob) => {
                const url = window.URL.createObjectURL(blob);
                window.open(url);
            })
            .catch((error) => console.error('Error generating A4 bill:', error));
        }

        let paymentHandler;

        document.addEventListener('DOMContentLoaded', () => {
            paymentHandler = new PaymentHandler();
        });

        function addNewPayment() {
            const paymentType = document.getElementById('payment_type').value;
            const amount = document.getElementById('payment_amount').value;
            
            if (!amount || parseFloat(amount) <= 0) {
                Swal.fire({
                    title: "Invalid Amount",
                    text: "Please enter a valid payment amount",
                    icon: "error"
                });
                return;
            }

            let paymentDetails = {
                type: paymentType,
                amount: amount
            };

            if (paymentType === 'card_payment') {
                const lastFourDigits = document.getElementById('card_last_four').value;
                const cardType = document.getElementById('card_type').value;
                
                if (!paymentHandler.validateCardDetails(lastFourDigits, cardType)) {
                    Swal.fire({
                        title: "Invalid Card Details",
                        text: "Please enter valid card details",
                        icon: "error"
                    });
                    return;
                }

                paymentDetails.cardDetails = {
                    lastFourDigits,
                    cardType
                };
            }

            paymentHandler.addPayment(paymentDetails);
            document.getElementById('payment_amount').value = '';
            if (paymentType === 'card_payment') {
                document.getElementById('card_last_four').value = '';
            }
        }

        function addCashPayment() {
            const paymentType = "cash_payment";
            const amount = '<?php echo $totalCashTendered; ?>';
            
            if (!amount || parseFloat(amount) <= 0) {
                return;
            }

            let paymentDetails = {
                type: paymentType,
                amount: amount
            };

            paymentHandler.addPayment(paymentDetails);
            document.getElementById('payment_amount').value = '';
            if (paymentType === 'card_payment') {
                document.getElementById('card_last_four').value = '';
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            addCashPayment();
        });

        // document.addEventListener('DOMContentLoaded', function() {
        //     // Get references to the input elements
        //     const netAmountInput = document.getElementById('net_amount');
        //     const transportFeeInput = document.getElementById('transport_fee');
        //     const paymentDueInput = document.getElementById('payment_due');
        //     const deductionAlert = document.getElementById('deduction-alert');

        //     // Initial values
        //     const initialTotalValue = parseFloat(netAmountInput.value.replace(/,/g, '')) || 0;
        //     const advancePayment = parseFloat(deductionAlert.textContent.replace(/[^\d.]/g, '')) || 0;

        //     // Function to format number with two decimal places and comma separators
        //     function formatNumberWithCommas(num) {
        //         return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        //     }

        //     // Function to calculate and update amounts
        //     function updatePaymentCalculations() {
        //         // Get current transport fee
        //         const transportFee = parseFloat(transportFeeInput.value) || 0;

        //         // Calculate new net amount (original total value + transport fee)
        //         const newNetAmount = initialTotalValue + transportFee;

        //         // Calculate new due amount (net amount - advance payment)
        //         const newDueAmount = Math.max(0, newNetAmount - advancePayment);

        //         // Update input fields with formatted values
        //         netAmountInput.value = formatNumberWithCommas(newNetAmount);
        //         paymentDueInput.value = formatNumberWithCommas(newDueAmount);
        //     }

        //     // Add event listener to transport fee input
        //     transportFeeInput.addEventListener('input', updatePaymentCalculations);
        // });
</script>

</html>