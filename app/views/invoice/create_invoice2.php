<!-- create_invoive2.php -->
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include '../../models/Database.php';
include '../../models/POS_Product.php';

session_start();
$user_name = $_SESSION['username'];
$user_role = $_SESSION['job_role'];
$user_branch = $_SESSION['store'];

if (!$user_name && !$user_branch) {
    header("Location: ../unauthorized/unauthorized_access.php");
    exit();
}

if ($user_role === 'admin') {
    $user_emblem = '<i class="fa-solid fa-user-tie" style="margin-left:10px"></i>';
} else {
    $user_emblem = '<i class="fa-solid fa-circle-user" style="margin-left:10px"></i>';
}

$product = new POS_PRODUCT($db_conn);

$results = $product->retrieveProduct('10001', '100006');

if (is_array($results)) {
    $prdName = $results['product_name'];
}


// function getCurrentDate() {
//     return date('Y-m-d');
// }
// $currentDate = getCurrentDate();
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Invoice</title>
    <!-- remastered-screen styles -->
    <link rel="stylesheet" href="./create-invoice.styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="../../assets/js/cart_handler.js" defer></script>
    <link rel="stylesheet" href="../../assets/notifier/style.css">
    </link>
    <script src="../../assets/notifier/index.var.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<style>
    /* return form css */
    .modal {
        display: none;
        position: fixed;
        z-index: 1005; /* Increased z-index */
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgb(0, 0, 0);
        background-color: rgba(0, 0, 0, 0.4);
    }

    .modal-content {
        background-color: rgb(255, 255, 255);
        margin: 4% auto;
        padding: 1px;
        border: 1px solid #888;
        width: 55%;
        height: 78%;
    }

    .close {
        color: red;
        float: right;
        /* font-size: 18px; */
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }

    /* barcode reader css */
    .barcode-reader-container {
        margin: 5px 0;
        width: 95%;
    }

    .barcode-reader-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 2px;
    }

    .barcode-reader-table>thead>tr>th {
        font-family: "Poppins", serif;
        font-weight: 500;
        text-align: center;
        background-color: #0a3981;
        color: white;
        padding: 1px 2px;
    }

    .barcode-reader-table>tbody>tr>td {
        padding: 1px 2px;
        font-family: "Poppins", serif;
        font-weight: 400;
        text-align: center;
        font-size: 0.9em;
        color: #2c3e50;
    }

    .barcode-reader-table>tbody>tr:hover>td {
        background-color: #dcdde1;
    }

    .barcode-reader-table>tbody>tr:nth-child(odd) {
        background-color: #dfe4ea;
    }

    .barcode-reader-table>tbody>tr:nth-child(even) {
        background-color: #f1f2f6;
    }

    .barcode-input,
    .barcode-product-name,
    .barcode-quantity,
    .barcode-our-price {
        width: 100%;
        padding: 2px;
        font-family: "Poppins", serif;
        font-size: 0.9em;
        text-align: center;
    }

    .barcode-input {
        border: 2px solid #2c3e50;
        border-radius: 4px;
    }

    .barcode-product-name,
    .barcode-quantity,
    .barcode-our-price {
        border: 2px solid #dfe4ea;
        background-color: #f9f9f9;
    }

    .barcode-tb-inp {
        padding: 2px;
        font-family: "Poppins", serif;
        font-size: 0.9em;
        text-align: center;
        border: none;
    }

    .eland-pos-input-cont {
        position: relative;
        width: 100%;
    }

    #search-customer {
        width: 100%;
        box-sizing: border-box;
    }

    .dropdown-list {
        position: absolute;
        top: calc(100% + 2px);
        left: 0;
        width: 100%;
        background: #fff;
        border: 1px solid #ccc;
        z-index: 1000;
        max-height: 150px;
        overflow-y: auto;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    }


    .dropdown-item {
        padding: 8px 12px;
        cursor: pointer;
    }

.dropdown-item:hover {
    background-color: #f0f0f0; 
}

