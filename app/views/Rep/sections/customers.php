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
                    <select class="form-control ml-2" id="route-filter" name="route">
                        <option value="">All Routes</option>
                        <?php
                        // Fetch routes for the dropdown
                        try {
                            $route_stmt = $conn->prepare("SELECT id, name FROM routes ORDER BY name");
                            $route_stmt->execute();
                            $route_result = $route_stmt->get_result();
                            
                            while ($route = $route_result->fetch_assoc()) {
                                $selected = (isset($_GET['route']) && $_GET['route'] == $route['id']) ? 'selected' : '';
                                echo "<option value='{$route['id']}' $selected>{$route['name']}</option>";
                            }
                            $route_stmt->close();
                        } catch (Exception $e) {
                            echo "<option value=''>Error loading routes</option>";
                        }
                        ?>
                    </select>
                    <div class="input-group-append">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
            </form>
            <div class="button-group">
                <button type="button" class="btn btn-info mr-2" data-toggle="modal" data-target="#route-modal">
                    <i class="fas fa-route"></i> Routes
                </button>
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#add-customer-modal">
                    <i class="fas fa-plus"></i> Add Customer
                </button>
            </div>
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
                    $route_filter = isset($_GET['route']) && !empty($_GET['route']) ? $_GET['route'] : null;
                    
                    try {
                        // Modify the query to include route filtering
                        $query = "
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
                            WHERE (c.name LIKE ? OR c.telephone LIKE ? OR c.nic LIKE ?)
                        ";
                        
                        // Add route filter condition if selected
                        if ($route_filter !== null) {
                            $query .= " AND c.route_id = ?";
                        }
                        
                        $query .= " ORDER BY c.name LIMIT 50";
                        
                        $stmt = $conn->prepare($query);
                        
                        if ($route_filter !== null) {
                            $stmt->bind_param("sssi", $search_term, $search_term, $search_term, $route_filter);
                        } else {
                            $stmt->bind_param("sss", $search_term, $search_term, $search_term);
                        }
                        
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

<!-- Include Route Modal -->
<div class="modal fade" id="route-modal" tabindex="-1" aria-labelledby="route-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="route-modal-title">Manage Routes</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="add-route-form" class="mb-4">
                    <div class="form-group">
                        <label for="route-name">Route Name</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="route-name" name="name" required>
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">Add Route</button>
                            </div>
                        </div>
                    </div>
                </form>
                
                <h6 class="font-weight-bold mb-3">Available Routes</h6>
                <div id="routes-container" class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="routes-list">
                            <!-- Routes will be loaded here -->
                            <tr>
                                <td colspan="3" class="text-center">Loading routes...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

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
        // Function declarations - Define all functions first
        
        // Load routes in dropdown for add/edit customer
        function loadRoutesForCustomerModal(selectedRouteId = null) {
            $.ajax({
                url: 'process/get_routes.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let options = '<option value="">-- Select Route --</option>';
                        
                        response.routes.forEach(function(route) {
                            const selected = (selectedRouteId !== null && route.id == selectedRouteId) ? 'selected' : '';
                            options += `<option value="${route.id}" ${selected}>${route.name}</option>`;
                        });
                        
                        $('#customer_route_id').html(options);
                        
                        // If selectedRouteId provided but no option was marked as selected, set it explicitly
                        if (selectedRouteId !== null) {
                            $('#customer_route_id').val(selectedRouteId);
                            console.log(`Route dropdown value set to: ${selectedRouteId}`);
                        }
                    } else {
                        console.error('Error loading routes:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading routes:', error);
                }
            });
        }
        
        // Edit customer function
        function editCustomer(customerId) {
            // Ensure we reset any previous data
            $('#customer-form')[0].reset();
            $('#customer_id').val('');
            $('#customer-form-title').text('Edit Customer');
            
            // Load routes before showing modal to prevent "function not defined" error
            loadRoutesForCustomerModal();
            
            // Send AJAX request to get customer details
            $.ajax({
                url: 'process/get_customer.php',
                type: 'GET',
                data: { id: customerId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const customer = response.customer;
                        console.log("Customer data received:", customer);
                        
                        // Populate form with customer details
                        $('#customer_id').val(customer.id);
                        $('#customer_name').val(customer.name);
                        $('#customer_telephone').val(customer.telephone);
                        $('#customer_nic').val(customer.nic);
                        $('#customer_address').val(customer.address);
                        $('#customer_whatsapp').val(customer.whatsapp);
                        $('#customer_credit_limit').val(customer.credit_limit);
                        
                        // Show the modal first
                        $('#add-customer-modal').modal('show');
                        
                        // After modal is shown, update route selection
                        setTimeout(function() {
                            if (customer.route_id) {
                                $('#customer_route_id').val(customer.route_id);
                                console.log('Set route ID to:', customer.route_id);
                            }
                        }, 300);
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", xhr.responseText);
                    alert('Error retrieving customer details. Please try again.');
                }
            });
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
                                    <div class="col-4 font-weight-bold">Route:</div>
                                    <div class="col-8">${customer.route_name || '-'}</div>
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
                    
                    // Format credit balance with color styling
                    const creditBalanceVal = parseFloat(customer.credit_balance.replace(/,/g, ''));
                    const creditBalanceClass = creditBalanceVal > 0 ? 'text-danger font-weight-bold' : '';
                    const creditBalanceFormatted = `<span class='${creditBalanceClass}'>Rs. ${customer.credit_balance}</span>`;
                    
                    html += `<tr>
                        <td>${customer.name}</td>
                        <td>${customer.telephone}</td>
                        <td>${customer.nic}</td>
                        <td>Rs. ${customer.credit_limit}</td>
                        <td>${creditBalanceFormatted}</td>
                        <td>${advanceAmount}</td>
                        <td>
                            <button class='btn btn-sm btn-info view-customer' data-id='${customer.id}' title='View Details'>
                                <i class='fas fa-eye'></i>
                            </button>
                            <button class='btn btn-sm btn-primary edit-customer' data-id='${customer.id}' title='Edit'>
                                <i class='fas fa-edit'></i>
                            </button>
                            <a href='../customers/payment.php?id=${customer.id}' class='btn btn-sm btn-success' title='Make Payment'>
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
        
        // Function to load routes for route management modal
        function loadRoutes() {
            $.ajax({
                url: 'process/get_routes.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        updateRoutesTable(response.routes);
                    } else {
                        $('#routes-list').html('<tr><td colspan="3" class="text-center text-danger">' + response.message + '</td></tr>');
                    }
                },
                error: function() {
                    $('#routes-list').html('<tr><td colspan="3" class="text-center text-danger">Error loading routes</td></tr>');
                }
            });
        }
        
        // Update routes table
        function updateRoutesTable(routes) {
            let html = '';
            
            if (routes.length > 0) {
                routes.forEach(function(route) {
                    html += `<tr>
                        <td>${route.id}</td>
                        <td>${route.name}</td>
                        <td>
                            <button class="btn btn-sm btn-danger delete-route" data-id="${route.id}" title="Delete Route">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>`;
                });
            } else {
                html = '<tr><td colspan="3" class="text-center">No routes found</td></tr>';
            }
            
            $('#routes-list').html(html);
            
            // Attach event handlers to delete buttons
            $('.delete-route').click(function() {
                const routeId = $(this).data('id');
                if (confirm('Are you sure you want to delete this route?')) {
                    deleteRoute(routeId);
                }
            });
        }
        
        // Delete route function
        function deleteRoute(routeId) {
            $.ajax({
                url: 'process/delete_route.php',
                type: 'POST',
                data: { id: routeId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        loadRoutes();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error deleting route. Please try again.');
                }
            });
        }
        
        // Event bindings
        
        // Customer search form submission
        $('#customer-search-form').submit(function(e) {
            e.preventDefault();
            
            const searchTerm = $('#customer-search').val();
            const routeId = $('#route-filter').val();
            
            // Show loading indicator in table
            $('#customers-table tbody').html('<tr><td colspan="7" class="text-center"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></td></tr>');
            
            // Send AJAX request
            $.ajax({
                url: 'process/search_customers.php',
                type: 'GET',
                data: { term: searchTerm, route: routeId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        updateCustomerTable(response.customers);
                    } else {
                        $('#customers-table tbody').html('<tr><td colspan="7" class="text-center text-danger">' + response.message + '</td></tr>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", xhr.responseText);
                    $('#customers-table tbody').html('<tr><td colspan="7" class="text-center text-danger">Error searching customers. Please try again.</td></tr>');
                }
            });
        });
        
        // Load routes when route modal is shown
        $('#route-modal').on('shown.bs.modal', function() {
            loadRoutes();
        });
        
        // Add route form submission
        $('#add-route-form').submit(function(e) {
            e.preventDefault();
            const routeName = $('#route-name').val().trim();
            
            if (routeName === '') {
                alert('Please enter a route name');
                return;
            }
            
            // Send AJAX request to add route
            $.ajax({
                url: 'process/add_route.php',
                type: 'POST',
                data: { name: routeName },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#route-name').val('');
                        loadRoutes();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error adding route. Please try again.');
                }
            });
        });
        
        // Load routes when customer modal is opened for adding a new customer
        $('#add-customer-modal').on('show.bs.modal', function(e) {
            // Get the button that triggered the modal
            const button = $(e.relatedTarget);
            
            // If opened via button (not programmatically), this is an add operation
            if (button.length) {
                $('#customer-form')[0].reset();
                $('#customer_id').val('');
                $('#customer-form-title').text('Add New Customer');
                loadRoutesForCustomerModal();
            }
        });
        
        // Save customer button click event
        $('#save-customer-btn').click(function() {
            const customerId = $('#customer_id').val();
            const formData = $('#customer-form').serialize();
            
            console.log("Form data:", formData); // Debug log
            console.log("Route ID value:", $('#customer_route_id').val()); // Debug log
            
            // URL depends on whether we're adding or editing
            const url = customerId ? 'process/update_customer.php' : 'process/add_customer.php';
            
            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        $('#add-customer-modal').modal('hide');
                        
                        // Reload page after success
                        setTimeout(function() {
                            location.reload();
                        }, 500);
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error:", xhr.responseText);
                    alert('Error: ' + error);
                }
            });
        });
        
        // Reset form when modal is hidden
        $('#add-customer-modal').on('hidden.bs.modal', function() {
            $('#customer-form')[0].reset();
            $('#customer_id').val('');
            $('#customer-form-title').text('Add New Customer');
        });
        
        // Initialize event handlers
        attachCustomerEventHandlers();
    });
</script>
