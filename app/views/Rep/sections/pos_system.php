<?php
// POS system logic
require_once '../../../../config/databade.php';
session_start();

// Get rep_id from session
$rep_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;

// Generate invoice number
function generateInvoiceNumber($conn) {
    $prefix = 'INV';
    $date = date('Ymd');
    
    // Get the last used number for today
    $stmt = $conn->prepare("SELECT MAX(invoice_number) as max_num FROM pos_sales WHERE invoice_number LIKE ?");
    $search_pattern = $prefix . $date . '%';
    $stmt->bind_param("s", $search_pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['max_num']) {
        // Extract the sequence number and increment
        $last_num = intval(substr($row['max_num'], -4));
        $next_num = $last_num + 1;
    } else {
        // No invoices yet today, start with 1
        $next_num = 1;
    }
    
    // Format with leading zeros (4 digits)
    $sequence = str_pad($next_num, 4, '0', STR_PAD_LEFT);
    return $prefix . $date . $sequence;
}

$invoiceNumber = generateInvoiceNumber($conn);
?>

<div class="section-card fade-transition" id="pos-section">
    <div class="section-header">
        <i class="fas fa-cash-register"></i> POS System
    </div>
    <div class="section-body">
        <a href="#" class="return-link" id="return-from-pos">
            <i class="fas fa-chevron-left"></i> Return to Dashboard
        </a>
        
        <!-- Customer Search Section (Initially Hidden, Show on Toggle) -->
        <div class="card mb-4" id="customer-search-card" style="display: none;">
            <div class="card-body">
                <div class="form-group position-relative">
                    <label for="customer-phone-search"><i class="fas fa-search mr-1"></i> Search Customer by Phone</label>
                    <input type="text" class="form-control" id="customer-phone-search" placeholder="Enter phone number" autocomplete="off">
                    <div id="customer-phone-suggestions" class="phone-suggestions"></div>
                </div>
                
                <div id="pos-customer-search-results" class="mt-3" style="display: none;">
                    <h6>Select a Customer</h6>
                    <div class="list-group" id="pos-customer-results-list"></div>
                </div>
                
                <div id="pos-no-customer-found" class="alert alert-info mt-3" style="display: none;">
                    No customer found with this phone number.
                    <button type="button" class="btn btn-sm btn-outline-primary ml-2" data-toggle="modal" data-target="#add-customer-modal">
                        Add New Customer
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Selected Customer Info Section -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <button class="btn btn-outline-primary" id="toggle-customer-search">
                    <i class="fas fa-user-plus"></i> Select Customer
                </button>
            </div>
            <div id="selected-pos-customer-info" style="display: none;">
                <span class="badge badge-pill badge-primary mr-2" id="customer-badge">
                    <i class="fas fa-user mr-1"></i> <span id="pos-customer-name-display"></span>
                </span>
                <button class="btn btn-sm btn-outline-secondary" id="clear-customer">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div>
                <span class="text-muted">Invoice #: </span>
                <span class="font-weight-bold"><?php echo $invoiceNumber; ?></span>
            </div>
        </div>

        <!-- Product Search Section -->
        <div class="card mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Add Products</h5>
                <div class="form-inline">
                    <div class="input-group">
                        <input type="text" class="form-control form-control-sm" id="barcode-input" placeholder="Scan barcode" autofocus>
                        <div class="input-group-append">
                            <button class="btn btn-sm btn-outline-secondary" type="button" id="search-barcode">
                                <i class="fas fa-barcode"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="form-group position-relative">
                    <label for="product-search"><i class="fas fa-search mr-1"></i> Search Product by Name</label>
                    <input type="text" class="form-control" id="product-search" placeholder="Type product name" autocomplete="off">
                    <div id="product-suggestions" class="product-suggestions"></div>
                </div>
                
                <form id="add-product-form" class="mt-3">
                    <div class="form-row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="selected-product">Selected Product</label>
                                <input type="text" class="form-control" id="selected-product" readonly>
                                <input type="hidden" id="lorry-stock-id" name="lorry_stock_id">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="available-qty">Available Qty</label>
                                <input type="text" class="form-control" id="available-qty" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="unit-price">Unit Price</label>
                                <input type="text" class="form-control" id="unit-price" readonly>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="quantity">Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quantity" min="1" value="1">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="free-qty">Free Qty</label>
                                <input type="number" class="form-control" id="free-qty" min="0" value="0">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="discount-percent">Disc %</label>
                                <input type="number" class="form-control" id="discount-percent" min="0" max="100" value="0">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="discount-amount">Disc Amount</label>
                                <input type="number" class="form-control" id="discount-amount" min="0" value="0" step="0.01">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="line-total">Line Total</label>
                                <input type="text" class="form-control" id="line-total" readonly>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Products Table -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Cart Items</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0" id="pos-items-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Free</th>
                                <th>Discount</th>
                                <th>Total</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="pos-items-body">
                            <tr id="empty-cart">
                                <td colspan="7" class="text-center">No items added yet</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Total and Payment Section -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Payment Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment-method">Payment Method <span class="text-danger">*</span></label>
                                    <select class="form-control" id="payment-method">
                                        <option value="cash">Cash</option>
                                        <option value="card">Card</option>
                                        <option value="credit_card">Credit Card</option>
                                        <option value="credit">Credit</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="paid-amount">Amount Paid</label>
                                    <input type="number" class="form-control" id="paid-amount" min="0" value="0">
                                </div>
                            </div>
                        </div>
                        
                        <button type="button" class="btn btn-success btn-lg btn-block" id="complete-sale">
                            <i class="fas fa-check-circle mr-1"></i> Complete Sale
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>Rs. <span id="subtotal-amount">0.00</span></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Discount:</span>
                            <span>Rs. <span id="total-discount">0.00</span></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 font-weight-bold">
                            <span>Grand Total:</span>
                            <span>Rs. <span id="grand-total">0.00</span></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Amount Paid:</span>
                            <span>Rs. <span id="display-paid-amount">0.00</span></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Change:</span>
                            <span>Rs. <span id="change-amount">0.00</span></span>
                        </div>
                        <div class="d-flex justify-content-between font-weight-bold text-danger" id="credit-amount-row" style="display: none;">
                            <span>Credit Amount:</span>
                            <span>Rs. <span id="credit-amount">0.00</span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Duplicate Barcode Modal -->
<div class="modal fade" id="duplicate-barcode-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Multiple Products Found</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Multiple products were found with this barcode. Please select the correct one:</p>
                <div class="list-group" id="duplicate-products-list"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Confirmation Modal with Enhanced Options -->