.modal-container {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1005; /* Increased z-index */
  }
  
  .modal-content {
    background-color: white;
    border-radius: 5px;
    width: 39%;
    height: 90%;
    max-width: 1200px;
    max-height: 90vh;
    position: relative;
    overflow: hidden;
    padding: 10;
  }
  
  .close-modal {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 24px;
    cursor: pointer;
    z-index: 1007; /* One higher than modal content */
    color: #333;
    background: rgba(255, 255, 255, 0.7);
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  /* Added new styles to ensure elements stay on top of modals */
.bill-values-container-main {
    position: relative; 
    z-index: 1000;
}

.opt-btn {
    position: relative;
    z-index: 1000;
}

.opt-menu {
    z-index: 1000;
}

/* Updating modal z-index to be lower than our key elements */
.modal-container {
    z-index: 1005; /* Increased z-index */
}

.modal {
    z-index: 1005; /* Increased z-index */
}

.multiple-item-selector-modal,
.short-cut-keys-modal,
.bill-cancellation-modal,
.attendance-mark-modal,
.mes-unit-modal {
    z-index: 1005; /* Increased z-index for all modals */
}

/* Make sure modal content is above the overlay */
.modal-content,
.bc-form-area,
.mis-modal-area {
    position: relative;
    z-index: 1006; /* One higher than the modal background */
}

/* Close buttons should be above modal content */
.close,
.close-modal,
.bc-close-btn,
.mis-modal-close {
    z-index: 1007; /* One higher than modal content */
}
</style>

<body>
<?php include '../customers/add_customer.php';?>
    <!-- bill cancel confirmation -->
    <div class="bill-cancellation-modal" id="bill-cancellation-modal">
        <div class="bc-form-area">
            <button class="bc-close-btn" onclick="closeBcModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <input type="text" id="deleting_bill_id" value="null" hidden>
            <input type="text" id="gram-value-holder" value="null" hidden>
            <input type="text" id="mes-unit-name" value="null" hidden>
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

    <!-- attendance modal -->
    <div class="attendance-mark-modal">
        <div class="short_cut_heading">Employee Attendance</div>
        <input type="text" id="attendanceBarcodeInput" placeholder="Scan Employee Barcode" autofocus>
    </div>

    <!-- shortcut modal -->
    <div class="short-cut-keys-modal">
        <div class="short_cut_heading">Short Cut Keys</div>
        <div class="shortcut">
            <span>F1:</span> Create New Invoice
        </div>
        <div class="shortcut">
            <span>F2:</span> Hold Invoice
        </div>
        <div class="shortcut">
            <span>F3:</span> Select Customer
        </div>
        <div class="shortcut">
            <span>F5:</span> Wholesale/Retail Switch
        </div>
        <div class="shortcut">
            <span>F6:</span> Discount
        </div>
        <div class="shortcut">
            <span>F7:</span> Free Issue
        </div>
        <div class="shortcut">
            <span>F8:</span> Short Cut Displayer
        </div>
        <div class="shortcut">
            <span>F9:</span> Calculator
        </div>
        <div class="shortcut">
            <span>F10:</span> Advance Take & Give
        </div>
        <div class="shortcut">
            <span>F11:</span> Employee Attendance
        </div>
        <div class="shortcut">
            <span>Arrow Up/Down:</span> Go through cart items
        </div>
        <div class="shortcut">
            <span>ENTER:</span> Find item by barcode
        </div>
        <div class="shortcut">
            <span>INS:</span> Add item to cart
        </div>
        <div class="shortcut">
            <span>END:</span> Add item (when multiple items there)
        </div>
        <div class="shortcut">
            <span>HOME:</span>
            <p>Go to dashboard</p>
        </div>
        <div class="shortcut">
            <span>Number Pad +:</span> Go to checkout
        </div>
        <div class="shortcut">
            <span>ctrl + r -:</span>
            <p>Refund / Return</p>
        </div>
        <div class="shortcut">
            <span>ctrl + s -:</span>
            <p>Search product</p>
        </div>
        <div class="shortcut">
            <span>ctrl + b -:</span>
            <p>Focus on barcode field</p>
        </div>
    </div>

    <div class="multiple-item-selector-modal" id="iwb-modal">
        <div class="mis-modal-area">
            <button class="mis-modal-close" onclick="hideIwbModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <div class="iwb-search-field-cont">
                <input type="text" class="iwb-search-field" id="iwb-search-field" placeholder="Search by item name" onkeyup="searchProducts()">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>

            <table class="table item-cart-mistb" id="iwb-table">
                <thead class="thead-dark">
                    <tr>
                        <th>Stock ID</th>
                        <th>Product Information</th>
                        <th>MRP</th>
                        <th>Our Price</th>
                        <th></th>
                    </tr>
                </thead>

                <!-- tbody -->
                <tbody>

                </tbody>
            </table>
        </div>
    </div>

    <!-- multiple items with same barcode handler -->
    <div class="multiple-item-selector-modal" id="mis-modal">
        <div class="mis-modal-area">
            <button class="mis-modal-close" onclick="hideMisModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>

            <table class="table item-cart-mistb" id="mis-table">
                <thead class="thead-dark">
                    <tr>
                        <th>Stock ID</th>
                        <th>Product Information</th>
                        <th>Barcode</th>
                        <th>Price</th>
                    </tr>
                </thead>

                <!-- tbody -->
                <tbody>

                </tbody>
            </table>
        </div>
    </div>

    <div class="invoice-container-rmaster">
        <!-- header -->
        <div class="header-container-rmaster">
            <div class="text-partition-cont">
                <!-- first content row -->
                <div class="content-row first-row">
                    <!-- timer -->
                    <div class="timer-container">
                        <span id="current-date">November 21, 2024</span>
                        <span id="current-time">7:28:50 AM</span>
                    </div>

                    <!-- <img src="../../assets/images/cart.png" alt="inventory-pholder" class="header-img-rmaster"> -->
                    <!-- company-info -->
                    <div class="company-info">
                        <!-- <span class="heading-sinhala">එග්ලන්ඩ් සුපර්</span> -->
                        <span class="heading-english">RisiRasa Products</span>
                        <span class="company-motto">Delicious Treats</span>
                    </div>
                    <div id="header-image">
                        <img src="../../assets/images/cart.png" alt="Header Image" />
                    </div>
                    <div class="img-box">
                        <img alt="Product Image" id="selected-product-image" src="" />
                        <!-- <span class="placeholder">Product Image</span> -->
                    </div>

                </div>
                <h2 id="create-invoice-header">Create Invoice</H2>
                <!-- second content row -->
                <div class="content-row">
                    <!-- salesmain-info-ribbon -->
                    <div class="salesman-info-ribbon">
                        <!-- salesman -->
                        <div class="eland-pos-input-cont">
                            <label for="select-salesman" class="eland-input-label">salesman
                                <?php echo $user_emblem ?>
                            </label>
                            <select id="select-salesman" class="eland-input">
                                <option value="s1" selected><?php echo $user_name ?></option>
                            </select>
                        </div>
                        <!-- user -->
                        <div class="eland-pos-input-cont">
                            <label for="select-salesman" class="eland-input-label">user
                                <?php echo $user_emblem ?>
                            </label>
                            <input type="text" class="eland-input" id="current_user" value="<?php echo $user_name ?>" disabled>
                        </div>
                        <!-- location -->
                        <div class="eland-pos-input-cont">
                            <label for="select-salesman" class="eland-input-label">location</label>
                            <select id="select-salesman" class="eland-input">
                                <option value="s1" selected><?php echo $user_branch ?></option>
                            </select>
                        </div>
                        <!-- customer -->
                        <div class="eland-pos-input-cont">
                            <label for="select-customer" class="eland-input-label">Customer<button type="button" class="add-customer" data-toggle="modal" data-target="#addCustomerModal">+</button></label>
                            <input type="text" id="search-customer" class="eland-input" placeholder="Search customer...">
                            <!-- these are two important fields for advance payment handling feature. do not delete -->
                            <input type="text" id="customerIdAdvPay" hidden>
                            <ul id="customer-list" class="dropdown-list"></ul>
                        </div>

                        <!-- tr options-container -->
                        <div class="tr-opt-cont">
                            <!-- transaction type -->
                            <div class="transaction-type-selector">
                                <label class="tra-type-label">
                                    <input type="radio" class="tra-type-selector" id="tra_whole" name="customerType" value="retail" checked>
                                    <i class="fa-solid fa-bag-shopping"></i>
                                    Retail
                                </label>
                                <label class="tra-type-label">
                                    <input type="radio" class="tra-type-selector" id="tra_wholesale" name="customerType" value="wholesale">
                                    <i class="fa-solid fa-boxes-packing"></i>
                                    Wholesale
                                </label>
                            </div>

                            <div class="tr-opt-btn-cont">
                                <!-- credit limit button -->
                                <button class="eland-pos-button">
                                    <i class="fa-solid fa-credit-card"></i>
                                    credit limit
                                </button>
                                <!-- new invoice button -->
                                <button class="eland-pos-button" id="new-invoice-button" onclick="holdInvoice();">
                                    <i class="fa-solid fa-file-invoice-dollar"></i>
                                    new invoice
                                </button>
                            </div>
                        </div>



                    </div>
                </div>
            </div>
            <!-- Credit Limit Modal -->
            <div class="modal" id="creditLimitModal">
                <div class="modal-content" style="width: 400px; height: auto;">
                    <span class="close" onclick="closeCreditLimitModal()">&times;</span>
                    <h3 style="text-align: center; margin-bottom: 20px;">Customer Credit Information</h3>
                    <div class="credit-info-container">
                        <div class="credit-info-item">
                            <label>Credit Limit:</label>
                            <input type="text" id="credit-limit-value" disabled>
                        </div>
                        <div class="credit-info-item">
                            <label>Credit Balance:</label>
                            <input type="text" id="credit-balance-value" disabled>
                        </div>
                        <div class="credit-info-item">
                            <label>Remaining Credit:</label>
                            <input type="text" id="remaining-credit-value" disabled>
                        </div>
                    </div>
                </div>
            </div>

            <!-- measurement unit changer -->
             <div class="mes-unit-modal" id="mes-unit-modal">
                <table>
                    <tr onclick="setMeasurementUnit('litre')">
                        <td>Litre</td>
                    </tr>
                    <tr onclick="setMeasurementUnit('bottle')">
                        <td>Bottle</td>
                    </tr>
                    <tr onclick="setMeasurementUnit('kilogram')">
                        <td>Kilogram</td>
                    </tr>
                </table>
             </div>

            <style>
                .credit-info-container {
                    padding: 20px;
                }

                .credit-info-item {
                    margin-bottom: 15px;
                }

                .credit-info-item label {
                    display: inline-block;
                    width: 120px;
                    font-weight: bold;
                }

                .credit-info-item input {
                    width: 150px;
                    padding: 5px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                }
            </style>
            <script>
                // Credit Limit Modal Functions
                function openCreditLimitModal() {
                    const customerId = document.getElementById('customerIdAdvPay').value;
                    if (!customerId) {
                        alert('Please select a customer first');
                        return;
                    }

                    fetch('../../controllers/customer_controller.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                action: 'getCreditInfo',
                                id: customerId
                            }),
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                const creditLimit = parseFloat(data.data.credit_limit) || 0;
                                const creditBalance = parseFloat(data.data.credit_balance) || 0;
                                const remainingCredit = creditLimit - creditBalance;

                                document.getElementById('credit-limit-value').value = creditLimit.toFixed(2);
                                document.getElementById('credit-balance-value').value = creditBalance.toFixed(2);
                                document.getElementById('remaining-credit-value').value = remainingCredit.toFixed(2);

                                document.getElementById('creditLimitModal').style.display = 'block';
                            } else {
                                alert(data.message);
                            }
                        })
                        .catch(error => console.error('Error:', error));
                }

                function closeCreditLimitModal() {
                    document.getElementById('creditLimitModal').style.display = 'none';
                }

                document.querySelector('.eland-pos-button i.fa-credit-card').closest('button').addEventListener('click', openCreditLimitModal);

                // Close modal when clicking outside the modal content
                window.addEventListener('click', function(event) {
                    const modal = document.getElementById('creditLimitModal');
                    if (event.target === modal) {
                        closeCreditLimitModal();
                    }
                });

             
                document.querySelector('.eland-pos-button i.fa-credit-card').closest('button').addEventListener('click', openCreditLimitModal);
            </script>
            <!-- bill related fees -->
            <div class="bill-values-container-main">
                <div class="total-val-indicator-main">
                    <span class="total-val-label-main">Total Amount:</span>
                    <input style="color: #26de81;" type="text" class="total-val-input-main" id="total_amount" value="00.00" disabled>
                </div>
                <div class="total-val-indicator-main">
                    <span class="total-val-label-main">Cash Tendered:</span>
                    <input style="color: #26de81;" type="text" class="total-val-input-main" id="total_cash_tendered" value="00.00" onkeyup="balanceHandler()">
                </div>
                <div class="total-val-indicator-main">
                    <span class="total-val-label-main">Balance:</span>
                    <input type="text" class="total-val-input-main" id="total_balance_final" value="00.00" disabled>
                </div>
            </div>
        </div>



        <!-- item-cart and other components -->
        <div class="item-comp-container">
            <!-- held invoices -->
            <div class="held-invoice-container">
                <span class="held-invoice-heading">Hold Invoices</span>
                <div id="held-invoices"></div>
            </div>

            <!-- shopping cart table -->
            <div class="shopping-cart-container">
                <div class="operation-selector-ribbon">
                    <label class="tra-type-label">
                        <input type="radio" class="tra-type-selector" id="tra_invoice" name="invoiceType" value="retail" checked>
                        <i class="fa-solid fa-file-invoice"></i>
                        Invoice
                    </label>
                    <label class="tra-type-label">
                        <input type="radio" class="tra-type-selector" id="tra_quotation" name="invoiceType" value="wholesale">
                        <i class="fa-solid fa-money-bill"></i>
                        Wholesale
                    </label>
                    <label class="tra-type-label">
                        <input type="radio" class="tra-type-selector" id="tra_order" name="invoiceType" value="quotation">
                        <i class="fa-solid fa-scale-unbalanced-flip"></i>
                        Quotation
                    </label>
                    <label class="tra-type-label">
                        <input type="radio" class="tra-type-selector" id="tra_refund" name="invoiceType" value="refund">
                        <i class="fa-solid fa-registered"></i>
                        Refund
                    </label>
                    <label class="tra-type-label">
                        <input type="radio" class="tra-type-selector" id="tra_return" name="invoiceType" value="return">
                        <i class="fa-solid fa-right-left"></i>
                        Return
                    </label>
                </div>

                <div id="returnModal" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <iframe id="returnIframe" style="width:100%; height:100%; border:none;"></iframe>
                    </div>
                </div>



                <div class="barcode-reader-container">
                    <table class="table table-striped barcode-reader-table" id="barcode-reader-tb">
                        <thead class="thead-dark">
                            <tr>
                                <th>Barcode</th>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Our Price</th>
                                <th>Discount</th>
                                <th>Free Issue</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="barcode-reader-items">
                            <tr>
                                <td>
                                    <!-- onchange="getPossibleBarcodeCombinations('<?php echo $user_branch; ?>')" -->
                                    <input type="text" id="barcode-input" class="barcode-input" placeholder="Enter barcode" >
                                    <div id="suggestion-box"></div>
                                </td>
                                <td>
                                    <input type="text" id="product-name" class="barcode-product-name" disabled>
                                </td>
                                <td>
                                    <input type="number" id="quantity" class="barcode-quantity" value="1" oninput="updatePriceAndAddToCart()" onkeyup="itemPromotionCheck()">
                                </td>
                                <td>
                                    <input type="text" id="our-price" class="barcode-our-price">
                                </td>
                                <td>
                                    <input type="text" id="bcode_discount" class="barcode-tb-inp">
                                    <input type="text" id="bcode_discount_amount" class="barcode-tb-inp" value="0" hidden>
                                </td>
                                <td>
                                    <input type="text" id="bcode_fi" class="barcode-tb-inp">
                                </td>
                                <td>
                                    <button onclick="addToCartFromInput()">Add to Cart</button>
                                </td>
                            </tr>
                        </tbody>

                    </table>
                </div>

                <script>
                    function getSelectedInvoiceType() {
                        const selectedRadio = document.querySelector('input[name="invoiceType"]:checked');
                        return selectedRadio ? selectedRadio.value : null;
                    }
                    // Track the currently fetched product

                    // Add product to cart from the inputs
                    function addToCartFromInput() {
                        const mesUnit = document.getElementById("mes-unit-name");
                        const gramQtyHolder = document.getElementById('gram-value-holder')
                        const barcodeInput = document.getElementById("barcode-input").value.trim();
                        let quantity = parseFloat(document.getElementById("quantity").value) || 1;
                        if(mesUnit.value != "null"){
                            quantity = (parseFloat(gramQtyHolder.value) * quantity) / 1000
                        }
                        const discount = parseInt(document.getElementById("bcode_discount").value) || 0;
                        const free_issue = parseInt(document.getElementById("bcode_fi").value) || 0;
                        const priceValue = parseFloat(document.getElementById("our-price").value)

                        if (!barcodeInput) {
                            alert("Please enter a valid barcode.");
                            return;
                        }

                        // if (!currentProduct || currentProduct.barcode !== barcodeInput) {
                        //     fetchProductDetails(barcodeInput).then(() => {
                        //         if (currentProduct) {
                        //             processAddToCart(quantity, discount, free_issue, priceValue);
                        //         }
                        //     });
                        // } else {
                        //     processAddToCart(quantity, discount, free_issue, priceValue);
                        // }
                            processAddToCart(quantity, discount, free_issue, priceValue);
                    }

                    const handleInsertKeyPress = (products) => {
                        const rows = document.querySelectorAll("#mis-table tbody tr");
                        if (rows.length === 0) return;

                        const highlightedRow = rows[currentRowIndex];
                        const productId = highlightedRow.dataset.productId;
                        console.log(productId + "is pid");

                        const selectedProduct = products.find((product) => product.id == productId);
                        console.log(products);

                        if (selectedProduct) {
                            // selectProduct(selectedProduct);
                            updateProductUI(selectedProduct);
                            currentProduct = selectedProduct
                            hideMisModal();
                        } else {
                            let notifier = new AWN();
                            notifier.alert("data record not exist!");
                        }
                    };

                    document.addEventListener("keydown", (e) => {
                        const misModal = document.getElementById("mis-modal");
                        if (e.key === "End" && misModal.classList.contains("show-mis-modal")) {
                            handleInsertKeyPress(products);
                            currentRowIndex = 0;
                        }
                    });

                    // Helper function to process adding product to the cart
                    function processAddToCart(quantity, discount, freeIssueCount, priceValue) {
                        // if (!currentProduct) {
                        //     alert("Product details not found. Please fetch product details first.");
                        //     return;
                        // }

                        const unitPrice = parseFloat(priceValue) || 0;
                        const totalPrice = quantity * unitPrice
                        const discountAmount = totalPrice * (discount / 100)
                        const finalPrice = totalPrice - discountAmount

                        const cartItem = {
                            ...currentProduct,
                            quantity,
                            finalPrice,
                            discount,
                            freeIssueCount,
                            unitPrice
                        };

                        addToCart(cartItem); // Call the existing addToCart function
                    }

                    // Fetch product details based on the barcode (return a Promise)
                    // Global variable to store the selected product
                    let currentProduct = null;

                    // Fetch product details based on barcode and handle single/multiple products
                    function fetchProductDetails(barcode) {
                        let notifier = new AWN()
                        return new Promise((resolve, reject) => {
                            if (!barcode) {
                                notifier.warning('Please enter a valid barcode.')
                                reject(new Error("Invalid barcode."));
                                return;
                            }

                            fetch("fetch_product_static.php", {
                                    method: "POST",
                                    headers: {
                                        "Content-Type": "application/x-www-form-urlencoded",
                                    },
                                    body: `barcode=${encodeURIComponent(barcode)}`,
                                })
                                .then((response) => {
                                    if (!response.ok) {
                                        throw new Error(`HTTP error! Status: ${response.status}`);
                                    }
                                    return response.json();
                                })
                                .then((data) => {
                                    if (data.success && data.products && data.products.length > 0) {
                                        if (data.products.length === 1) {
                                            const product = data.products[0];
                                            currentProduct = product; // Set the current product
                                            if(currentProduct.unit === 'coc_oil(kg)'){
                                                showMesUnitModal();
                                            }else{
                                                updateProductUI(product); // Update the input fields
                                                resolve(); // Resolve the Promise
                                            }
                                        } else {
                                            console.log(data.products);
                                            currentProduct = null; // Reset current product
                                            showMisModal(data.products); // Show modal for product selection
                                            resolve(); // Still resolve, as modal is displayed
                                        }
                                    } else {
                                        alert("Product not found!");
                                        resetProductUI();
                                        reject(new Error("Product not found."));
                                    }
                                })
                                .catch((error) => {
                                    console.error("Error fetching product:", error);
                                    alert("An error occurred while fetching product details. Please try again.");
                                    reject(error);
                                });
                        });
                    }


                    // Update the UI with fetched product details
                    // Update the UI with fetched product details
                    function updateProductUI(product) {

                        let currentPrice = 0;
                        const invoiceType = getSelectedInvoiceType()
                        if (invoiceType === 'retail') {
                            currentPrice = product.our_price
                        } else if (invoiceType === 'wholesale') {
                            currentPrice = product.wholesale_price
                        } else {
                            currentPrice = product.max_retail_price
                        }

                        document.getElementById("product-name").value = product.product_name;
                        document.getElementById("quantity").value = 1; // Default quantity
                        document.getElementById("our-price").value = parseFloat(currentPrice).toFixed(2);
                        document.getElementById("bcode_discount").value = 0;
                        document.getElementById("bcode_fi").value = 0;
                    }

                    function updateProductUIEnhanced(product) {
                        let currentPrice = 0;
                        const invoiceType = getSelectedInvoiceType()
                        if(invoiceType === 'retail'){
                            currentPrice = product.our_price
                        }
                        else if(invoiceType === 'wholesale'){
                            currentPrice = product.wholesale_price  
                        }
                        else{
                            currentPrice = product.max_retail_price
                        }

                        document.getElementById("product-name").value = product.product_name;
                        document.getElementById("barcode-input").value = product.barcode;
                        document.getElementById("quantity").value = 1; // Default quantity
                        document.getElementById("our-price").value = parseFloat(currentPrice).toFixed(2);
                        document.getElementById("bcode_discount").value = 0;
                        document.getElementById("bcode_fi").value = 0;
                    }

                    // Reset the UI when product details are invalid or missing
                    function resetProductUI() {
                        document.getElementById("product-name").value = "";
                        document.getElementById("quantity").value = "";
                        document.getElementById("our-price").value = "";
                        document.getElementById("bcode_discount").value = "";
                        document.getElementById("bcode_fi").value = "";
                    }

                    const loadCartState = () => {
                        const cart = JSON.parse(localStorage.getItem("shopping_cart")) || [];
                        return cart;
                    };

                    document.addEventListener('DOMContentLoaded', () => {
                        clearCartState()
                        const cart = loadCartState();
                        if (cart.length > 0) {
                            cart.forEach((item) => {
                                addToCart(item); // Add each item to the cart
                            });
                        }
                    })

                    function addToCart(product) {
                        let cart = loadCartState()
                        if (!Array.isArray(cart)) {
                            cart = [];
                        }

                        let notifier = new AWN();
                        const mesUnit = document.getElementById("mes-unit-name");
                        const invoiceType = getSelectedInvoiceType()
                        const discountAmountHolder = document.getElementById("bcode_discount_amount");
                        const discountAmount = parseFloat(discountAmountHolder.value)

                        if (parseInt(product.available_stock) < 0) {
                            notifier.warning("Remaining stock are below than zero!")
                        }

                        const quantityInput = document.getElementById("quantity");
                        const quantity = parseFloat(quantityInput.value) || 1; // Get quantity from input, default to 1 if invalid
                        let finalPrice = 0;
                        let isPromotion = false;

                        if (invoiceType === "retail") {
                            const promoStartDate = new Date(product.start_date);
                            const promoEndDate = new Date(product.end_date);
                            const currentDate = new Date();

                            promoStartDate.setHours(0, 0, 0, 0);
                            promoEndDate.setHours(23, 59, 59, 999);
                            currentDate.setHours(0, 0, 0, 0);

                            if (currentDate >= promoStartDate && currentDate <= promoEndDate) {
                                isPromotion = true;
                                finalPrice = product.deal_price;
                            } else {
                                isPromotion = false;
                                finalPrice = product.unitPrice || product.our_price;
                            }
                        } else if (invoiceType === "wholesale") {
                            finalPrice = product.unitPrice
                            isPromotion = false
                        } else {
                            notifier.alert("Feature not available")
                            return
                        }
                        const wholesalePrice = document.getElementById("alt-wholesale");
                        const mrp = document.getElementById("alt-mrp");
                        const remStock = document.getElementById("rem-stock");
                        const itemName = document.getElementById("info-item");
                        const cartTableBody = document.querySelector("#pos-cart-tb tbody");
                        const cartItemCount = Array.from(cartTableBody.rows).length;
                        const rowId = `${product.stock_id}_${product.itemcode}`;
                        const existingRow = Array.from(cartTableBody.rows).find(
                            (row) => row.dataset.rowId === rowId
                        );
                        const selectedProductImage = document.getElementById("selected-product-image");
                        const altOurPrice = document.getElementById("alt-our-price");
                        const scgPrice = document.getElementById("alt-scg-price");
                        const mrpPrice = document.getElementById("alt-mrprice");

                        // Update product details
                        wholesalePrice.textContent = parseFloat(product.wholesale_price).toFixed(2);
                        mrp.textContent = parseFloat(product.cost_price).toFixed(2);
                        remStock.textContent = parseInt(product.available_stock)
                        itemName.textContent = product.product_name;
                        selectedProductImage.src = `../inventory/${product.image_path}`;
                        altOurPrice.textContent = parseFloat(product.our_price).toFixed(2);
                        scgPrice.textContent = parseFloat(product.super_customer_price).toFixed(2);
                        mrpPrice.textContent = parseFloat(product.max_retail_price).toFixed(2);

                        if (existingRow) {
                            const qtyCell = existingRow.querySelector(`.qty_${rowId}`);
                            const subtotalCell = existingRow.querySelector(`.subtotal_${rowId}`);
                            const newQty = parseFloat(qtyCell.value) + quantity; // Increment existing quantity
                            qtyCell.value = newQty;
                            subtotalCell.textContent = (newQty * finalPrice).toFixed(2);
                        } else {
                            // Add new row for the product
                            const newRow = document.createElement("tr");
                            newRow.dataset.rowId = rowId;
                            newRow.dataset.remainingStock = product.available_stock;
                            newRow.dataset.productRealId = product.id;
                            newRow.dataset.ourPrice = finalPrice;
                            newRow.dataset.wholesalePrice = product.wholesale_price;
                            newRow.setAttribute(
                                "onclick",
                                `alternatePricePopulator(
        ${parseFloat(product.wholesale_price).toFixed(2)},
        ${parseFloat(product.cost_price).toFixed(2)},
        ${parseFloat(product.available_stock).toFixed(2)},
        '${product.product_name}',
        '${product.image_path}',
        ${parseFloat(product.our_price).toFixed(2)},
        ${parseFloat(product.super_customer_price).toFixed(2)},
        ${parseFloat(product.max_retail_price).toFixed(2)},
      )`
                            );


                            const grossTotal = parseFloat(quantity * finalPrice)
                            let subtotal = 0;
                            if (product.discount > 0) {
                                const discountFee = parseFloat(grossTotal * (product.discount / 100))
                                subtotal = parseFloat(grossTotal - discountFee)
                            } else {
                                subtotal = grossTotal
                            }

                            newRow.innerHTML = `
            <td class="item_no_${rowId}">${cartItemCount + 1}</td>
            <td class="stock_id_${rowId}">${product.stock_id}</td>
            <td class="item_id_${rowId}">${product.barcode}</td>
            <td style="text-transform: capitalize;" class="product_name_${rowId}">${product.product_name} ${mesUnit.value != "null" ? ` (${mesUnit.value})` : ""}</td>
            <td class="mrp_${rowId}">${parseFloat(
      product.max_retail_price
    ).toFixed(2)}</td>
            <td class="unit_price_${rowId}">${parseFloat(finalPrice).toFixed(
      2
    )} ${isPromotion ? '<i class="fa-solid fa-certificate"></i>' : ""} </td>
            <td><input type="number" class="qty-indicator qty_${rowId}" value="${product.quantity || quantity}" min="1" oninput="updateCartTotal()" readonly/></td>
            <td class="discount_${rowId}">
                <input type="text" class="disc-percentage" id="discount_val_${rowId}" value="${product.discount}" onkeyup="updateCartTotal()" readonly/>
                <input type="text" class="disc-percentage" id="discount_amount_val_${rowId}" value="${product.discountAmount || parseFloat(discountAmountHolder.value) || 0}" hidden/>
            </td>
            <td class="free_${rowId}">${product.freeIssueCount}</td>
            <td class="subtotal_${rowId}">${subtotal.toFixed(2)}</td>
     <td><i class="fa-solid fa-delete-left" onclick="removeFromCart('${rowId}')" style="color: crimson;"></i></td>`;
                            cartTableBody.appendChild(newRow);
                        }

                        const currentRow = Array.from(cartTableBody.rows).find(
                            (row) => row.dataset.rowId === rowId
                        );
                        const currentQty = parseFloat(document.querySelector(`.qty_${rowId}`).value)
                        const existingItem = cart.find((item) => item.id === product.id);
                        if (existingItem) {
                            existingItem.quantity;
                        } else {
                            cart.push({
                                ...product,
                                quantity: currentQty || 1,
                                discountAmount: discountAmount
                            });
                        }
                        saveCartState(cart)

                        if (parseFloat(discountAmountHolder.value) > 0) {
                            notifier.info(`Discount: ${parseFloat(discountAmountHolder.value)} off!`)
                            discountAmountHolder.value = 0
                        }

                        itemCounter();
                        updateCartTotal();
                        if (product.freeIssueCount > 0) {
                            notifier.info(`Promotion: ${product.freeIssueCount} free items!`)
                        }
                        mesUnit.value = "null"
                    }

                    // Automatically update the price and add to cart when quantity changes
                    function updatePriceAndAddToCart() {
                        const quantityInput = document.getElementById("quantity");
                        const unitPrice = parseFloat(document.getElementById("our-price").value) || currentProduct.our_price;
                        const quantity = parseFloat(quantityInput.value) || 1;

                        if (!currentProduct) {
                            alert("Please fetch product details first.");
                            return;
                        }

                        // Calculate the final price based on the quantity
                        const finalPrice = quantity * unitPrice;

                        // this line looks unnecessary. so commented it.
                        // document.getElementById("our-price").value = finalPrice.toFixed(2);
                    }





                    // Example existing addToCart function (should be customized as needed)



                    // Example removeFromCart function (should be customized as needed)
                    function removeFromCart(rowId) {
                        const row = document.querySelector(`[data-row-id='${rowId}']`);
                        if (row) row.remove();
                    }
                </script>




                <!-- table -->
                <div class="cart-table-cont">
                    <table class="table table-striped table-hover item-cart" id="pos-cart-tb">
                        <thead class="thead-dark">
                            <tr>
                                <th>No.</th>
                                <th>Stock ID</th>
                                <th>Scan Barcode</th>
                                <th>Product Information</th>
                                <th>Unit Price</th>
                                <th>Our Price</th>
                                <th>QTY</th>
                                <th>Discount %</th>
                                <th>Fr.Issue</th>
                                <th>Total Amount</th>
                                <th style="width: 30px;"></th>
                            </tr>
                        </thead>

                        <!-- tbody -->
                        <tbody id="pos-cart-items">

                        </tbody>
                    </table>
                </div>

                <div class="option-menu-container">
                    <!-- option menu -->
                    <div class="opt-menu" id="opt-menu">
                        <button onclick="location.href='../../views/invoice/day_end_balance.php';" class="opt-child-btn">
                            <i class="fa-solid fa-cloud-moon"></i>
                        </button>
                        <button onclick="location.href='../../controllers/logout.php';" class="opt-child-btn">
                            <i class="fa-solid fa-power-off"></i>
                        </button>
                        <button onclick="goToPaymentScreen2()" class="opt-child-btn">
                            <i class="fa-solid fa-cash-register"></i>
                        </button>
                        <button onclick="location.href='create_invoive2.php';" class="opt-child-btn">
                            <i class="fa-solid fa-boxes-stacked"></i>
                        </button>
                        <button onclick="location.href='create_invoive2.php';" class="opt-child-btn">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>

                    <!-- menu expand btn -->
                    <button class="opt-btn" id="opt-btn">
                        <i class="fa-solid fa-list"></i>
                    </button>

                    <!-- cart item counter -->
                    <div class="cart-item-counter">
                        <span class="item-count-indicator" id="item-count-indicator">0</span>
                        <i class="fa-solid fa-bag-shopping"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- cart value and trasaction related details -->
        <div class="cart-footer">

            <!-- function btn container -->
            <div class="function-btn-cont">
                <button onclick="location.href='fetch_bill_record.php';" class="button-style">
                    <i class="fa-solid fa-file-excel"></i>
                    <span>Export</span>
                </button>
                <button onclick="openCalc()" class="button-style">
                    <i class="fa-solid fa-calculator"></i>
                    <span>Calculator</span>
                </button>
                <button onclick="location.href='advance_list.php';" class="button-style">
                    <i class="fa-solid fa-credit-card"></i>
                    <span>advance Pay</span>
                </button>
                <button onclick="markEmployeeAttendance()" class="button-style">
                    <i class="fa-solid fa-clipboard-user"></i>
                    <span>Attendance</span>
                </button>
                <button class="button-style">
                    <i class="fa-solid fa-print"></i>
                    <span>Print</span>
                </button>

                <button class="bill-mode-cont" onclick="showIwbModal()">
                    Search Item <i style="margin-left: 10px;" class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>

            <!-- bill-value container -->
            <div class="bill-values-container">
                <div class="total-val-indicator">
                    <span class="total-val-label">Outstandings:</span>
                    <input type="text" class="total-val-input" id="total_outstandings" value="00.00" disabled>
                </div>
                <div class="total-val-indicator">
                    <span class="total-val-label">Total Balance:</span>
                    <input type="text" class="total-val-input" id="balance_outstanding" value="00.00" disabled>
                </div>
                <div class="total-val-indicator">
                    <span class="total-val-label">Total Discount:</span>
                    <input type="text" class="total-val-input" id="total_discount" value="00.00" onkeyup="discountHandlerFinal()">
                </div>
            </div>

            <!-- bill-value container -->
            <div class="bill-values-container">
                <div class="total-val-indicator">
                    <span class="total-val-label">Sub Total:</span>
                    <input type="text" class="total-val-input" id="sub_total" value="00.00" disabled>
                </div>
                <div class="total-val-indicator">
                    <span class="total-val-label">Return Amount:</span>
                    <input type="text" class="total-val-input" id="total_return_amount" value="00.00" disabled>
                </div>
                <div class="total-val-indicator-small">
                    <span class="total-val-label">Next Bill:</span>
                    <input type="text" class="total-val-input" id="next_bill_no" value="Bill/00014" disabled>
                </div>
            </div>

            <!-- item-description-container -->
            <div class="item-desc-cont">
                <div class="item-info">
                    <span class="info-label">Item Name:</span>
                    <span class="info-value" id="info-item"></span>

                    <input type="text" id="stockId" hidden>
                    <input type="text" id="itemId" hidden>

                    <div class="alternate-price-cont">
                        <div class="alt-price-box alt-price-box2">
                            <span class="alt-price-label">MRP</span>
                            <span class="alt-price-val-visible" id="alt-mrprice">00.00</span>
                            <!-- <button class="alt-toggle-btn" id="alt-wholesale-btn" onclick="alternateWholesale()">show</button> -->
                        </div>
                        <div class="alt-price-box alt-price-box2">
                            <span class="alt-price-label">Our Price</span>
                            <span class="alt-price-val-visible" id="alt-our-price">00.00</span>
                            <!-- <button class="alt-toggle-btn" id="alt-wholesale-btn" onclick="alternateWholesale()">show</button> -->
                        </div>
                        <div class="alt-price-box">
                            <span class="alt-price-label">wholesale</span>
                            <span class="alt-price-val" id="alt-wholesale">00.00</span>
                            <button class="alt-toggle-btn" id="alt-wholesale-btn" onclick="alternateWholesale()">show</button>
                        </div>
                        <div class="alt-price-box">
                            <span class="alt-price-label">SCG Price</span>
                            <span class="alt-price-val" id="alt-scg-price">00.00</span>
                            <button class="alt-toggle-btn" id="alt-scg-btn" onclick="alternateSCGP()">show</button>
                        </div>
                        <div class="alt-price-box">
                            <span class="alt-price-label">cost</span>
                            <span class="alt-price-val" id="alt-mrp">00.00</span>
                            <button class="alt-toggle-btn" id="alt-mrp-btn" onclick="alternateMRP()">show</button>
                        </div>
                        <div class="alt-price-box">
                            <span class="alt-price-label">rem. stock</span>
                            <span class="alt-price-val" id="rem-stock">00.00</span>
                            <button class="alt-toggle-btn" id="rem-stock-price-btn" onclick="alternateRemainingStock()">show</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Refund Modal Structure -->
        <div id="refundModal" class="modal">
            <div class="refund-modal-content">
                <span class="close" id="refund-close-button">&times;</span>
                <h2>Cash Return</h2>
                <div class="refund-search-section">
                    <div id="">
                        <label>Search By Bill ID : </label>
                        <input class="refund-bill-id-search-input" type="text" placeholder="Type Bill ID">
                        <button class="refund-bill-id-search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div>
                        <label>Filter By Item Code : </label>
                        <input type="text" class="refund-bill-id-filter-input" placeholder="Type Item Code">
                    </div>

                </div>
                <table class="refund-table">
                    <thead>
                        <tr>
                            <th>Stock ID</th>
                            <th>Item Code</th>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Discount(%)</th>
                            <th>Subtotal</th>
                            <th>Return Qty</th>
                            <th>Return Cash Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Dynamic Data Will Be Added Here -->
                    </tbody>
                </table>
                <div class="refund-return-section">
                    <label>Refund Amount:</label>
                    <input type="text" id="return-cash-amount" readonly>
                    <button class="cash-return-btn">Cash Return</button>
                </div>
            </div>
        </div>


        <!-- Calculator Modal -->
        <div id="calculatorModal" class="calculator">
            <div id="calculatorHeader">Calculator</div>
            <input type="text" id="calcInput" disabled>
            <div>
                <button onclick="appendCalc('1')">1</button>
                <button onclick="appendCalc('2')">2</button>
                <button onclick="appendCalc('3')">3</button>
                <button onclick="appendCalc('+')">+</button>
            </div>
            <div>
                <button onclick="appendCalc('4')">4</button>
                <button onclick="appendCalc('5')">5</button>
                <button onclick="appendCalc('6')">6</button>
                <button onclick="appendCalc('-')">-</button>
            </div>
            <div>
                <button onclick="appendCalc('7')">7</button>
                <button onclick="appendCalc('8')">8</button>
                <button onclick="appendCalc('9')">9</button>
                <button onclick="appendCalc('*')">*</button>
            </div>
            <div>
                <button onclick="appendCalc('0')">0</button>
                <button onclick="clearCalc()">C</button>
                <button onclick="calculate()">=</button>
                <button onclick="appendCalc('/')">/</button>
            </div>
            <button onclick="closeCalc()" id="cal-close-btn">Close</button>
        </div>

        <div id="payment-modal" class="modal-container" style="display: none;">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <iframe id="payment-iframe" src="" frameborder="0" style="width: 100%; height: 100%;"></iframe>
            </div>
        </div>

