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
                    <label for="customer-phone-search"><i class="fas fa-search mr-1"></i> Search Customer by Name</label>
                    <input type="text" class="form-control" id="customer-phone-search" placeholder="Enter name" autocomplete="off">
                    <div id="customer-phone-suggestions" class="phone-suggestions"></div>
                </div>
                
                <div id="pos-customer-search-results" class="mt-3" style="display: none;">
                    <h6>Select a Customer</h6>
                    <div class="list-group" id="pos-customer-results-list"></div>
                </div>
                
                <div id="pos-no-customer-found" class="alert alert-info mt-3" style="display: none;">
                    No customer found with this phone number.
                    <button type="button" class="btn btn-sm btn-outline-primary ml-2" id="pos-add-new-customer">
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
                <button class="btn btn-outline-success ml-2" id="add-customer-btn">
                    <i class="fas fa-user-plus"></i> Add New Customer
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
                        <div class="row mb-2" id="summary-used-advance-row" style="display: none;">
                            <div class="col-6 font-weight-bold text-info">Advance Used:</div>
                            <div class="col-6 text-info">Rs. <span id="summary-used-advance">0.00</span></div>
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
                                
                                <!-- Return Bill Field -->
                                <div class="form-group">
                                    <label for="return-bill-number">Return Bill Number</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="return-bill-number" placeholder="Enter RTN-XXXXXXXX-XXXX">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-primary" type="button" id="verify-return-bill">
                                                <i class="fas fa-check"></i> Verify
                                            </button>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">Enter a valid return bill number to deduct the amount.</small>
                                </div>

                                <div class="alert alert-success" id="return-bill-info" style="display: none;">
                                    <i class="fas fa-check-circle mr-1"></i> Return amount: Rs. <span id="return-bill-amount">0.00</span>
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

<!-- Add Customer Modal for POS -->
<div class="modal fade" id="pos-add-customer-modal" tabindex="-1" role="dialog" aria-labelledby="posAddCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="posAddCustomerModalLabel">Add New Customer</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="pos-customer-form">
                    <div class="form-group">
                        <label for="pos-customer-name">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="pos-customer-name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="pos-customer-telephone">Telephone <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="pos-customer-telephone" name="telephone" required>
                    </div>
                    <div class="form-group">
                        <label for="pos-customer-nic">NIC</label>
                        <input type="text" class="form-control" id="pos-customer-nic" name="nic">
                    </div>
                    <div class="form-group">
                        <label for="pos-customer-address">Address</label>
                        <textarea class="form-control" id="pos-customer-address" name="address" rows="2"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="pos-customer-whatsapp">WhatsApp</label>
                        <input type="tel" class="form-control" id="pos-customer-whatsapp" name="whatsapp">
                    </div>
                    <div class="form-group">
                        <label for="pos-customer-route-id">Route</label>
                        <select class="form-control" id="pos-customer-route-id" name="route_id">
                            <option value="">-- Select Route --</option>
                            <!-- Routes will be loaded here -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="pos-customer-credit-limit">Credit Limit</label>
                        <input type="number" class="form-control" id="pos-customer-credit-limit" name="credit_limit" value="0">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-pos-customer-btn">Save Customer</button>
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

/* Toast notification style */
.toast-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #28a745;
    color: white;
    padding: 10px 20px;
    border-radius: 4px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    z-index: 9999;
    opacity: 0;
    transform: translateY(-20px);
    transition: all 0.3s ease;
}

.toast-notification.show {
    opacity: 1;
    transform: translateY(0);
}
</style>