<div class="modal fade" id="payment-confirmation-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Payment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Order Summary</h6>
                            </div>
                            <div class="card-body">
                                <div class="summary-info">
                                    <div class="row mb-2">
                                        <div class="col-6 font-weight-bold">Invoice Number:</div>
                                        <div class="col-6"><?php echo $invoiceNumber; ?></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-6 font-weight-bold">Customer:</div>
                                        <div class="col-6" id="summary-customer-name">No customer selected</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-6 font-weight-bold">Total Items:</div>
                                        <div class="col-6" id="summary-total-items">0</div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-6 font-weight-bold">Subtotal:</div>
                                        <div class="col-6">Rs. <span id="summary-subtotal">0.00</span></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-6 font-weight-bold">Discount:</div>
                                        <div class="col-6">Rs. <span id="summary-discount">0.00</span></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-6 font-weight-bold">Grand Total:</div>
                                        <div class="col-6">Rs. <span id="summary-grand-total">0.00</span></div>
                                    </div>
                                    <!-- Advance Amount Row (Hidden by default) -->
                                    <div class="row mb-2" id="summary-advance-row" style="display: none;">
                                        <div class="col-6 font-weight-bold text-success">Available Advance:</div>
                                        <div class="col-6 text-success">Rs. <span id="summary-advance-amount">0.00</span></div>
                                    </div>
                                    <!-- Used Advance Row (Hidden by default) -->
                                    <div class="row mb-2" id="summary-used-advance-row" style="display: none;">
                                        <div class="col-6 font-weight-bold text-info">Advance Used:</div>
                                        <div class="col-6 text-info">Rs. <span id="summary-used-advance">0.00</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Payment Options</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="modal-payment-method">Payment Method</label>
                                    <select class="form-control" id="modal-payment-method">
                                        <option value="cash">Cash</option>
                                        <option value="card">Card</option>
                                        <option value="credit_card">Credit Card</option>
                                        <option value="cheque">Cheque</option>
                                        <option value="credit">Credit</option>
                                    </select>
                                </div>
                                
                                <!-- Cheque Number Field (initially hidden) -->
                                <div class="form-group" id="cheque-number-group" style="display: none;">
                                    <label for="cheque-number">Cheque Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="cheque-number" placeholder="Enter cheque number">
                                </div>
                                
                                <div class="form-group">
                                    <label for="modal-paid-amount">Amount Paid</label>
                                    <input type="number" class="form-control" id="modal-paid-amount" min="0" step="0.01">
                                </div>
                                
                                <div class="form-group" id="modal-change-amount-group">
                                    <label>Change Amount</label>
                                    <div class="form-control bg-light">Rs. <span id="modal-change-amount">0.00</span></div>
                                </div>
                                <div class="form-group" id="modal-credit-amount-group" style="display: none;">
                                    <label>Credit Amount</label>
                                    <div class="form-control bg-light">Rs. <span id="modal-credit-amount">0.00</span></div>
                                </div>
                                <!-- Advance Payment Checkbox -->
                                <div class="form-check mb-3" id="use-advance-check-container" style="display: none;">
                                    <input class="form-check-input" type="checkbox" id="use-advance-payment">
                                    <label class="form-check-label" for="use-advance-payment">
                                        Use available advance (Rs. <span id="available-advance-amount">0.00</span>)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="print-invoice" checked>
                                    <label class="form-check-label" for="print-invoice">
                                        Print Invoice
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirm-payment">Confirm & Complete</button>
            </div>
        </div>
    </div>
</div>

<!-- Include Customer Modal for adding customers -->
<?php require_once 'modals/customer_modal.php'; ?>

<!-- Include SweetAlert2 for better notifications -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* Suggestions Dropdown Styles */
.phone-suggestions, .product-suggestions {
    position: absolute;
    z-index: 1000;
    width: 100%;
    max-height: 200px;
    overflow-y: auto;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 0 0 4px 4px;
    box-shadow: 0 5px 10px rgba(0,0,0,0.1);
    display: none;
}
.phone-suggestion-item, .product-suggestion-item {
    padding: 10px 15px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
}
.phone-suggestion-item:hover, .product-suggestion-item:hover {
    background-color: #f8f9fa;
}
.phone-suggestion-item:last-child, .product-suggestion-item:last-child {
    border-bottom: none;
}
.customer-name, .product-name {
    font-weight: 600;
}
.customer-phone, .product-details {
    color: #6c757d;
    font-size: 0.85em;
    display: block;
}

/* POS Items Table Styles */
#pos-items-table th {
    font-size: 0.85rem;
    vertical-align: middle;
}
#pos-items-table td {
    vertical-align: middle;
}
.item-action-btn {
    padding: 0.2rem 0.5rem;
    font-size: 0.8rem;
}

/* Customer Badge Styles */
#customer-badge {
    font-size: 0.9rem;
    padding: 0.5rem 0.75rem;
}
</style>

<script>
// Define posData before it's used anywhere in the code
$(document).ready(function() {
    // Initialize posData object with default values
    window.posData = {
        items: [],                    // Cart items array
        subtotal: 0,                  // Cart subtotal
        discountAmount: 0,            // Discount amount
        grandTotal: 0,                // Grand total (after discount)
        customerId: null,             // Selected customer ID
        customerName: null,           // Selected customer name
        advanceAmount: 0,             // Available advance amount for customer
        advanceUsed: 0,               // Amount of advance used in current transaction
        paymentMethod: 'cash',        // Default payment method
        paidAmount: 0,                // Amount paid by customer
        changeAmount: 0,              // Change to be returned to customer
        creditAmount: 0,              // Amount on credit
        invoiceNumber: generateInvoiceNumber() // Current invoice number
    };
    
    // Function to generate a new invoice number
    function generateInvoiceNumber() {
        const prefix = 'INV';
        const date = new Date().toISOString().slice(0, 10).replace(/-/g, '');
        const random = Math.floor(1000 + Math.random() * 9000);
        return `${prefix}${date}${random}`;
    }
    
    // Now you can safely use posData throughout your code
    console.log("POS system initialized with invoice number:", posData.invoiceNumber);
    
    // ...existing code...
});

// If you have any standalone functions that use posData, make sure they are within the document ready function
// or check if posData exists before using it:

function updatePaymentSummary() {
    if (typeof window.posData === 'undefined') {
        console.error("posData is not defined. Initializing with defaults.");
        window.posData = {
            items: [],
            subtotal: 0,
            discountAmount: 0,
            grandTotal: 0,
            advanceUsed: 0,
            paymentMethod: 'cash',
            paidAmount: 0,
            changeAmount: 0,
            creditAmount: 0
        };
    }
    
    // Now use posData safely
    const paymentMethod = $('#payment-method').val();
    const remainingToPay = Math.max(0, posData.grandTotal - posData.advanceUsed);
    
    // ...rest of function code...
}