</body>

<script>
    const optionsBtn = document.getElementById('opt-btn')
    const optionsMenu = document.getElementById('opt-menu')

    optionsBtn.addEventListener('click', () => {
        optionsMenu.classList.toggle('expand')
    })

    // document.addEventListener("DOMContentLoaded", () => {
    //     const tableBody = document.getElementById("hold-invoice-rows");
    //     for (let i = 0; i < 8; i++) {
    //         const row = document.createElement("tr");
    //         const cell = document.createElement("td");
    //         cell.textContent = "1";
    //         row.appendChild(cell);
    //         tableBody.appendChild(row);
    //     }
    // });

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

    document.addEventListener('DOMContentLoaded', () => {
        updateDateTime();
        setInterval(updateDateTime, 1000);
    });
</script>

<script>
    document.getElementById('search-customer').addEventListener('input', function() {
        const customerIdHolder = document.getElementById('customerIdAdvPay');
        const query = this.value;

        if (query.length < 2) {
            // Clear dropdown if query is too short
            document.getElementById('customer-list').innerHTML = '';
            return;
        }

        // Perform an AJAX request
        fetch('../../controllers/customer_controller.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'searchCustomerByPhoneNumber',
                    query
                }),
            })
            .then((response) => response.json())
            .then((data) => {
                const dropdown = document.getElementById('customer-list');
                dropdown.innerHTML = ''; // Clear existing items

                if (data.status === 'success') {
                    data.data.forEach((customer) => {
                        const li = document.createElement('li');
                        li.textContent = customer.name;
                        li.setAttribute('data-id', customer.id);
                        li.classList.add('dropdown-item');

                        // Add click event to select the customer
                        li.addEventListener('click', function() {
                            document.getElementById('search-customer').value = customer.name;
                            customerIdHolder.value = customer.id
                            dropdown.innerHTML = ''; // Clear dropdown after selection
                        });

                        dropdown.appendChild(li);
                    });
                } else {
                    const li = document.createElement('li');
                    li.textContent = 'No customers found';
                    li.classList.add('dropdown-item');
                    dropdown.appendChild(li);
                }
            })
            .catch((error) => console.error('Error:', error));
    });

    document.addEventListener("keydown", function(event) {
        if (event.code === "NumpadAdd") {
            goToPaymentScreen2()
        }
    });

    document.addEventListener("keydown", function(event) {
        if (event.code === "Home") {
            window.location.href = "../dashboard/index.php";
        }
    });



    document.addEventListener("DOMContentLoaded", function() {
        // Attach click event listener to delete icons
        const deleteIcons = document.querySelectorAll('.held-invoice-delete-icon');
        deleteIcons.forEach(icon => {
            icon.addEventListener('click', function() {
                const billNo = this.getAttribute('data-bill-no');
                openBcModal(billNo)
                // if (confirm("Are you sure you want to delete this held invoice?")) {
                //     // Send the request to delete the bill
                //     fetch('delete_held_invoice.php', {
                //             method: 'POST',
                //             headers: {
                //                 'Content-Type': 'application/x-www-form-urlencoded',
                //             },
                //             body: `bill_no=${encodeURIComponent(billNo)}`,
                //         })
                //         .then(response => response.json())
                //         .then(data => {
                //             if (data.success) {
                //                 alert('Invoice deleted successfully');
                //                 // Optionally, remove the item from the DOM
                //                 this.closest('.invoice-item').remove();
                //             } else {
                //                 alert('Failed to delete invoice');
                //             }
                //         })
                //         .catch(error => console.error('Error deleting invoice:', error));
                // }
            });
        });
    });
