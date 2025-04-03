<?php
// Advance payment management logic
require_once '../../../../config/databade.php';

// Generate a unique bill number
function generateAdvanceBillNumber($conn) {
    $prefix = 'ADV';
    $date = date('Ymd');
    
    // Get the last used number for today
    $stmt = $conn->prepare("SELECT MAX(advance_bill_number) as max_num FROM advance_payments WHERE advance_bill_number LIKE ?");
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
        // No bills yet today, start with 1
        $next_num = 1;
    }
    
    // Format with leading zeros (4 digits)
    $sequence = str_pad($next_num, 4, '0', STR_PAD_LEFT);
    return $prefix . $date . $sequence;
}

$newBillNumber = generateAdvanceBillNumber($conn);
?>

<div class="section-card fade-transition" id="advance-payment-section">
    <div class="section-header">
        <i class="fas fa-money-check-alt"></i> Advance Payment
    </div>
    <div class="section-body">
        <a href="#" class="return-link" id="return-from-advance-payment">
            <i class="fas fa-chevron-left"></i> Return to Dashboard
        </a>
        
        <div class="card mb-4">
            <div class="card-body">
                <div class="form-group position-relative">
                    <label for="phone-search">Search Customer by Phone Number</label>
                    <input type="text" class="form-control" id="phone-search" placeholder="Enter phone number" autocomplete="off">
                    <div id="phone-suggestions" class="phone-suggestions"></div>
                </div>

                <div id="customer-search-results" class="mb-3" style="display: none;">
                    <h5>Select a Customer</h5>
                    <div class="list-group" id="customer-results-list"></div>
                </div>
                
                <div id="no-customer-found" class="alert alert-info" style="display: none;">
                    No customer found with this phone number. 
                    <button type="button" class="btn btn-sm btn-outline-primary ml-2" data-toggle="modal" data-target="#add-customer-modal">
                        Add New Customer
                    </button>
                </div>
                
                <div id="selected-customer-info" style="display: none;" class="mb-3">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Customer Information</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Name:</strong> <span id="customer-name-display"></span></p>
                                    <p><strong>Phone:</strong> <span id="customer-phone-display"></span></p>
                                    <p><strong>NIC:</strong> <span id="customer-nic-display"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Address:</strong> <span id="customer-address-display"></span></p>
                                    <p><strong>Credit Limit:</strong> Rs. <span id="customer-credit-limit-display"></span></p>
                                    <p><strong>Current Advance:</strong> Rs. <span id="customer-advance-display">0.00</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Advance Payment Details</h5>
            </div>
            <div class="card-body">
                <form id="advance-payment-form">
                    <input type="hidden" id="customer_id" name="customer_id">
                    <input type="hidden" id="advance_bill_number" name="advance_bill_number" value="<?php echo $newBillNumber; ?>">
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="payment_amount">Payment Amount <span class="required-indicator">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rs.</span>
                                </div>
                                <input type="number" step="0.01" min="0" class="form-control" id="payment_amount" name="payment_amount" required>
                            </div>
                        </div>
                        
                        <div class="form-group col-md-6">
                            <label for="payment_type">Payment Type <span class="required-indicator">*</span></label>
                            <select class="form-control" id="payment_type" name="payment_type" required>
                                <option value="">Select Payment Type</option>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="bank">Bank Transfer</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="reason">Reason/Note</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3"></textarea>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="print_bill" name="print_bill" value="1" checked>
                        <label class="form-check-label" for="print_bill">
                            Print Bill
                        </label>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save mr-1"></i> Save Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Include Customer Modal for adding new customers -->
<?php require_once 'modals/customer_modal.php'; ?>