function updateTotals() {
    if (typeof window.posData === 'undefined') {
        console.error("posData is not defined. Initializing with defaults.");
        window.posData = {
            items: [],
            subtotal: 0,
            discountAmount: 0,
            grandTotal: 0,
            advanceUsed: 0
        };
    }
    
    // Calculate totals based on cart items
    let subtotal = 0;
    posData.items.forEach(item => {
        subtotal += parseFloat(item.subtotal);
    });
    
    // ...rest of function code...
}

// Main POS System JavaScript
$(document).ready(function() {
    let posData = {
        invoice: '<?php echo $invoiceNumber; ?>',
        customerId: null,
        customerName: null,
        items: [],
        subtotal: 0,
        totalDiscount: 0,
        grandTotal: 0,
        paidAmount: 0,
        changeAmount: 0,
        creditAmount: 0,
        paymentMethod: 'cash',
        advanceAmount: 0,
        advanceUsed: 0
    };
    
    // Toggle customer search section
    $('#toggle-customer-search').click(function() {
        $('#customer-search-card').slideToggle(300);
    });
    
    // Clear selected customer
    $('#clear-customer').click(function() {
        clearSelectedCustomer();
    });
    
    // Monitor customer phone search input
    $('#customer-phone-search').on('input', function() {
        const searchTerm = $(this).val().trim();
        
        if (searchTerm.length < 2) {
            $('#customer-phone-suggestions').hide();
            return;
        }
        
        // AJAX call to fetch phone suggestions
        $.ajax({
            url: 'process/get_phone_suggestions.php',
            type: 'GET',
            data: { term: searchTerm },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.customers.length > 0) {
                    displayPhoneSuggestions(response.customers);
                } else {
                    $('#customer-phone-suggestions').hide();
                }
            }
        });
    });
    
    // Display customer phone suggestions
    function displayPhoneSuggestions(customers) {
        let html = '';
        
        customers.forEach(function(customer) {
            html += `<div class="phone-suggestion-item" data-phone="${customer.telephone}">
                <span class="customer-name">${customer.name}</span>
                <span class="customer-phone">${customer.telephone}</span>
            </div>`;
        });
        
        $('#customer-phone-suggestions').html(html).show();
        
        // Handle suggestion click
        $('.phone-suggestion-item').click(function() {
            const phone = $(this).data('phone');
            $('#customer-phone-search').val(phone);
            $('#customer-phone-suggestions').hide();
            searchCustomerByPhone(phone);
        });
    }
    
    // Search for customer by phone
    function searchCustomerByPhone(phone) {
        $.ajax({
            url: 'process/search_customers_by_phone.php',
            type: 'GET',
            data: { phone: phone },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.customers.length > 0) {
                    displayCustomerSearchResults(response.customers);
                } else {
                    $('#pos-customer-search-results').hide();
                    $('#pos-no-customer-found').show();
                }
            }
        });
    }
    
    // Display customer search results
    function displayCustomerSearchResults(customers) {
        let html = '';
        
        customers.forEach(function(customer) {
            html += `<a href="#" class="list-group-item list-group-item-action pos-customer-result" 
                data-id="${customer.id}" 
                data-name="${customer.name}" 
                data-phone="${customer.telephone}" 
                data-nic="${customer.nic}">
                <div class="d-flex justify-content-between">
                    <div>
                        <strong>${customer.name}</strong>
                        <small class="d-block text-muted">${customer.telephone} | ${customer.nic}</small>
                    </div>
                </div>
            </a>`;
        });
        
        $('#pos-customer-results-list').html(html);
        $('#pos-customer-search-results').show();
        $('#pos-no-customer-found').hide();
        
        // Handle customer selection
        $('.pos-customer-result').click(function(e) {
            e.preventDefault();
            selectCustomer($(this));
        });
    }
    
    // Select a customer with enhanced advance amount check
    function selectCustomer(element) {
        posData.customerId = element.data('id');
        posData.customerName = element.data('name');
        
        // Update UI
        $('#pos-customer-name-display').text(posData.customerName);
        $('#selected-pos-customer-info').show();
        
        // Hide search section
        $('#customer-search-card').slideUp(300);
        
        // Summary update
        $('#summary-customer-name').text(posData.customerName);
        
        // Check if customer has advance amount
        if (posData.customerId) {
            // AJAX call to check customer advance amount
            $.ajax({
                url: 'process/check_customer_balance.php',
                type: 'GET',
                data: { customer_id: posData.customerId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        posData.advanceAmount = response.advance_amount;
                        
                        // Update UI if there's advance amount
                        if (posData.advanceAmount > 0) {
                            // Show badge indicating advance amount
                            $('#customer-badge').append('<span class="badge badge-success ml-2">Adv: Rs. ' + posData.advanceAmount.toFixed(2) + '</span>');
                        }
                    }
                }
            });
        }
    }
    
    // Clear selected customer
    function clearSelectedCustomer() {
        posData.customerId = null;
        posData.customerName = null;
        
        // Update UI
        $('#selected-pos-customer-info').hide();
        
        // Summary update
        $('#summary-customer-name').text('No customer selected');
    }
    
    // Product search by name
    $('#product-search').on('input', function() {
        const searchTerm = $(this).val().trim();
        
        if (searchTerm.length < 2) {
            $('#product-suggestions').hide();
            return;
        }
        
        // AJAX call to search products in lorry stock
        $.ajax({
            url: 'process/search_lorry_stock.php',
            type: 'GET',
            data: { term: searchTerm },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.products.length > 0) {
                    displayProductSuggestions(response.products);
                } else {
                    $('#product-suggestions').hide();
                }
            }
        });
    });
    
    // Display product suggestions
    function displayProductSuggestions(products) {
        let html = '';
        
        products.forEach(function(product) {
            html += `<div class="product-suggestion-item" 
                data-id="${product.id}" 
                data-name="${product.product_name}" 
                data-price="${product.unit_price}" 
                data-qty="${product.quantity}">
                <span class="product-name">${product.product_name}</span>
                <span class="product-details">Rs. ${parseFloat(product.unit_price).toFixed(2)} | Available: ${product.quantity}</span>
            </div>`;
        });
        
        $('#product-suggestions').html(html).show();
        
        // Handle suggestion click
        $('.product-suggestion-item').click(function() {
            selectProduct($(this));
        });
    }
    
    // Barcode search
    $('#barcode-input, #search-barcode').on('keypress click', function(e) {
        if ((e.type === 'keypress' && e.which === 13) || e.type === 'click') {
            e.preventDefault();
            
            const barcode = $('#barcode-input').val().trim();
            
            if (!barcode) return;
            
            // AJAX call to search products by barcode
            $.ajax({
                url: 'process/search_lorry_stock_by_barcode.php',
                type: 'GET',
                data: { barcode: barcode },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        if (response.products.length === 1) {
                            // Single product found - select it
                            const product = response.products[0];
                            selectProductById(product.id);
                        } else if (response.products.length > 1) {
                            // Multiple products - show modal
                            showDuplicateProductsModal(response.products);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Product Not Found',
                                text: 'No product found with this barcode.'
                            });
                        }
                    }
                }
            });
            
            // Clear the barcode input for next scan
            $('#barcode-input').val('').focus();
        }
    });
    
    // Show duplicate products modal
    function showDuplicateProductsModal(products) {
        let html = '';
        
        products.forEach(function(product) {
            html += `<a href="#" class="list-group-item list-group-item-action duplicate-product-item" data-id="${product.id}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${product.product_name}</strong>
                        <small class="d-block text-muted">Rs. ${parseFloat(product.unit_price).toFixed(2)}</small>
                    </div>
                    <span class="badge badge-primary">Qty: ${product.quantity}</span>
                </div>
            </a>`;
        });
        
        $('#duplicate-products-list').html(html);
        $('#duplicate-barcode-modal').modal('show');
        
        // Handle product selection from modal
        $('.duplicate-product-item').click(function(e) {
            e.preventDefault();
            
            const productId = $(this).data('id');
            selectProductById(productId);
            
            $('#duplicate-barcode-modal').modal('hide');
        });
    }
    
    // Select product by ID
    function selectProductById(productId) {
        $.ajax({
            url: 'process/get_lorry_stock_product.php',
            type: 'GET',
            data: { id: productId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Display product info in form
                    $('#selected-product').val(response.product.product_name);
                    $('#lorry-stock-id').val(response.product.id);
                    $('#available-qty').val(response.product.quantity);
                    $('#unit-price').val(parseFloat(response.product.unit_price).toFixed(2));
                    
                    // Reset other form fields
                    $('#quantity').val(1);
                    $('#free-qty').val(0);
                    $('#discount-percent').val(0);
                    $('#discount-amount').val(0);
                    
                    // Calculate and update line total
                    calculateLineTotal();
                    
                    // Focus on quantity field
                    $('#quantity').focus().select();
                }
            }
        });
    }
    
    // Select product from suggestion
    function selectProduct(element) {
        const id = element.data('id');
        const name = element.data('name');
        const price = parseFloat(element.data('price'));
        const availableQty = element.data('qty');
        
        // Update form
        $('#selected-product').val(name);
        $('#lorry-stock-id').val(id);
        $('#available-qty').val(availableQty);
        $('#unit-price').val(price.toFixed(2));
        $('#product-search').val('');
        $('#product-suggestions').hide();
        
        // Reset other form fields
        $('#quantity').val(1);
        $('#free-qty').val(0);
        $('#discount-percent').val(0);
        $('#discount-amount').val(0);
        
        // Calculate line total
        calculateLineTotal();
        
        // Focus on quantity field
        $('#quantity').focus().select();
    }
    
    // Calculate line total
    function calculateLineTotal() {
        const quantity = parseInt($('#quantity').val()) || 0;
        const unitPrice = parseFloat($('#unit-price').val()) || 0;
        const discountPercent = parseFloat($('#discount-percent').val()) || 0;
        let discountAmount = parseFloat($('#discount-amount').val()) || 0;
        
        // Calculate subtotal before discount
        const subtotal = quantity * unitPrice;
        
        // Handle percentage discount
        if (discountPercent > 0) {
            discountAmount = (subtotal * discountPercent / 100);
            $('#discount-amount').val(discountAmount.toFixed(2));
        }
        
        // Calculate final total
        const total = Math.max(0, subtotal - discountAmount);
        
        // Update line total
        $('#line-total').val(total.toFixed(2));
    }
    
    // Update calculations when quantity, discount percent, or discount amount changes
    $('#quantity, #discount-percent, #discount-amount').on('input', function() {
        const field = $(this).attr('id');
        const availableQty = parseInt($('#available-qty').val()) || 0;
        
        // Validate quantity
        if (field === 'quantity') {
            const quantity = parseInt($(this).val()) || 0;
            if (quantity > availableQty) {
                $(this).val(availableQty);
                Swal.fire({
                    icon: 'warning',
                    title: 'Insufficient Stock',
                    text: `Only ${availableQty} units available in stock.`
                });
            }
        }
        
        // If discount amount is changed, reset discount percent
        if (field === 'discount-amount') {
            $('#discount-percent').val(0);
        }
        
        calculateLineTotal();
    });
    
    // Add product to cart
    $('#add-product-form').submit(function(e) {
        e.preventDefault();
        
        const stockId = $('#lorry-stock-id').val();
        const productName = $('#selected-product').val();
        const unitPrice = parseFloat($('#unit-price').val()) || 0;
        const quantity = parseInt($('#quantity').val()) || 0;
        const freeQty = parseInt($('#free-qty').val()) || 0;
        const discountPercent = parseFloat($('#discount-percent').val()) || 0;
        const discountAmount = parseFloat($('#discount-amount').val()) || 0;
        const lineTotal = parseFloat($('#line-total').val()) || 0;
        const availableQty = parseInt($('#available-qty').val()) || 0;
        
        // Validate inputs
        if (!stockId || !productName) {
            Swal.fire({
                icon: 'error',
                title: 'No Product Selected',
                text: 'Please select a product to add to cart.'
            });
            return;
        }
        
        if (quantity <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Quantity',
                text: 'Please enter a valid quantity.'
            });
            return;
        }
        
        if (quantity > availableQty) {
            Swal.fire({
                icon: 'error',
                title: 'Insufficient Stock',
                text: `Only ${availableQty} units available in stock.`
            });
            return;
        }
        
        if (quantity + freeQty > availableQty) {
            Swal.fire({
                icon: 'error',
                title: 'Insufficient Stock for Free Items',
                text: `Cannot add ${freeQty} free items. Total quantity exceeds available stock.`
            });
            return;
        }
        
        // Check if product already exists in cart
        const existingItemIndex = posData.items.findIndex(item => item.stockId === stockId);
        
        if (existingItemIndex >= 0) {
            // Update existing item
            const existingItem = posData.items[existingItemIndex];
            const totalQty = existingItem.quantity + existingItem.freeQty + quantity + freeQty;
            
            if (totalQty > availableQty) {
                Swal.fire({
                    icon: 'error',
                    title: 'Insufficient Stock',
                    text: `Cannot add more items. Total quantity would exceed available stock.`
                });
                return;
            }
            
            // Update the item
            existingItem.quantity += quantity;
            existingItem.freeQty += freeQty;
            existingItem.discountPercent = discountPercent;
            existingItem.discountAmount += discountAmount;
            existingItem.subtotal += lineTotal;
        } else {
            // Add new item to cart
            posData.items.push({
                stockId: stockId,
                productName: productName,
                unitPrice: unitPrice,
                quantity: quantity,
                freeQty: freeQty,
                discountPercent: discountPercent,
                discountAmount: discountAmount,
                subtotal: lineTotal
            });
        }
        
        // Update cart UI
        updateCartUI();
        
        // Reset product form
        $('#selected-product').val('');
        $('#lorry-stock-id').val('');
        $('#available-qty').val('');
        $('#unit-price').val('');
        $('#quantity').val(1);
        $('#free-qty').val(0);
        $('#discount-percent').val(0);
        $('#discount-amount').val(0);
        $('#line-total').val('');
        
        // Focus on barcode input for next scan
        $('#barcode-input').focus();
    });
    
    // Remove item from cart
    $(document).on('click', '.remove-item', function() {
        const index = $(this).data('index');
        posData.items.splice(index, 1);
        updateCartUI();
    });
    
    // Update cart UI and calculations
    function updateCartUI() {
        if (posData.items.length === 0) {
            // Empty cart
            $('#pos-items-body').html('<tr id="empty-cart"><td colspan="7" class="text-center">No items added yet</td>');
            
            // Reset totals
            posData.subtotal = 0;
            posData.totalDiscount = 0;
            posData.grandTotal = 0;
        } else {
            // Build cart items HTML
            let html = '';
            let subtotal = 0;
            let totalDiscount = 0;
            
            posData.items.forEach((item, index) => {
                subtotal += (item.quantity * item.unitPrice);
                totalDiscount += item.discountAmount;
                
                html += `<tr>
                    <td>${item.productName}</td>
                    <td>Rs. ${item.unitPrice.toFixed(2)}</td>
                    <td>${item.quantity}</td>
                    <td>${item.freeQty}</td>
                    <td>
                        ${item.discountPercent > 0 ? `${item.discountPercent}% / ` : ''}
                        Rs. ${item.discountAmount.toFixed(2)}
                    </td>
                    <td>Rs. ${item.subtotal.toFixed(2)}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger remove-item" data-index="${index}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            });
            
            // Update cart
            $('#pos-items-body').html(html);
            
            // Update totals
            posData.subtotal = subtotal;
            posData.totalDiscount = totalDiscount;
            posData.grandTotal = subtotal - totalDiscount;
        }
        
        // Update summary
        $('#subtotal-amount').text(posData.subtotal.toFixed(2));
        $('#total-discount').text(posData.totalDiscount.toFixed(2));
        $('#grand-total').text(posData.grandTotal.toFixed(2));
        
        // Update payment summary
        updatePaymentSummary();
        
        // Update confirmation modal summary
        $('#summary-total-items').text(posData.items.length);
        $('#summary-subtotal').text(posData.subtotal.toFixed(2));
        $('#summary-discount').text(posData.totalDiscount.toFixed(2));
        $('#summary-grand-total').text(posData.grandTotal.toFixed(2));
    }
    
    // Handle paid amount changes
    $('#paid-amount').on('input', function() {
        posData.paidAmount = parseFloat($(this).val()) || 0;
        updatePaymentSummary();
    });
    
    // Handle payment method changes
    $('#payment-method').change(function() {
        posData.paymentMethod = $(this).val();
        
        // If credit selected, automatically set paid amount to 0
        if (posData.paymentMethod === 'credit') {
            posData.paidAmount = 0;
            $('#paid-amount').val(0);
        }
        
        updatePaymentSummary();
    });
    
    // Update payment summary (paid amount, change, credit amount)
    function updatePaymentSummary() {
        if (posData.paidAmount >= posData.grandTotal) {
            // Paid in full or excess
            posData.changeAmount = posData.paidAmount - posData.grandTotal;
            posData.creditAmount = 0;
            $('#credit-amount-row').hide();
            $('#summary-credit-row').hide();
        } else {
            // Partial payment (credit)
            posData.changeAmount = 0;
            posData.creditAmount = posData.grandTotal - posData.paidAmount;
            $('#credit-amount-row').show();
            $('#credit-amount').text(posData.creditAmount.toFixed(2));
            
            $('#summary-credit-row').show();
            $('#summary-credit-amount').text(posData.creditAmount.toFixed(2));
        }
        
        // Update UI
        $('#display-paid-amount').text(posData.paidAmount.toFixed(2));
        $('#change-amount').text(posData.changeAmount.toFixed(2));
        
        // Update confirmation modal
        $('#summary-payment-method').text($('#payment-method option:selected').text());
        $('#summary-amount-paid').text(posData.paidAmount.toFixed(2));
        $('#summary-change').text(posData.changeAmount.toFixed(2));
    }
    
    // Complete sale button click with enhanced payment modal
    $('#complete-sale').click(function() {
        // Validate cart
        if (posData.items.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Empty Cart',
                text: 'Please add at least one product to the cart.'
            });
            return;
        }
        
        // Check payment method and customer for credit
        if (posData.paymentMethod === 'credit' && !posData.customerId) {
            Swal.fire({
                icon: 'error',
                title: 'Customer Required',
                text: 'Please select a customer for credit sales.'
            });
            return;
        }
        
        // Reset advance usage
        posData.advanceUsed = 0;
        
        // Set the values in the payment modal
        $('#modal-payment-method').val(posData.paymentMethod);
        $('#modal-paid-amount').val(posData.grandTotal.toFixed(2));
        
        // If customer has advance, show the option to use it
        if (posData.customerId && posData.advanceAmount > 0) {
            $('#use-advance-check-container').show();
            $('#available-advance-amount').text(posData.advanceAmount.toFixed(2));
            $('#summary-advance-row').show();
            $('#summary-advance-amount').text(posData.advanceAmount.toFixed(2));
        } else {
            $('#use-advance-check-container').hide();
            $('#summary-advance-row').hide();
        }
        
        // Update payment calculations in the modal
        updateModalPaymentCalculations();
        
        // Show confirmation modal
        $('#payment-confirmation-modal').modal('show');
    });
    
    // Handle paid amount changes in the modal
    $('#modal-paid-amount').on('input', function() {
        updateModalPaymentCalculations();
    });
    
    // Handle payment method changes in the modal
    $('#modal-payment-method').change(function() {
        posData.paymentMethod = $(this).val();
        
        // Show/hide cheque number field based on payment method
        if (posData.paymentMethod === 'cheque') {
            $('#cheque-number-group').show();
            
            // Focus on cheque number field when shown
            setTimeout(function() {
                $('#cheque-number').focus();
            }, 100);
        } else {
            $('#cheque-number-group').hide();
        }
        
        // If credit selected, automatically set paid amount to 0
        if (posData.paymentMethod === 'credit') {
            $('#modal-paid-amount').val(0);
            $('#use-advance-check-container').hide();
        } else if (posData.customerId && posData.advanceAmount > 0) {
            $('#use-advance-check-container').show();
        }
        
        updateModalPaymentCalculations();
    });
    
    // Handle advance payment checkbox
    $('#use-advance-payment').change(function() {
        // Don't automatically set the paid amount - just recalculate
        updateModalPaymentCalculations();
        
        // Suggest a default amount but don't force it - keep field editable
        if ($(this).is(':checked')) {
            // If advance is checked, suggest remaining amount (if any)
            const remaining = Math.max(0, posData.grandTotal - posData.advanceUsed);
            // Only suggest, don't set - user can still type
            $('#modal-paid-amount').attr('placeholder', remaining.toFixed(2));
        } else {
            // If advance is unchecked, suggest full amount
            $('#modal-paid-amount').attr('placeholder', posData.grandTotal.toFixed(2));
        }
    });
    
    // Update payment calculations in the modal
    function updateModalPaymentCalculations() {
        // Get current values
        let paidAmount = parseFloat($('#modal-paid-amount').val()) || 0;
        let remainingTotal = posData.grandTotal;
        let useAdvance = $('#use-advance-payment').is(':checked');
        
        // Calculate advance usage if checkbox is checked
        if (useAdvance && posData.advanceAmount > 0) {
            // Determine how much advance to use (up to the available amount or remaining total)
            posData.advanceUsed = Math.min(posData.advanceAmount, remainingTotal);
            remainingTotal -= posData.advanceUsed;
            
            // Show advance usage in summary
            $('#summary-used-advance-row').show();
            $('#summary-used-advance').text(posData.advanceUsed.toFixed(2));
            
            // Don't automatically set the paid amount - keep it as user entered
            // This allows users to type additional payment amounts
        } else {
            posData.advanceUsed = 0;
            $('#summary-used-advance-row').hide();
        }
        
        // Calculate change or credit amount
        if (paidAmount + posData.advanceUsed >= posData.grandTotal) {
            // Paid in full (combination of advance and payment)
            posData.changeAmount = (paidAmount + posData.advanceUsed) - posData.grandTotal;
            posData.creditAmount = 0;
            
            // Update UI
            $('#modal-change-amount-group').show();
            $('#modal-credit-amount-group').hide();
            $('#modal-change-amount').text(posData.changeAmount.toFixed(2));
        } else {
            // Partial payment (credit)
            posData.changeAmount = 0;
            posData.creditAmount = posData.grandTotal - (paidAmount + posData.advanceUsed);
            
            // Update UI
            $('#modal-change-amount-group').hide();
            $('#modal-credit-amount-group').show();
            $('#modal-credit-amount').text(posData.creditAmount.toFixed(2));
        }
        
        // Store paid amount
        posData.paidAmount = paidAmount;
    }
    
    // Confirm payment button click with enhanced processing
    $('#confirm-payment').click(function() {
        // Update payment method from modal
        posData.paymentMethod = $('#modal-payment-method').val();
        
        // Validate cheque number if payment method is cheque
        if (posData.paymentMethod === 'cheque') {
            const chequeNumber = $('#cheque-number').val().trim();
            if (!chequeNumber) {
                Swal.fire({
                    icon: 'error',
                    title: 'Cheque Number Required',
                    text: 'Please enter the cheque number to continue.'
                });
                return;
            }
            posData.chequeNumber = chequeNumber;
        } else {
            posData.chequeNumber = null; // Reset cheque number if not cheque payment
        }
        
        // Prepare data for submission
        const saleData = {
            invoice_number: posData.invoice,
            customer_id: posData.customerId,
            customer_name: posData.customerName,
            items: posData.items,
            subtotal: posData.subtotal,
            discount_amount: posData.totalDiscount,
            net_amount: posData.grandTotal,
            payment_method: posData.paymentMethod,
            paid_amount: posData.paidAmount,
            change_amount: posData.changeAmount,
            credit_amount: posData.creditAmount,
            advance_used: posData.advanceUsed,
            cheque_number: posData.chequeNumber,
            print_invoice: $('#print-invoice').is(':checked') ? 1 : 0
        };
        
        console.log('Submitting sale data:', saleData); // Debug log
        
        // Show loading indicator
        $('#confirm-payment').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        // Send data to server
        $.ajax({
            url: 'process/save_pos_sale.php',
            type: 'POST',
            data: JSON.stringify(saleData),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                console.log('Server response:', response); // Add debug log for response
                
                // Close modal
                $('#payment-confirmation-modal').modal('hide');
                
                if (response.success) {
                    // Print invoice if requested
                    if (saleData.print_invoice) {
                        // Add a small delay to ensure data is saved before printing
                        setTimeout(function() {
                            window.open(`../invoice/pos_rep_invoice.php?invoice=${response.invoice_number}`, '_blank');
                        }, 500);
                    }
                    
                    // Update the invoice number display with the new number from server
                    if (response.next_invoice) {
                        // Update in posData
                        posData.invoice = response.next_invoice;
                        
                        // Update invoice display in the UI
                        $('span.text-muted:contains("Invoice #:")').next().text(response.next_invoice);
                    }
                    
                    // Reset the POS system for the next sale
                    resetPOSForNextSale();
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Sale Completed',
                        text: 'The sale has been successfully processed.'
                    });
                    
                    // Focus on barcode input for next sale
                    $('#barcode-input').focus();
                    
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'An error occurred while processing the sale.'
                    });
                }
                
                // Reset button state
                $('#confirm-payment').prop('disabled', false).html('Confirm & Complete');
            },
            error: function() {
                // Close modal
                $('#payment-confirmation-modal').modal('hide');
                
                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    text: 'Could not connect to the server. Please try again.'
                });
                
                // Reset button state
                $('#confirm-payment').prop('disabled', false).html('Confirm & Complete');
            }
        });
    });
    
    // Simple function to reset the POS system for a new sale
    function resetPOSForNextSale() {
        // Clear cart items
        posData.items = [];
        $('#pos-items-body').html('<tr id="empty-cart"><td colspan="7" class="text-center">No items added yet</td></tr>');
        
        // Reset totals
        posData.subtotal = 0;
        posData.totalDiscount = 0;
        posData.grandTotal = 0;
        
        // Update summary displays
        $('#subtotal-amount').text('0.00');
        $('#total-discount').text('0.00');
        $('#grand-total').text('0.00');
        $('#display-paid-amount').text('0.00');
        $('#change-amount').text('0.00');
        
        // Reset customer info
        clearSelectedCustomer();
        posData.customerId = null;
        posData.customerName = null;
        posData.advanceAmount = 0;
        posData.advanceUsed = 0;
        
        // Clear customer badge if it has any advance info
        $('#customer-badge').html('<i class="fas fa-user mr-1"></i> <span id="pos-customer-name-display"></span>');
        
        // Reset payment info
        posData.paidAmount = 0;
        posData.changeAmount = 0;
        posData.creditAmount = 0;
        
        // Reset form fields
        $('#payment-method').val('cash');
        $('#paid-amount').val('0.00');
        $('#barcode-input').val('');
        $('#product-search').val('');
        
        // Hide credit amount row
        $('#credit-amount-row').hide();
        
        // Reset the use advance checkbox
        $('#use-advance-payment').prop('checked', false);
        
        // Reset cheque fields
        $('#cheque-number').val('');
        $('#cheque-number-group').hide();
        posData.chequeNumber = null;
        
        // Log reset completion
        console.log("POS system reset with new invoice:", posData.invoice);
    }
    
    // Function to handle using advance payment
    $(document).ready(function() {
        // Add variables to track advance payment
        let customerAdvanceAmount = 0;
        let advanceUsed = 0;
        let grandTotal = 0;
        
        // When customer is selected - check for advance payment
        $('#customer-results').on('click', '.customer-row', function() {
            // ...existing customer selection code...
            
            const customerId = $(this).data('id');
            const customerName = $(this).data('name');
            const advance = parseFloat($(this).data('advance')) || 0;
            
            // Store values in posData
            posData.customerId = customerId;
            posData.customerName = customerName;
            posData.advanceAmount = advance;
            
            // Update local variable
            customerAdvanceAmount = advance;
            
            console.log("Customer selected with advance amount:", customerAdvanceAmount);
            
            // Check if customer has advance payment
            if (customerAdvanceAmount > 0) {
                // Show advance payment section
                $('#available-advance-amount').text('Rs. ' + customerAdvanceAmount.toFixed(2));
                $('#advance-payment-section').show();
            } else {
                // Hide advance payment section
                $('#advance-payment-section').hide();
                $('#advance-amount-container').hide();
                $('#remaining-payment-section').hide();
            }
            
            // Reset advance usage
            $('#use-advance').prop('checked', false);
            advanceUsed = 0;
            posData.advanceUsed = 0;
            
            // Update payment calculations
            updatePaymentAmounts();
        });
        
        // Use advance payment toggle
        $('#use-advance').change(function() {
            if ($(this).is(':checked')) {
                // User wants to use advance payment
                
                // Calculate how much advance to use (either full amount or total due, whichever is less)
                grandTotal = parseFloat(posData.grandTotal) || 0;
                const maxAdvanceToUse = Math.min(customerAdvanceAmount, grandTotal);
                
                // Set the advance amount
                advanceUsed = maxAdvanceToUse;
                posData.advanceUsed = maxAdvanceToUse;
                
                // Show advance amount input with default value set to maximum usable
                $('#advance-amount').val(maxAdvanceToUse.toFixed(2));
                $('#advance-amount-container').slideDown();
                
                // Calculate remaining amount to pay after advance
                const remainingToPay = Math.max(0, grandTotal - advanceUsed);
                
                // Update the UI to show remaining amount
                $('#remaining-amount').text('Rs. ' + remainingToPay.toFixed(2));
                $('#remaining-payment-section').show();
                
                // Update paid amount field based on remaining to pay
                $('#paid-amount').val(remainingToPay.toFixed(2));
                
                console.log("Using advance payment:", advanceUsed, "Remaining to pay:", remainingToPay);
            } else {
                // User doesn't want to use advance payment
                $('#advance-amount-container').slideUp();
                $('#remaining-payment-section').hide();
                
                // Reset advance usage
                advanceUsed = 0;
                posData.advanceUsed = 0;
                
                // Reset paid amount to full grand total
                $('#paid-amount').val(posData.grandTotal.toFixed(2));
                
                console.log("Not using advance payment");
            }
            
            // Update payment calculations
            updatePaymentAmounts();
        });
        
        // Handle changes to advance amount input
        $('#advance-amount').on('input', function() {
            const inputAmount = parseFloat($(this).val()) || 0;
            
            // Ensure amount doesn't exceed limits
            let actualAmount = inputAmount;
            
            // Don't exceed available advance
            if (actualAmount > customerAdvanceAmount) {
                actualAmount = customerAdvanceAmount;
                $(this).val(actualAmount.toFixed(2));
            }
            
            // Don't exceed grand total
            if (actualAmount > grandTotal) {
                actualAmount = grandTotal;
                $(this).val(actualAmount.toFixed(2));
            }
            
            // Update the advance used variables
            advanceUsed = actualAmount;
            posData.advanceUsed = actualAmount;
            
            // Calculate remaining amount to pay
            const remainingToPay = Math.max(0, grandTotal - advanceUsed);
            
            // Update UI
            $('#remaining-amount').text('Rs. ' + remainingToPay.toFixed(2));
            
            // Update paid amount field to match remaining
            $('#paid-amount').val(remainingToPay.toFixed(2));
            
            console.log("Advance amount updated:", advanceUsed, "Remaining to pay:", remainingToPay);
            
            // Update payment calculations
            updatePaymentAmounts();
        });
        
        // "Use Max" button for advance payment
        $('#use-max-advance').click(function() {
            const maxAdvanceToUse = Math.min(customerAdvanceAmount, grandTotal);
            
            // Set the advance amount input
            $('#advance-amount').val(maxAdvanceToUse.toFixed(2));
            
            // Update the advance used variables
            advanceUsed = maxAdvanceToUse;
            posData.advanceUsed = maxAdvanceToUse;
            
            // Calculate remaining amount to pay
            const remainingToPay = Math.max(0, grandTotal - advanceUsed);
            
            // Update UI
            $('#remaining-amount').text('Rs. ' + remainingToPay.toFixed(2));
            
            // Update paid amount field
            $('#paid-amount').val(remainingToPay.toFixed(2));
            
            console.log("Max advance used:", advanceUsed, "Remaining to pay:", remainingToPay);
            
            // Update payment calculations
            updatePaymentAmounts();
        });
        
        // Function to update payment amounts based on current state
        function updatePaymentAmounts() {
            const paidAmount = parseFloat($('#paid-amount').val()) || 0;
            const paymentMethod = $('#payment-method').val();
            
            // Calculate the effective total (grand total minus advance used)
            const effectiveTotal = Math.max(0, posData.grandTotal - advanceUsed);
            
            // Calculate change based on payment method and effective total
            if (paymentMethod === 'credit') {
                // For credit payments, there's no change
                posData.changeAmount = 0;
                posData.creditAmount = effectiveTotal;
                posData.paidAmount = 0;
            } else {
                // For cash/card payments
                posData.changeAmount = paidAmount > effectiveTotal ? paidAmount - effectiveTotal : 0;
                posData.creditAmount = 0;
                posData.paidAmount = paidAmount;
            }
            
            // Update any UI elements showing the change amount
            if ($('#change-amount').length) {
                $('#change-amount').text('Rs. ' + posData.changeAmount.toFixed(2));
            }
            
            console.log("Payment amounts updated:", {
                method: paymentMethod,
                grandTotal: posData.grandTotal,
                advanceUsed: advanceUsed,
                effectiveTotal: effectiveTotal,
                paidAmount: posData.paidAmount,
                changeAmount: posData.changeAmount,
                creditAmount: posData.creditAmount
            });
        }
        
        // Update payment amounts when paid amount changes
        $('#paid-amount').on('input', function() {
            updatePaymentAmounts();
        });
        
        // Update payment amounts when payment method changes
        $('#payment-method').change(function() {
            const method = $(this).val();
            
            if (method === 'credit') {
                // If credit, set paid amount to 0
                $('#paid-amount').val('0.00');
            } else {
                // For cash/card, set paid amount to remaining after advance
                const remainingToPay = Math.max(0, posData.grandTotal - advanceUsed);
                $('#paid-amount').val(remainingToPay.toFixed(2));
            }
            
            // Update payment calculations
            updatePaymentAmounts();
        });
        
        // Ensure grand total is updated whenever cart changes
        const originalUpdateTotals = updateTotals;
        updateTotals = function() {
            // Call original function if it exists
            if (typeof originalUpdateTotals === 'function') {
                originalUpdateTotals();
            }
            
            // Update local grandTotal variable
            grandTotal = parseFloat(posData.grandTotal) || 0;
            
            // If advance is being used, update the advance amount input max value
            if ($('#use-advance').is(':checked')) {
                const currentAdvanceUsed = parseFloat($('#advance-amount').val()) || 0;
                
                // If current advance used is more than new grand total, adjust it
                if (currentAdvanceUsed > grandTotal) {
                    const newAdvanceUsed = Math.min(customerAdvanceAmount, grandTotal);
                    $('#advance-amount').val(newAdvanceUsed.toFixed(2));
                    advanceUsed = newAdvanceUsed;
                    posData.advanceUsed = newAdvanceUsed;
                }
                
                // Update remaining to pay
                const remainingToPay = Math.max(0, grandTotal - advanceUsed);
                $('#remaining-amount').text('Rs. ' + remainingToPay.toFixed(2));
                
                // Update paid amount field
                if ($('#payment-method').val() !== 'credit') {
                    $('#paid-amount').val(remainingToPay.toFixed(2));
                }
            } else {
                // Not using advance, update paid amount to full grand total
                if ($('#payment-method').val() !== 'credit') {
                    $('#paid-amount').val(grandTotal.toFixed(2));
                }
            }
            
            // Update payment calculations
            updatePaymentAmounts();
        };
        
        // Complete sale handler - ensure advance payment is included
        $('#complete-sale').click(function() {
        
            // Prepare sale data 
            const saleData = {
                // ...existing properties...
                advance_used: advanceUsed,
                // ...existing properties...
            };
            
  
        });
        
       
    });
});

// Add the missing updateTotals function
function updateTotals() {
    // Calculate subtotal from cart items
    let subtotal = 0;
    posData.items.forEach(item => {
        subtotal += parseFloat(item.subtotal);
    });
    
    // Apply discount (if any)
    const discountAmount = parseFloat($('#discount-amount').val()) || 0;
    const grandTotal = subtotal - discountAmount;
    
    // Store values in posData object for use with other functions
    posData.subtotal = subtotal;
    posData.discountAmount = discountAmount;
    posData.grandTotal = grandTotal;
    
    // Update UI elements
    $('#cart-subtotal').text('Rs. ' + subtotal.toFixed(2));
    $('#cart-discount').text('Rs. ' + discountAmount.toFixed(2));
    $('#cart-total').text('Rs. ' + grandTotal.toFixed(2));
    
    // Update the paid amount field based on payment method
    if ($('#payment-method').val() !== 'credit') {
        $('#paid-amount').val(grandTotal.toFixed(2));
    } else {
        $('#paid-amount').val('0.00');
    }
    
    // If advance payment is being used, adjust the paid amount accordingly
    if (typeof posData.advanceUsed !== 'undefined' && posData.advanceUsed > 0) {
        const remainingAfterAdvance = Math.max(0, grandTotal - posData.advanceUsed);
        
        if ($('#payment-method').val() !== 'credit') {
            $('#paid-amount').val(remainingAfterAdvance.toFixed(2));
        }
        
        // Update the remaining amount display if it exists
        if ($('#remaining-amount').length > 0) {
            $('#remaining-amount').text('Rs. ' + remainingAfterAdvance.toFixed(2));
        }
    }
    
    // Update cart item count badge
    $('#cart-badge').text(posData.items.length);
    
    console.log("Totals updated:", {
        subtotal: subtotal.toFixed(2),
        discount: discountAmount.toFixed(2),
        grandTotal: grandTotal.toFixed(2),
        advanceUsed: (posData.advanceUsed || 0).toFixed(2)
    });
}

// Make sure updateTotals is called when the page loads if there are items in the cart
$(document).ready(function() {
    // Initialize totals if needed
    if (posData.items && posData.items.length > 0) {
        updateTotals();
    }
});

$(document).ready(function() {
    // ...existing code...
    
    // Allow proper decimal handling for discount input
    $('#discount-amount').on('input', function() {
        // Allow decimal values with any precision
        let value = $(this).val();
        
        // Ensure valid decimal format (only numbers and one decimal point)
        if (value && !/^[0-9]*\.?[0-9]*$/.test(value)) {
            value = value.replace(/[^0-9.]/g, '');
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }
            $(this).val(value);
        }
        
        // Update calculations with the decimal value
        calculateTotals();
    });
    
    // ...existing code...
    
    function calculateTotals() {
        // ...existing code...
        
        // Parse discount amount as float to properly handle decimals
        const discountAmount = parseFloat($('#discount-amount').val()) || 0;
        
        // ...existing code...
    }
    
    // ...existing code...
});
</script>