</script>
<!-- return popup for -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const returnRadio = document.getElementById("tra_return");
        const modal = document.getElementById("returnModal");
        const iframe = document.getElementById("returnIframe");
        const closeButton = document.querySelector(".close");

        let lastSelectedRadio = null;

        // Function to open modal
        function openReturnModal() {
            iframe.src = "return_form.php"; // Always reload the form
            modal.style.display = "block";
        }

        // Event listener for the Return radio button
        returnRadio.addEventListener("change", function() {
            if (this.checked) {
                openReturnModal();
            }
        });

        // Close button event listener
        closeButton.addEventListener("click", function() {
            modal.style.display = "none";
            lastSelectedRadio = returnRadio.checked ? returnRadio : null; // Save last selection
        });

        // Close modal when clicking outside the content
        window.onclick = function(event) {
            if (event.target === modal) {
                modal.style.display = "none";
                lastSelectedRadio = returnRadio.checked ? returnRadio : null; // Save last selection
            }
        };

        // Reopen modal if "Return" is still selected when clicking it again
        returnRadio.addEventListener("click", function() {
            if (this === lastSelectedRadio) {
                openReturnModal();
            }
        });

        // Reset lastSelectedRadio when another radio is selected
        document.querySelectorAll('input[name="invoiceType"]').forEach((radio) => {
            radio.addEventListener("change", function() {
                if (this !== returnRadio) {
                    lastSelectedRadio = null; // Reset last selection
                }
            });
        });
    });


    const openBcModal = (billId) => {
        document.getElementById('deleting_bill_id').value = billId
        const bcModal = document.getElementById('bill-cancellation-modal')
        bcModal.classList.add('display-bc-modal')
    }

    const closeBcModal = () => {
        const bcModal = document.getElementById('bill-cancellation-modal')
        bcModal.classList.remove('display-bc-modal')
    }

    const createDeleteBillRecord = async () => {
        let notifier = new AWN()
        const reason = document.getElementById('cancellation_reason').value.trim()
        const bill_id = document.getElementById('deleting_bill_id').value.trim()
        const cancelled_by = '<?php echo $user_name ?>'

        if (bill_id === "null") {
            notifier.warning("Hold bill id not parsed!")
            return
        }

        const billAmount = await fetchTotalValueOfHeldBill(bill_id)

        console.log("Final Bill Amount:", billAmount); // Debugging
        if (parseFloat(billAmount) < 1) { // Ensure it's a valid number
            notifier.alert("Total value missing");
            return;
        }

        try {
            const response = await fetch('./create_delete_bill_record.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    bill_id: bill_id,
                    reason: reason + " (Hold Bill)",
                    bill_amount: billAmount,
                    cancelled_by: cancelled_by
                })
            });

            const result = await response.json();
            if (result.success) {
                fetch('delete_held_invoice.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `bill_no=${encodeURIComponent(bill_id)}`,
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // alert('Invoice deleted successfully');
                            // Optionally, remove the item from the DOM
                            // this.closest('.invoice-item').remove();
                        } else {
                            alert('Failed to delete invoice');
                        }
                    })
                    .catch(error => console.error('Error deleting invoice:', error));
                document.getElementById('deleting_bill_id').value = "null"
                closeBcModal()
                window.location.reload()
                notifier.success(result.message)
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

var measurementConversions = [];
const fetchMeasurementConversions = () => {
    let notifier = new AWN()
    fetch('fetch_measurement_convs.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                measurementConversions = data.data;
                console.log('Measurement conversions loaded successfully:', measurementConversions);
            } else {
                console.error('Error fetching measurement conversions:', data.message);
            }
        })
        .catch(error => {
            console.error('Error fetching measurement conversions:', error);
        });
}

document.addEventListener('DOMContentLoaded', fetchMeasurementConversions);

const measurementConverter = (itemCode, selectedMesUnit) => {
    const gramQtyHolder = document.getElementById('gram-value-holder')
    let notifier = new AWN()
    measurementConversions.forEach((record) => {
        if(record.item_code == itemCode){
            switch(selectedMesUnit){
                case 'litre':
                    gramQtyHolder.value = record.selling_litre
                    break;
                case 'bottle':
                    gramQtyHolder.value = record.selling_bottle
                    break;
                case 'kilogram':
                    gramQtyHolder.value = record.selling_kilo
                    break;
                default:
                    notifier.alert('invalid measure unit passed!')
                    return
            }
        }
    })
}
</script>


</html>