<script>
$(document).ready(function() {
    // Add New Customer button - Show modal
    $('#add-customer-btn, #pos-add-new-customer').click(function() {
        // Load routes into dropdown
        loadRoutesForPosCustomerForm();
        
        // Show the modal
        $('#pos-add-customer-modal').modal('show');
    });
    
    // Load routes for customer form
    function loadRoutesForPosCustomerForm() {
        $.ajax({
            url: 'process/get_routes.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    let options = '<option value="">-- Select Route --</option>';
                    
                    response.routes.forEach(function(route) {
                        options += `<option value="${route.id}">${route.name}</option>`;
                    });
                    
                    $('#pos-customer-route-id').html(options);
                } else {
                    console.error('Error loading routes:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading routes:', error);
            }
        });
    }
    
    // Save POS Customer button click
    $('#save-pos-customer-btn').click(function() {
        // Get form data
        const name = $('#pos-customer-name').val().trim();
        const telephone = $('#pos-customer-telephone').val().trim();
        const nic = $('#pos-customer-nic').val().trim();
        const address = $('#pos-customer-address').val().trim();
        const whatsapp = $('#pos-customer-whatsapp').val().trim();
        const routeId = $('#pos-customer-route-id').val();
        const creditLimit = $('#pos-customer-credit-limit').val();
        
        // Validate required fields
        if (!name || !telephone) {
            Swal.fire({
                icon: 'error',
                title: 'Required Fields',
                text: 'Please enter name and telephone number.'
            });
            return;
        }
        
        // Show loading state
        $('#save-pos-customer-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
        
        // Send data to server
        $.ajax({
            url: 'process/add_customer.php',
            type: 'POST',
            data: {
                name: name,
                telephone: telephone,
                nic: nic,
                address: address,
                whatsapp: whatsapp,
                route_id: routeId,
                credit_limit: creditLimit
            },
            dataType: 'json',
            success: function(response) {
                // Reset button state
                $('#save-pos-customer-btn').prop('disabled', false).html('Save Customer');
                
                if (response.success) {
                    // Close modal
                    $('#pos-add-customer-modal').modal('hide');
                    
                    // Reset form
                    $('#pos-customer-form')[0].reset();
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Customer Added',
                        text: 'The customer has been successfully added.'
                    });
                    
                    // Select the newly added customer
                    if (response.customer_id) {
                        // Simulate a customer selection - use existing selectCustomer function
                        const customerElement = {
                            data: function(key) {
                                const data = {
                                    id: response.customer_id,
                                    name: name,
                                    telephone: telephone,
                                    nic: nic
                                };
                                return data[key];
                            }
                        };
                        
                        selectCustomer(customerElement);
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to add customer.'
                    });
                }
            },
            error: function() {
                // Reset button state
                $('#save-pos-customer-btn').prop('disabled', false).html('Save Customer');
                
                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    text: 'Could not connect to the server. Please try again.'
                });
            }
        });
    });
    
    // Fix the toggle-customer-search button functionality
    $('#toggle-customer-search').click(function() {
        $('#customer-search-card').toggle();
        if ($('#customer-search-card').is(':visible')) {
            $('#customer-phone-search').focus();
        }
    });
    
    // Customer phone search functionality
    $('#customer-phone-search').on('input', function() {
        const searchTerm = $(this).val().trim();
        
        if (searchTerm.length >= 2) {
            // Perform AJAX search
            $.ajax({
                url: 'process/search_customers.php',
                type: 'GET',
                data: { term: searchTerm },
                dataType: 'json',
                success: function(response) {
                    let suggestionsHtml = '';
                    
                    if (response.success && response.customers.length > 0) {
                        response.customers.forEach(function(customer) {
                            suggestionsHtml += `
                                <div class="phone-suggestion-item" 
                                    data-id="${customer.id}" 
                                    data-name="${customer.name}" 
                                    data-telephone="${customer.telephone}"
                                    data-nic="${customer.nic || ''}"
                                    data-credit-balance="${customer.credit_balance || 0}"
                                    data-advance="${customer.advance_amount || 0}"
                                    data-route="${customer.route_name || ''}">
                                    <span class="customer-name">${customer.name}</span>
                                    <span class="customer-phone">${customer.telephone}</span>
                                </div>`;
                        });
                        
                        $('#customer-phone-suggestions').html(suggestionsHtml).show();
                        
                        // Attach click event to suggestions
                        $('.phone-suggestion-item').click(function() {
                            selectCustomer($(this));
                        });
                    } else {
                        $('#customer-phone-suggestions').hide();
                        $('#pos-customer-search-results').hide();
                        $('#pos-no-customer-found').show();
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error searching for customers:", error);
                    $('#customer-phone-suggestions').hide();
                }
            });
        } else {
            $('#customer-phone-suggestions').hide();
            $('#pos-customer-search-results').hide();
            $('#pos-no-customer-found').hide();
        }
    });
    
    // Function to select a customer
    function selectCustomer(customerElement) {
        // Get customer details from data attributes
        const customerId = customerElement.data('id');
        const customerName = customerElement.data('name');
        const customerPhone = customerElement.data('telephone');
        const customerNIC = customerElement.data('nic');
        const creditBalance = parseFloat(customerElement.data('credit-balance')) || 0;
        const advanceAmount = parseFloat(customerElement.data('advance')) || 0;
        
        // Update selected customer display
        $('#pos-customer-name-display').text(customerName);
        $('#selected-pos-customer-info').show();
        
        // Store customer data for use at checkout
        window.selectedCustomer = {
            id: customerId,
            name: customerName,
            telephone: customerPhone,
            nic: customerNIC,
            creditBalance: creditBalance,
            advanceAmount: advanceAmount
        };
        
        // Log selected customer for debugging
        console.log("Selected customer:", window.selectedCustomer);
        
        // Hide search components
        $('#customer-search-card').hide();
        $('#customer-phone-suggestions').hide();
        $('#pos-customer-search-results').hide();
        $('#pos-no-customer-found').hide();
        
        // Clear search input
        $('#customer-phone-search').val('');
        
        // Update payment UI based on customer selection
        updatePaymentOptionsForCustomer();
    }
    
    // Clear selected customer
    $('#clear-customer').click(function() {
        $('#selected-pos-customer-info').hide();
        $('#pos-customer-name-display').text('');
        window.selectedCustomer = null;
        
        // Reset payment UI
        updatePaymentOptionsForCustomer();
    });
    
    // Update payment options based on customer
    function updatePaymentOptionsForCustomer() {
        if (window.selectedCustomer) {
            // Enable credit option and show advance if available
            $("#payment-method option[value='credit']").prop('disabled', false);
            
            // Check if customer has advance payment
            if (window.selectedCustomer.advanceAmount > 0) {
                $('#summary-advance-row').show();
                $('#summary-advance-amount').text(window.selectedCustomer.advanceAmount.toFixed(2));
                $('#use-advance-check-container').show();
                $('#available-advance-amount').text(window.selectedCustomer.advanceAmount.toFixed(2));
            } else {
                $('#summary-advance-row').hide();
                $('#use-advance-check-container').hide();
            }
        } else {
            // Disable credit option if no customer selected
            $("#payment-method option[value='credit']").prop('disabled', true);
            
            // If credit is selected but no customer, switch to cash
            if ($("#payment-method").val() === 'credit') {
                $("#payment-method").val('cash');
            }
            
            // Hide advance payment options
            $('#summary-advance-row').hide();
            $('#use-advance-check-container').hide();
        }
    }
    
    // Handle barcode input - search when Enter key is pressed
    $('#barcode-input').keypress(function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            const barcode = $(this).val().trim();
            if (barcode) {
                searchProductByBarcode(barcode);
            }
        }
    });
    
    // The search barcode button should also trigger the same function
    $('#search-barcode').click(function() {
        const barcode = $('#barcode-input').val().trim();
        if (barcode) {
            searchProductByBarcode(barcode);
        }
    });
    
    // Function to search for product by barcode
    function searchProductByBarcode(barcode) {
        // Show loading indicator inside the input
        $('#barcode-input').prop('disabled', true);
        $('#search-barcode').html('<i class="fas fa-spinner fa-spin"></i>');
        
        // Make API request to search for product
        $.ajax({
            url: 'process/search_product_barcode.php',
            type: 'GET',
            data: { barcode: barcode },
            dataType: 'json',
            success: function(response) {
                // Reset loading indicators
                $('#barcode-input').prop('disabled', false);
                $('#search-barcode').html('<i class="fas fa-barcode"></i>');
                
                if (response.success) {
                    if (response.products.length > 1) {
                        // Multiple products found with the same barcode
                        showDuplicateProductsModal(response.products);
                    } else if (response.products.length === 1) {
                        // Single product found - select it
                        selectProduct(response.products[0]);
                    } else {
                        // No products found
                        Swal.fire({
                            icon: 'error',
                            title: 'Product Not Found',
                            text: 'No product found with barcode: ' + barcode
                        });
                    }
                } else {
                    // Error occurred
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to search for product'
                    });
                }
                
                // Clear barcode input and focus it again for next scan
                $('#barcode-input').val('').focus();
            },
            error: function(xhr, status, error) {
                // Reset loading indicators
                $('#barcode-input').prop('disabled', false);
                $('#search-barcode').html('<i class="fas fa-barcode"></i>');
                
                console.error('Error searching for product:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    text: 'Failed to connect to server. Please try again.'
                });
                
                // Clear barcode input and focus it again
                $('#barcode-input').val('').focus();
            }
        });
    }
    
    // Function to show modal with duplicate products
    function showDuplicateProductsModal(products) {
        // Clear previous product list
        $('#duplicate-products-list').empty();
        
        // Add each product to the list
        products.forEach(function(product) {
            const listItem = `
                <a href="#" class="list-group-item list-group-item-action duplicate-product" 
                   data-product='${JSON.stringify(product).replace(/'/g, "&#39;")}'>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">${product.product_name}</h6>
                            <small>Barcode: ${product.barcode || product.itemcode || 'N/A'}</small>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-primary">Rs. ${parseFloat(product.price).toFixed(2)}</span><br>
                            <small>Stock: ${product.quantity}</small>
                        </div>
                    </div>
                </a>
            `;
            $('#duplicate-products-list').append(listItem);
        });
        
        // Show the modal
        $('#duplicate-barcode-modal').modal('show');
        
        // Handle product selection from modal
        $('.duplicate-product').off('click').on('click', function(e) {
            e.preventDefault();
            const product = $(this).data('product');
            $('#duplicate-barcode-modal').modal('hide');
            selectProduct(product);
        });
    }
    
    // Function to select a product and populate the form fields
    function selectProduct(product) {
        console.log('Selected product:', product);
        
        // Populate product fields
        $('#selected-product').val(product.product_name);
        $('#lorry-stock-id').val(product.id || product.lorry_stock_id || '');
        $('#available-qty').val(product.quantity || '0');
        $('#unit-price').val(parseFloat(product.price).toFixed(2));
        
        // Store the selected product for later use
        window.selectedProduct = product;
        
        // Update line total calculation
        updateLineTotal();
        
        // Focus on quantity input for quicker entry
        $('#quantity').focus().select();
    }
    
    // Function to update line total when quantity or discount changes
    function updateLineTotal() {
        const quantity = parseInt($('#quantity').val()) || 0;
        const unitPrice = parseFloat($('#unit-price').val()) || 0;
        const discountPercent = parseFloat($('#discount-percent').val()) || 0;
        const discountAmount = parseFloat($('#discount-amount').val()) || 0;
        
        let total = quantity * unitPrice;
        
        // Apply percentage discount
        if (discountPercent > 0) {
            total = total - (total * (discountPercent / 100));
        }
        
        // Apply fixed discount
        if (discountAmount > 0) {
            total = total - discountAmount;
        }
        
        // Ensure total is not negative
        total = Math.max(total, 0);
        
        $('#line-total').val(total.toFixed(2));
    }
    
    // Update line total when quantity, price, or discount changes
    $('#quantity, #discount-percent, #discount-amount').on('input', function() {
        updateLineTotal();
    });
    
    // Handle add product form submission (both button click and Enter key)
    $('#add-product-form').on('submit', function(e) {
        e.preventDefault();
        addProductToCart();
    });
    
    // Handle Enter key on quantity field
    $('#quantity').keypress(function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            addProductToCart();
        }
    });
    
    // Function to add product to cart
    function addProductToCart() {
        // Get form values
        const productName = $('#selected-product').val().trim();
        const lorryStockId = $('#lorry-stock-id').val();
        const quantity = parseInt($('#quantity').val()) || 0;
        const freeQuantity = parseInt($('#free-qty').val()) || 0;
        const unitPrice = parseFloat($('#unit-price').val()) || 0;
        const discountPercent = parseFloat($('#discount-percent').val()) || 0;
        const discountAmount = parseFloat($('#discount-amount').val()) || 0;
        const lineTotal = parseFloat($('#line-total').val()) || 0;
        
        // Validate input
        if (!productName) {
            Swal.fire({
                icon: 'error',
                title: 'Product Required',
                text: 'Please select a product first.'
            });
            return;
        }
        
        if (quantity <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Quantity',
                text: 'Please enter a quantity greater than zero.'
            });
            return;
        }
        
        // Check available quantity
        const availableQty = parseInt($('#available-qty').val()) || 0;
        if (quantity > availableQty) {
            Swal.fire({
                icon: 'warning',
                title: 'Not Enough Stock',
                text: `Only ${availableQty} items available in stock.`
            });
            return;
        }
        
        // Get selected product data from stored object
        const product = window.selectedProduct || {};
        
        // Create item object for cart
        const item = {
            lorry_stock_id: lorryStockId,
            product_name: productName,
            barcode: product.barcode || product.itemcode || '',
            quantity: quantity,
            free_quantity: freeQuantity,
            unit_price: unitPrice,
            discount_percent: discountPercent,
            discount_amount: discountAmount,
            subtotal: lineTotal
        };
        
        // Send item to server to add to cart
        $.ajax({
            url: 'process/add_to_cart.php',
            type: 'POST',
            data: item,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Item added to cart successfully
                    updateCartDisplay();
                    
                    // Clear the product selection form
                    clearProductForm();
                    
                    // Focus back on barcode input for next scan
                    $('#barcode-input').focus();
                    
                    // Show brief success message
                    showToast('Product added to cart!');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to add product to cart.'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error adding to cart:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    text: 'Could not add product to cart. Please try again.'
                });
            }
        });
    }
    
    // Clear product selection form
    function clearProductForm() {
        $('#selected-product').val('');
        $('#lorry-stock-id').val('');
        $('#available-qty').val('');
        $('#unit-price').val('');
        $('#quantity').val(1);
        $('#free-qty').val(0);
        $('#discount-percent').val(0);
        $('#discount-amount').val(0);
        $('#line-total').val('');
        window.selectedProduct = null;
    }
    
    // Update cart display with latest items
    function updateCartDisplay() {
        $.ajax({
            url: 'process/get_cart.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    renderCartItems(response.cart);
                    updateOrderSummary(response.totals);
                } else {
                    console.error('Error loading cart:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching cart:', error);
            }
        });
    }
    
    // Render cart items in the table
    function renderCartItems(items) {
        const tableBody = $('#pos-items-body');
        
        // Clear current items
        tableBody.empty();
        
        if (items.length === 0) {
            tableBody.html('<tr id="empty-cart"><td colspan="7" class="text-center">No items added yet</td></tr>');
            return;
        }
        
        // Add each item to the table
        items.forEach(function(item, index) {
            const row = `
                <tr data-index="${index}">
                    <td>${item.product_name}</td>
                    <td>${parseFloat(item.unit_price).toFixed(2)}</td>
                    <td>${item.quantity}</td>
                    <td>${item.free_quantity}</td>
                    <td>${item.discount_percent}% / ${parseFloat(item.discount_amount).toFixed(2)}</td>
                    <td>${parseFloat(item.subtotal).toFixed(2)}</td>
                    <td>
                        <button class="btn btn-sm btn-danger remove-item" data-index="${index}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            
            tableBody.append(row);
        });
        
        // Attach remove item event handlers
        $('.remove-item').click(function() {
            const indexToRemove = $(this).data('index');
            removeCartItem(indexToRemove);
        });
    }
    
    // Update order summary values
    function updateOrderSummary(totals) {
        $('#subtotal-amount').text(parseFloat(totals.subtotal).toFixed(2));
        $('#total-discount').text(parseFloat(totals.totalDiscount).toFixed(2));
        $('#grand-total').text(parseFloat(totals.grandTotal).toFixed(2));
    }
    
    // Remove item from cart
    function removeCartItem(index) {
        $.ajax({
            url: 'process/remove_from_cart.php',
            type: 'POST',
            data: { index: index },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    updateCartDisplay();
                    showToast('Item removed from cart');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to remove item from cart.'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error removing from cart:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    text: 'Could not remove item from cart. Please try again.'
                });
            }
        });
    }
    
    // Small toast notification
    function showToast(message) {
        const toast = $('<div class="toast-notification"></div>').text(message);
        $('body').append(toast);
        
        setTimeout(function() {
            toast.addClass('show');
        }, 100);
        
        setTimeout(function() {
            toast.removeClass('show');
            
            setTimeout(function() {
                toast.remove();
            }, 500);
        }, 3000);
    }
    
    // Load cart on page load
    updateCartDisplay();
    
    // Handle Complete Sale button click
    $('#complete-sale').click(function() {
        // Validate that there are items in the cart
        $.ajax({
            url: 'process/get_cart.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success && response.cart && response.cart.length > 0) {
                    // We have items, show payment confirmation modal
                    prepareCheckoutModal(response.cart, response.totals);
                } else {
                    // No items in cart
                    Swal.fire({
                        icon: 'warning',
                        title: 'Empty Cart',
                        text: 'Please add items to your cart before completing the sale.'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to check cart items. Please try again.'
                });
            }
        });
    });
    
    // Function to prepare checkout modal before showing it
    function prepareCheckoutModal(items, totals) {
        // Update summary information
        $('#summary-total-items').text(items.length);
        $('#summary-subtotal').text(parseFloat(totals.subtotal).toFixed(2));
        $('#summary-discount').text(parseFloat(totals.totalDiscount).toFixed(2));
        $('#summary-grand-total').text(parseFloat(totals.grandTotal).toFixed(2));
        
        // Update customer name in summary
        if (window.selectedCustomer) {
            $('#summary-customer-name').text(window.selectedCustomer.name);
        } else {
            $('#summary-customer-name').text('Walk-in Customer');
        }
        
        // Set default payment method to match the main form
        const paymentMethod = $('#payment-method').val();
        $('#modal-payment-method').val(paymentMethod);
        
        // Initialize paid amount to match grand total for convenience
        const grandTotal = parseFloat(totals.grandTotal);
        $('#modal-paid-amount').val(grandTotal.toFixed(2));
        
        // Reset change amount
        $('#modal-change-amount').text('0.00');
        
        // Show/hide credit amount field based on payment method
        updatePaymentFields();
        
        // Show the modal
        $('#payment-confirmation-modal').modal('show');
    }
    
    // Update payment fields when payment method changes
    $('#modal-payment-method').on('change', updatePaymentFields);
    
    function updatePaymentFields() {
        const paymentMethod = $('#modal-payment-method').val();
        
        // Show/hide cheque number field
        if (paymentMethod === 'cheque') {
            $('#cheque-number-group').show();
        } else {
            $('#cheque-number-group').hide();
        }
        
        // Show/hide credit amount group
        if (paymentMethod === 'credit') {
            $('#modal-credit-amount-group').show();
            $('#modal-change-amount-group').hide();
            
            // Ensure a customer is selected for credit
            if (!window.selectedCustomer) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Customer Required',
                    text: 'Please select a customer before using credit payment.'
                });
                $('#modal-payment-method').val('cash');
                $('#modal-credit-amount-group').hide();
                $('#modal-change-amount-group').show();
                return;
            }
        } else {
            $('#modal-credit-amount-group').hide();
            $('#modal-change-amount-group').show();
        }
        
        // Calculate change/credit amount
        calculateChange();
    }
    
    // Calculate change when amount paid changes
    $('#modal-paid-amount').on('input', calculateChange);
    
    // Calculate change/credit amount
    function calculateChange() {
        const grandTotal = parseFloat($('#summary-grand-total').text()) || 0;
        const paidAmount = parseFloat($('#modal-paid-amount').val()) || 0;
        const paymentMethod = $('#modal-payment-method').val();
        
        if (paymentMethod === 'credit') {
            // For credit, calculate remaining credit amount
            const creditAmount = Math.max(0, grandTotal - paidAmount).toFixed(2);
            $('#modal-credit-amount').text(creditAmount);
        } else {
            // For other payment types, calculate change
            const changeAmount = Math.max(0, paidAmount - grandTotal).toFixed(2);
            $('#modal-change-amount').text(changeAmount);
        }
    }
    
    // Handle confirm payment button click
    $('#confirm-payment').click(function() {
        // Disable button to prevent double submission
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        // Get payment details
        const paymentMethod = $('#modal-payment-method').val();
        const paidAmount = parseFloat($('#modal-paid-amount').val()) || 0;
        const printInvoice = $('#print-invoice').is(':checked');
        const chequeNumber = (paymentMethod === 'cheque') ? $('#cheque-number').val() : '';
        const returnBillNumber = $('#return-bill-number').val();
        
        // Validate required fields
        if (paymentMethod === 'cheque' && !chequeNumber) {
            Swal.fire({
                icon: 'error',
                title: 'Missing Information',
                text: 'Please enter the cheque number.'
            });
            $(this).prop('disabled', false).html('Confirm & Complete');
            return;
        }
        
        // Prepare data for submission
        const saleData = {
            payment_method: paymentMethod,
            paid_amount: paidAmount,
            invoice_number: '<?php echo $invoiceNumber; ?>', // From PHP variable
            cheque_number: chequeNumber,
            return_bill_number: returnBillNumber,
            customer_id: window.selectedCustomer ? window.selectedCustomer.id : null,
            customer_name: window.selectedCustomer ? window.selectedCustomer.name : 'Walk-in Customer',
            print_invoice: printInvoice
        };
        
        // Check if advance payment checkbox is checked and customer has advance
        const useAdvanceChecked = $('#use-advance-payment').is(':checked');
        console.log("Checking advance payment:", useAdvanceChecked);
        
        if (window.selectedCustomer && window.selectedCustomer.advanceAmount > 0 && useAdvanceChecked) {
            console.log("Adding advance payment data:", window.selectedCustomer.advanceAmount);
            saleData.use_advance = true;
            saleData.advance_amount = window.selectedCustomer.advanceAmount;
            
            // Update the display to show advance being used
            const advanceUsed = Math.min(window.selectedCustomer.advanceAmount, parseFloat($('#summary-grand-total').text()) || 0);
            $('#summary-used-advance').text(advanceUsed.toFixed(2));
        }
        
        console.log("Sale data:", saleData);
        
        // Submit sale data to server
        $.ajax({
            url: 'process/complete_sale.php',
            type: 'POST',
            data: saleData,
            dataType: 'json',
            success: function(response) {
                $('#confirm-payment').prop('disabled', false).html('Confirm & Complete');
                
                console.log("Server response:", response);
                
                if (response.success) {
                    // Close the modal
                    $('#payment-confirmation-modal').modal('hide');
                    
                    // Store the invoice number from response or use the one we sent
                    const generatedInvoiceNumber = response.invoice_number || saleData.invoice_number;
                    
                    // If print invoice is checked, open print window immediately
                    if (printInvoice) {
                        // Use the invoice number to open the correct invoice in a new tab
                        const printWindow = window.open('../invoice/pos_rep_invoice.php?invoice=' + encodeURIComponent(generatedInvoiceNumber), '_blank');
                        
                        // Show success message after a short delay
                        setTimeout(() => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Sale Completed',
                                text: 'The sale has been successfully completed.',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                // Reset POS after confirmation
                                resetPOS();
                            });
                        }, 500);
                    } else {
                        // Show success message immediately if not printing
                        Swal.fire({
                            icon: 'success',
                            title: 'Sale Completed',
                            text: 'The sale has been successfully completed.',
                            confirmButtonText: 'OK',
                            showCancelButton: true,
                            cancelButtonText: 'Print Invoice',
                            cancelButtonColor: '#3085d6'
                        }).then((result) => {
                            if (result.dismiss === Swal.DismissReason.cancel) {
                                // User clicked "Print Invoice" button
                                window.open('../invoice/pos_rep_invoice.php?invoice=' + encodeURIComponent(generatedInvoiceNumber), '_blank');
                            }
                            // Reset POS
                            resetPOS();
                        });
                    }
                } else {
                    // Show error message
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to complete the sale. Please try again.'
                    });
                }
            },
            error: function(xhr, status, error) {
                $('#confirm-payment').prop('disabled', false).html('Confirm & Complete');
                
                console.error('Error completing sale:', error);
                console.error('Server response:', xhr.responseText);
                
                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    text: 'Could not connect to the server. Please try again.'
                });
            }
        });
    });
    
    // Use advance payment checkbox event handler
    $('#use-advance-payment').on('change', function() {
        if ($(this).is(':checked') && window.selectedCustomer && window.selectedCustomer.advanceAmount > 0) {
            const grandTotal = parseFloat($('#summary-grand-total').text()) || 0;
            const advanceUsed = Math.min(window.selectedCustomer.advanceAmount, grandTotal);
            
            console.log("Advance checked. Available: " + window.selectedCustomer.advanceAmount + 
                        ", Using: " + advanceUsed + 
                        ", Grand total: " + grandTotal);
            
            // Display the advance amount that will be used
            $('#summary-used-advance-row').show();
            $('#summary-used-advance').text(advanceUsed.toFixed(2));
            
            // Adjust the amount that needs to be paid if advance doesn't cover the full amount
            const remainingToPay = Math.max(0, grandTotal - advanceUsed);
            $('#modal-paid-amount').val(remainingToPay.toFixed(2));
            
            // Recalculate change/credit
            calculateChange();
        } else {
            $('#summary-used-advance-row').hide();
            
            // Reset to full amount
            const grandTotal = parseFloat($('#summary-grand-total').text()) || 0;
            $('#modal-paid-amount').val(grandTotal.toFixed(2));
            
            // Recalculate change/credit
            calculateChange();
        }
    });

    // Verify return bill button click
    $('#verify-return-bill').click(function() {
        const returnBillNumber = $('#return-bill-number').val().trim();
        
        if (!returnBillNumber) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Information',
                text: 'Please enter a return bill number to verify.'
            });
            return;
        }
        
        // Show loading state
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        // Verify return bill with server
        $.ajax({
            url: 'process/verify_return_bill.php',
            type: 'GET',
            data: { return_bill_number: returnBillNumber },
            dataType: 'json',
            success: function(response) {
                // Reset button state
                $('#verify-return-bill').prop('disabled', false).html('<i class="fas fa-check"></i> Verify');
                
                if (response.success) {
                    // Show return bill amount
                    $('#return-bill-amount').text(parseFloat(response.amount).toFixed(2));
                    $('#return-bill-info').show();
                    
                    // Update payment fields with the return amount
                    const returnAmount = parseFloat(response.amount) || 0;
                    const grandTotal = parseFloat($('#summary-grand-total').text()) || 0;
                    const currentPaid = parseFloat($('#modal-paid-amount').val()) || 0;
                    
                    // Add return amount to paid amount
                    $('#modal-paid-amount').val((currentPaid + returnAmount).toFixed(2));
                    
                    // Recalculate change
                    calculateChange();
                } else {
                    $('#return-bill-info').hide();
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Return Bill',
                        text: response.message || 'Could not verify the return bill.'
                    });
                }
            },
            error: function() {
                // Reset button state
                $('#verify-return-bill').prop('disabled', false).html('<i class="fas fa-check"></i> Verify');
                
                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    text: 'Could not connect to the server. Please try again.'
                });
            }
        });
    });
});
</script>