<!-- Include SweetAlert2 library -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
.phone-suggestions {
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
.phone-suggestion-item {
    padding: 10px 15px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
}
.phone-suggestion-item:hover {
    background-color: #f8f9fa;
}
.phone-suggestion-item:last-child {
    border-bottom: none;
}
.customer-name {
    font-weight: 600;
}
.customer-phone {
    color: #6c757d;
    font-size: 0.85em;
    display: block;
}
</style>

<script>
$(document).ready(function() {
    let selectedCustomer = null;
    let typingTimer;
    const doneTypingInterval = 300; // 300ms
    
    // Monitor phone number input
    $('#phone-search').on('input', function() {
        let searchTerm = $(this).val();
        
        if(searchTerm.length < 2) {
            $('#phone-suggestions').hide();
            return;
        }
        
        // Clear the timeout
        clearTimeout(typingTimer);
        
        // Set a new timeout to fetch suggestions after user stops typing
        typingTimer = setTimeout(function() {
            fetchPhoneSuggestions(searchTerm);
        }, doneTypingInterval);
    });
    
    // Fetch phone suggestions via AJAX
    function fetchPhoneSuggestions(searchTerm) {
        $.ajax({
            url: 'process/get_phone_suggestions.php',
            type: 'GET',
            data: { term: searchTerm },
            dataType: 'json',
            success: function(response) {
                if(response.success && response.customers.length > 0) {
                    displayPhoneSuggestions(response.customers);
                } else {
                    $('#phone-suggestions').hide();
                }
            },
            error: function() {
                console.error('Error fetching phone suggestions');
            }
        });
    }
    
    // Display phone suggestions
    function displayPhoneSuggestions(customers) {
        let suggestions = '';
        customers.forEach(function(customer) {
            suggestions += `<div class="phone-suggestion-item" data-phone="${customer.telephone}">
                <span class="customer-name">${customer.name}</span>
                <span class="customer-phone">${customer.telephone}</span>
            </div>`;
        });
        
        $('#phone-suggestions').html(suggestions).show();
        
        // Handle suggestion click
        $('.phone-suggestion-item').click(function() {
            const phoneNumber = $(this).data('phone');
            $('#phone-search').val(phoneNumber);
            $('#phone-suggestions').hide();
            
            // Search for this phone number
            searchCustomerByPhone(phoneNumber);
        });
    }
    
    // Search for customer when Enter key is pressed in the phone search field
    $('#phone-search').keypress(function(e) {
        if(e.which === 13) {
            e.preventDefault();
            const phoneNumber = $(this).val().trim();
            if(phoneNumber) {
                searchCustomerByPhone(phoneNumber);
            }
        }
    });
    
    // Hide phone suggestions when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#phone-search, #phone-suggestions').length) {
            $('#phone-suggestions').hide();
        }
    });
    
    // Search customer by phone
    function searchCustomerByPhone(phoneNumber) {
        $.ajax({
            url: 'process/search_customers_by_phone.php',
            type: 'GET',
            data: { phone: phoneNumber },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.customers.length > 0) {
                    // Show customer list
                    displayCustomerResults(response.customers);
                } else {
                    // No customers found
                    $('#customer-search-results').hide();
                    $('#selected-customer-info').hide();
                    $('#no-customer-found').show();
                }
            },
            error: function() {
                alert('Error searching for customers. Please try again.');
            }
        });
    }
    
    // Display customer search results
    function displayCustomerResults(customers) {
        let html = '';
        
        customers.forEach(function(customer) {
            html += `<a href="#" class="list-group-item list-group-item-action customer-result" 
                       data-id="${customer.id}" 
                       data-name="${customer.name}"
                       data-phone="${customer.telephone}"
                       data-nic="${customer.nic}"
                       data-address="${customer.address}"
                       data-credit="${customer.credit_limit}">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-1">${customer.name}</h6>
                            <span class="badge badge-primary badge-pill">Rs. ${customer.credit_limit}</span>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-phone-alt mr-1"></i> ${customer.telephone} | 
                            <i class="fas fa-id-card mr-1"></i> ${customer.nic}
                        </small>
                    </a>`;
        });
        
        $('#customer-results-list').html(html);
        $('#customer-search-results').show();
        $('#no-customer-found').hide();
        $('#selected-customer-info').hide();
        
        // Handle customer selection
        $('.customer-result').click(function(e) {
            e.preventDefault();
            
            // Store selected customer data
            selectedCustomer = {
                id: $(this).data('id'),
                name: $(this).data('name'),
                phone: $(this).data('phone'),
                nic: $(this).data('nic'),
                address: $(this).data('address'),
                credit_limit: $(this).data('credit')
            };
            
            // Display customer info
            $('#customer-name-display').text(selectedCustomer.name);
            $('#customer-phone-display').text(selectedCustomer.phone);
            $('#customer-nic-display').text(selectedCustomer.nic);
            $('#customer-address-display').text(selectedCustomer.address);
            $('#customer-credit-limit-display').text(selectedCustomer.credit_limit);
            
            // Set customer ID in form
            $('#customer_id').val(selectedCustomer.id);
            
            // Check for existing advance payments
            checkExistingAdvancePayment(selectedCustomer.id);
            
            // Hide search results, show customer info
            $('#customer-search-results').hide();
            $('#selected-customer-info').show();
        });
    }
    
    // Check if the customer already has an advance payment
    function checkExistingAdvancePayment(customerId) {
        $.ajax({
            url: 'process/check_advance_payment.php',
            type: 'GET',
            data: { customer_id: customerId },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.has_advance) {
                    $('#customer-advance-display').text(parseFloat(response.amount).toFixed(2));
                    $('#advance_bill_number').val(response.bill_number);
                } else {
                    $('#customer-advance-display').text('0.00');
                }
            },
            error: function() {
                console.error('Error checking advance payment');
            }
        });
    }
    
    // Advance payment form submission
    $('#advance-payment-form').submit(function(e) {
        e.preventDefault();
        
        if (!selectedCustomer) {
            alert('Please select a customer first');
            return false;
        }
        
        // Get form data
        const formData = {
            customer_id: $('#customer_id').val(),
            customer_name: selectedCustomer.name,
            payment_amount: $('#payment_amount').val(),
            payment_type: $('#payment_type').val(),
            reason: $('#reason').val(),
            print_bill: $('#print_bill').is(':checked') ? 1 : 0,
            advance_bill_number: $('#advance_bill_number').val()
        };
        
        // Validate form
        if (!formData.payment_amount || !formData.payment_type) {
            alert('Please fill in all required fields');
            return false;
        }
        
        // Send AJAX request
        $.ajax({
            url: 'process/save_advance_payment.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    if (formData.print_bill) {
                        // Open bill in new window with POS format
                        window.open(`../invoice/advance_bill_pos.php?advance_bill_number=${response.bill_number}`, '_blank', 'width=350,height=600');
                    }
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        showConfirmButton: true
                    }).then((result) => {
                        // Clear form
                        $('#payment_amount').val('');
                        $('#payment_type').val('');
                        $('#reason').val('');
                        
                        // Update the displayed advance amount
                        $('#customer-advance-display').text(parseFloat(response.new_amount).toFixed(2));
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error saving advance payment. Please try again.'
                });
            }
        });
    });
});
</script>
