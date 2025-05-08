<?php
// Customers management logic
require_once '../../../../config/databade.php';

// Define base URL
// $base_url = '/risi_rasa_products';
// ?>

<div class="section-card fade-transition" id="customer-section">
    <div class="section-header">
        <i class="fas fa-users"></i> Customer Management
    </div>
    <div class="section-body">
        <a href="#" class="return-link" id="return-from-customer">
            <i class="fas fa-chevron-left"></i> Return to Dashboard
        </a>
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <form id="customer-search-form" class="form-inline flex-grow-1 mr-2">
                <div class="input-group w-100">
                    <input type="text" class="form-control" id="customer-search" name="search" placeholder="Search by name, phone or NIC">
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
            </form>
            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#add-customer-modal">
                <i class="fas fa-plus"></i> Add Customer
            </button>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped" id="customers-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>NIC</th>
                        <th>Credit Limit</th>
                        <th>Credit Balance</th>
                        <th>Advance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Fetch customers from database
                    $search_term = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : '%';
                    
                    try {
                        $stmt = $conn->prepare("
                            SELECT c.id, c.name, c.telephone, c.nic, c.address, c.whatsapp, c.credit_limit, c.credit_balance,
                                   COALESCE(ap.net_amount, 0) as advance_amount
                            FROM customers c
                            LEFT JOIN (
                                SELECT customer_id, net_amount 
                                FROM advance_payments 
                                WHERE id IN (
                                    SELECT MAX(id) 
                                    FROM advance_payments 
                                    GROUP BY customer_id
                                )
                            ) ap ON c.id = ap.customer_id
                            WHERE c.name LIKE ? OR c.telephone LIKE ? OR c.nic LIKE ?
                            ORDER BY c.name
                            LIMIT 50
                        ");
                        $stmt->bind_param("sss", $search_term, $search_term, $search_term);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows > 0) {
                            while ($customer = $result->fetch_assoc()) {
                                // Format advance amount - add text color based on value
                                $advanceClass = $customer['advance_amount'] > 0 ? 'text-success font-weight-bold' : '';
                                $advanceAmount = $customer['advance_amount'] > 0 ? 
                                    "<span class='$advanceClass'>Rs. " . number_format((float)$customer['advance_amount'], 2) . '</span>' : 
                                    'Rs. 0.00';
                                
                                // Format credit balance - add text color based on value
                                $creditBalance = (float)$customer['credit_balance'];
                                $creditBalanceClass = $creditBalance > 0 ? 'text-danger font-weight-bold' : '';
                                $creditBalanceFormatted = "<span class='$creditBalanceClass'>Rs. " . number_format($creditBalance, 2) . '</span>';
                                
                                echo "<tr>
                                    <td>{$customer['name']}</td>
                                    <td>{$customer['telephone']}</td>
                                    <td>{$customer['nic']}</td>
                                    <td>Rs. " . number_format((float)$customer['credit_limit'], 2) . "</td>
                                    <td>{$creditBalanceFormatted}</td>
                                    <td>{$advanceAmount}</td>
                                    <td>
                                        <button class='btn btn-sm btn-info view-customer' data-id='{$customer['id']}' title='View Details'>
                                            <i class='fas fa-eye'></i>
                                        </button>
                                        <button class='btn btn-sm btn-primary edit-customer' data-id='{$customer['id']}' title='Edit'>
                                            <i class='fas fa-edit'></i>
                                        </button>
                                        <a href='../customers/payment.php?id={$customer['id']}' class='btn btn-sm btn-success' title='Make Payment'>
                                            <i class='fas fa-money-bill-wave'></i>
                                        </a>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center'>No customers found</td></tr>";
                        }
                    } catch (Exception $e) {
                        echo "<tr><td colspan='7' class='text-center text-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Include Customer Modal -->
<?php require_once 'modals/customer_modal.php'; ?>

<!-- Customer View Modal -->
<div class="modal fade" id="view-customer-modal" tabindex="-1" aria-labelledby="view-customer-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="view-customer-title">Customer Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="customer-details-container">
                <!-- Customer details will be loaded here -->
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Customer search form submission
        $('#customer-search-form').submit(function(e) {
            e.preventDefault();
            
            const searchTerm = $('#customer-search').val();
            
            // Send AJAX request
            $.ajax({
                url: 'process/search_customers.php',
                type: 'GET',
                data: { term: searchTerm },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        updateCustomerTable(response.customers);
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error searching customers. Please try again.');
                }
            });
        });
        
        // Update customer table with search results
        function updateCustomerTable(customers) {
            let html = '';
            
            if (customers.length > 0) {
                customers.forEach(function(customer) {
                    // Format advance amount with color styling
                    const advanceClass = customer.advance_amount > 0 ? 'text-success font-weight-bold' : '';
                    const advanceAmount = customer.advance_amount > 0 ? 
                        `<span class='${advanceClass}'>Rs. ${parseFloat(customer.advance_amount).toFixed(2)}</span>` : 
                        'Rs. 0.00';
                    
                    html += `<tr>
                        <td>${customer.name}</td>
                        <td>${customer.telephone}</td>
                        <td>${customer.nic}</td>
                        <td>Rs. ${customer.credit_limit}</td>
                        <td>Rs. ${customer.credit_balance}</td>
                        <td>${advanceAmount}</td>
                        <td>
                            <button class='btn btn-sm btn-info view-customer' data-id='${customer.id}' title='View Details'>
                                <i class='fas fa-eye'></i>
                            </button>
                            <button class='btn btn-sm btn-primary edit-customer' data-id='${customer.id}' title='Edit'>
                                <i class='fas fa-edit'></i>
                            </button>
                            <a href='{$base_url}/app/views/customers/payment.php?id=${customer.id}' class='btn btn-sm btn-success' title='Make Payment'>
                                <i class='fas fa-money-bill-wave'></i>
                            </a>
                        </td>
                    </tr>`;
                });
            } else {
                html = "<tr><td colspan='7' class='text-center'>No customers found</td></tr>";
            }
            
            $('#customers-table tbody').html(html);
            
            // Re-attach event handlers
            attachCustomerEventHandlers();
        }
        
        // Function to attach event handlers for dynamic content
        function attachCustomerEventHandlers() {
            // View customer details
            $('.view-customer').click(function() {
                const customerId = $(this).data('id');
                viewCustomerDetails(customerId);
            });
            
            // Edit customer
            $('.edit-customer').click(function() {
                const customerId = $(this).data('id');
                editCustomer(customerId);
            });
        }
        
        // View customer details
        function viewCustomerDetails(customerId) {
            $('#customer-details-container').html('<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
            $('#view-customer-modal').modal('show');
            
            // Send AJAX request to get customer details
            $.ajax({
                url: 'process/get_customer.php',
                type: 'GET',
                data: { id: customerId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const customer = response.customer;
                        
                        // Format advance amount with color styling
                        const advanceClass = customer.advance_amount > 0 ? 'text-success font-weight-bold' : '';
                        const advanceAmount = customer.advance_amount > 0 ? 
                            `<span class='${advanceClass}'>Rs. ${parseFloat(customer.advance_amount).toFixed(2)}</span>` : 
                            'Rs. 0.00';
                        
                        // Format customer details
                        let html = `
                            <div class="customer-info">
                                <h4 class="font-weight-bold mb-3">${customer.name}</h4>
                                
                                <div class="row mb-2">
                                    <div class="col-4 font-weight-bold">Phone:</div>
                                    <div class="col-8">${customer.telephone}</div>
                                </div>
                                
                                <div class="row mb-2">
                                    <div class="col-4 font-weight-bold">WhatsApp:</div>
                                    <div class="col-8">${customer.whatsapp}</div>
                                </div>
                                
                                <div class="row mb-2">
                                    <div class="col-4 font-weight-bold">NIC:</div>
                                    <div class="col-8">${customer.nic}</div>
                                </div>
                                
                                <div class="row mb-2">
                                    <div class="col-4 font-weight-bold">Address:</div>
                                    <div class="col-8">${customer.address}</div>
                                </div>
                                
                                <div class="row mb-2">
                                    <div class="col-4 font-weight-bold">Credit Limit:</div>
                                    <div class="col-8">Rs. ${customer.credit_limit}</div>
                                </div>
                                
                                <div class="row mb-2">
                                    <div class="col-4 font-weight-bold">Credit Balance:</div>
                                    <div class="col-8">Rs. ${customer.credit_balance}</div>
                                </div>
                                
                                <div class="row mb-2">
                                    <div class="col-4 font-weight-bold">Advance Amount:</div>
                                    <div class="col-8">${advanceAmount}</div>
                                </div>
                                
                                <div class="row mb-2">
                                    <div class="col-4 font-weight-bold">Branch:</div>
                                    <div class="col-8">${customer.branch}</div>
                                </div>
                            </div>
                        `;
                        
                        $('#customer-details-container').html(html);
                    } else {
                        $('#customer-details-container').html('<div class="alert alert-danger">Error: ' + response.message + '</div>');
                    }
                },
                error: function() {
                    $('#customer-details-container').html('<div class="alert alert-danger">Error retrieving customer details. Please try again.</div>');
                }
            });
        }
        
        // Edit customer function
        function editCustomer(customerId) {
            // Send AJAX request to get customer details
            $.ajax({
                url: 'process/get_customer.php',
                type: 'GET',
                data: { id: customerId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const customer = response.customer;
                        
                        // Populate form with customer details
                        $('#customer-form-title').text('Edit Customer');
                        $('#customer_id').val(customer.id);
                        $('#customer_name').val(customer.name);
                        $('#customer_telephone').val(customer.telephone);
                        $('#customer_nic').val(customer.nic);
                        $('#customer_address').val(customer.address);
                        $('#customer_whatsapp').val(customer.whatsapp);
                        $('#customer_credit_limit').val(customer.credit_limit);
                        
                        // Show modal
                        $('#add-customer-modal').modal('show');
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error retrieving customer details. Please try again.');
                }
            });
        }
        
        // Initialize event handlers
        attachCustomerEventHandlers();
    });
</script>
