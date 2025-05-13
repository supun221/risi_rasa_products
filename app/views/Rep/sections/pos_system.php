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
});
</script>
