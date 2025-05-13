<!DOCTYPE html>
<html>

<head>
    <title>Customer Management | Risi Rasa Products</title>
    <link rel="stylesheet" href="../../assets/css/user_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* General Layout & Typography */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .page-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
            color: #2c3e50;
        }

        .content-wrapper {
            max-width: 1400px;
            margin: 0 auto;
            padding: 25px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        /* Add Customer Button */
        .add-customer {
            background-color: #4361ee;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            padding: 12px 20px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 3px 6px rgba(67, 97, 238, 0.2);
        }

        .add-customer:hover {
            background-color: #3249c2;
            transform: translateY(-2px);
            box-shadow: 0 5px 8px rgba(67, 97, 238, 0.3);
        }

        .add-customer:active {
            transform: translateY(0);
        }

        .add-customer i {
            font-size: 16px;
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background-color: #fff;
            font-size: 14px;
        }

        table th {
            background-color: #f8f9fa;
            color: #516173;
            font-weight: 600;
            padding: 14px 12px;
            text-align: left;
            border-bottom: 2px solid #e0e0e0;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        table td {
            padding: 14px 12px;
            border-bottom: 1px solid #f0f0f0;
            color: #3a3f51;
            vertical-align: middle;
        }

        table tr:last-child td {
            border-bottom: none;
        }

        table tr:hover {
            background-color: #f8faff;
        }

        /* Status Indicators */
        .credit-balance {
            font-weight: 600;
            color: #2c3e50;
        }

        .credit-limit {
            font-weight: 500;
            color: #4361ee;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .action-buttons a, 
        .action-buttons button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            border-radius: 6px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .action-btn-edit {
            background-color: #4361ee;
            width: 36px;
            height: 36px;
        }

        .action-btn-edit:hover {
            background-color: #3249c2;
            transform: translateY(-2px);
            box-shadow: 0 3px 6px rgba(67, 97, 238, 0.2);
        }

        .action-btn-delete {
            background-color: #ef476f;
            width: 36px;
            height: 36px;
        }

        .action-btn-delete:hover {
            background-color: #d64161;
            transform: translateY(-2px);
            box-shadow: 0 3px 6px rgba(239, 71, 111, 0.2);
        }

        .payment {
            background-color: #fca311;
            color: white;
            font-weight: 500;
            padding: 8px 14px;
            border-radius: 6px;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(252, 163, 17, 0.2);
        }

        .payment:hover {
            background-color: #e09010;
            transform: translateY(-2px);
            box-shadow: 0 3px 6px rgba(252, 163, 17, 0.3);
        }

        /* Modal and popup styles remain the same */
        .popup-container {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .popup {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .popup h2 {
            margin-top: 0;
        }

        .popup form {
            display: flex;
            flex-direction: column;
        }

        .popup form input {
            margin-bottom: 10px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .popup form button {
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .popup form button:hover {
            background-color: #0056b3;
        }

        /* Route Management Styles */
        .action-buttons-container {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .route-btn {
            background-color: #2ecc71;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            padding: 12px 20px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 3px 6px rgba(46, 204, 113, 0.2);
        }
        
        .route-btn:hover {
            background-color: #27ae60;
            transform: translateY(-2px);
            box-shadow: 0 5px 8px rgba(46, 204, 113, 0.3);
        }
        
        #routesTable {
            width: 100%;
            margin-top: 15px;
        }
        
        #routesTable th {
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>
    <?php
    require_once '../header1.php';
    require_once '../../../config/databade.php'; // Database connection

    // Fetch customers from the database with credit_balance and route name included
    $customers = [];
    $query = "SELECT c.id, c.name, c.telephone, c.nic, c.credit_limit, c.credit_balance, c.address, r.name as route_name 
              FROM customers c
              LEFT JOIN routes r ON c.route_id = r.id";
    $result = mysqli_query($conn, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $customers[] = $row;
        }
    } else {
        echo "<p>Error fetching customers: " . mysqli_error($conn) . "</p>";
    }

    include 'update_customer.php'; // Include update customer modal
    ?>

    <div class="content-wrapper">
        <div class="page-header">
            <h1>Customer Management</h1>
            <div class="action-buttons-container">
                <button type="button" class="route-btn" id="addRouteBtn">
                    <i class="fas fa-route"></i> Add Route
                </button>
                <button type="button" class="route-btn" id="viewRoutesBtn">
                    <i class="fas fa-map"></i> View Routes
                </button>
                <button type="button" class="add-customer" data-toggle="modal" data-target="#addCustomerModal">
                    <i class="fas fa-plus-circle"></i> Add Customer
                </button>
            </div>
        </div>
        
        <?php require_once 'add_customer.php'; // Include add customer modal ?>

        <!-- Route Management Modal -->
        <div class="modal fade" id="routeModal" tabindex="-1" role="dialog" aria-labelledby="routeModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="routeModalLabel">Manage Routes</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div id="addRouteForm" style="margin-bottom: 20px;">
                            <h6>Add New Route</h6>
                            <form id="routeForm" class="form-inline">
                                <div class="form-group mb-2" style="width: 100%;">
                                    <input type="text" class="form-control mr-2" id="routeName" placeholder="Route Name" style="width: 70%;">
                                    <button type="submit" class="btn btn-primary">Save Route</button>
                                </div>
                            </form>
                        </div>
                        
                        <div id="routeTableContainer">
                            <h6>Available Routes</h6>
                            <table class="table table-bordered table-sm" id="routesTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Route Name</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="routesTableBody">
                                    <!-- Routes will be populated here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="18%">Customer Name</th>
                        <th width="10%">Telephone</th>
                        <th width="10%">NIC</th>
                        <th width="13%">Address</th>
                        <th width="8%">Route</th>
                        <th width="8%">Credit limit</th>
                        <th width="10%">Credit Balance</th>
                        <th width="18%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($customers)) {
                        foreach ($customers as $index => $customer) {
                            $customerId = $customer['id'];
                            
                            // Use the credit_balance directly from customers table
                            $creditBalance = isset($customer['credit_balance']) ? $customer['credit_balance'] : 0.00;
                            
                            // Ensure credit_limit is numeric
                            $creditLimit = is_numeric($customer['credit_limit']) ? $customer['credit_limit'] : 0.00;
                            
                            // Get route name
                            $routeName = isset($customer['route_name']) ? htmlspecialchars($customer['route_name']) : '-';

                            echo '<tr id="customer-row-' . htmlspecialchars($customerId) . '">';
                            echo '<td>' . ($index + 1) . '</td>';
                            echo '<td><strong>' . htmlspecialchars($customer['name']) . '</strong></td>';
                            echo '<td>' . htmlspecialchars($customer['telephone']) . '</td>';
                            echo '<td>' . htmlspecialchars($customer['nic']) . '</td>';
                            echo '<td>' . htmlspecialchars($customer['address']) . '</td>';
                            echo '<td>' . $routeName . '</td>';
                            echo '<td class="credit-limit">' . number_format((float)$creditLimit, 2) . '</td>';
                            echo '<td class="credit-balance">' . number_format((float)$creditBalance, 2) . '</td>';
                            echo '<td class="action-buttons">
                            <a href="javascript:void(0);" onclick="loadCustomerDetails(' . htmlspecialchars($customerId) . ')" class="action-btn-edit" title="Edit Customer"><i class="fas fa-pencil-alt"></i></a>
                            <button onclick="confirmDelete(' . htmlspecialchars($customerId) . ')" class="action-btn-delete" title="Delete Customer"><i class="fas fa-trash-alt"></i></button>
                            <a href="payment.php?id=' . urlencode($customerId) . '" class="payment" title="Make Payment"><i class="fas fa-wallet"></i> Payment</a>
                        </td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="9" style="text-align:center;padding:20px;">No customers found.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function confirmDelete(customerId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send DELETE request using POST
                    fetch('../../controllers/customer_controller.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                action: 'delete',
                                id: customerId,
                            }),
                        })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.status === 'success') {
                                Swal.fire('Deleted!', data.message, 'success');
                                // Remove the row from the table
                                const row = document.getElementById('customer-row-' + customerId);
                                if (row) row.remove();
                            } else {
                                Swal.fire('Error!', data.message, 'error');
                            }
                        })
                        .catch((error) => {
                            console.error('Error:', error);
                            Swal.fire('Error!', 'Something went wrong.', 'error');
                        });
                }
            });
        }

        function loadCustomerDetails(customerId) {
            // Show loading indicator
            Swal.fire({
                title: 'Loading...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Fetch customer details using AJAX
            fetch('../../controllers/customer_controller.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'get',
                        id: customerId,
                    }),
                })
                .then((response) => {
                    // Check if response is ok
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(text => {
                    // Try to clean the response of any PHP warnings/errors before parsing
                    try {
                        // Check if response contains HTML (likely PHP warnings)
                        if (text.includes('<!DOCTYPE') || text.includes('<br') || text.includes('<b>Warning</b>')) {
                            // Try to extract the JSON part - look for the first '{' and last '}'
                            const jsonStart = text.indexOf('{');
                            const jsonEnd = text.lastIndexOf('}') + 1;
                            
                            if (jsonStart !== -1 && jsonEnd !== -1) {
                                const jsonPart = text.substring(jsonStart, jsonEnd);
                                console.log('Extracted JSON:', jsonPart);
                                return JSON.parse(jsonPart);
                            } else {
                                throw new Error('Could not extract valid JSON from response');
                            }
                        } else {
                            // No HTML, just parse normally
                            return JSON.parse(text);
                        }
                    } catch (error) {
                        console.error('Error parsing JSON:', error);
                        console.error('Raw response:', text);
                        Swal.close(); // Close loading indicator
                        throw new Error('Invalid JSON response');
                    }
                })
                .then((data) => {
                    // Close loading indicator
                    Swal.close();
                    
                    if (data.status === 'success') {
                        const customer = data.data;
                        console.log('Customer data:', customer); // Debug log
                        
                        // Populate the modal fields
                        document.getElementById('update-customer-id').value = customer.id;
                        document.getElementById('update-name').value = customer.name;
                        document.getElementById('update-telephone').value = customer.telephone;
                        document.getElementById('update-nic').value = customer.nic;
                        document.getElementById('update-address').value = customer.address;
                        document.getElementById('update-whatsapp').value = customer.whatsapp;
                        document.getElementById('update-email').value = customer.email;
                        document.getElementById('update-birthday').value = customer.birthday;
                        document.getElementById('update-credit-limit').value = customer.credit_limit;
                        document.getElementById('update-discount').value = customer.discount;
                        document.getElementById('update-price-type').value = customer.price_type;
                        
                        // Set route_id in the dropdown with proper handling
                        if (document.getElementById('update-route-id')) {
                            // Use a timeout to ensure the dropdown is populated
                            setTimeout(() => {
                                const routeDropdown = document.getElementById('update-route-id');
                                if (customer.route_id) {
                                    routeDropdown.value = customer.route_id;
                                    console.log('Set route_id to:', customer.route_id);
                                } else {
                                    routeDropdown.value = '';
                                    console.log('No route_id found, set to empty');
                                }
                            }, 300);
                        }
                        
                        // Show the modal
                        $('#updateCustomerModal').modal('show');
                    } else {
                        Swal.fire('Error!', data.message, 'error');
                    }
                })
                .catch((error) => {
                    Swal.close(); // Close loading indicator if still open
                    console.error('Error:', error);
                    Swal.fire('Error!', 'Failed to load customer details. ' + error.message, 'error');
                });
        }
        
        document.addEventListener("keydown", function(event) {
            if (event.code === "Home") {
                window.location.href = "../dashboard/index.php";
            }
        });
        
        // Route Management Scripts
        document.addEventListener('DOMContentLoaded', function() {
            const addRouteBtn = document.getElementById('addRouteBtn');
            const viewRoutesBtn = document.getElementById('viewRoutesBtn');
            
            // Handle Add Route button click
            addRouteBtn.addEventListener('click', function() {
                $('#routeModal').modal('show');
                document.getElementById('addRouteForm').style.display = 'block';
                loadRoutes();
            });
            
            // Handle View Routes button click
            viewRoutesBtn.addEventListener('click', function() {
                $('#routeModal').modal('show');
                document.getElementById('addRouteForm').style.display = 'none';
                loadRoutes();
            });
            
            // Handle Route Form Submit
            document.getElementById('routeForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const routeName = document.getElementById('routeName').value.trim();
                
                if (routeName === '') {
                    Swal.fire('Error!', 'Please enter a route name', 'error');
                    return;
                }
                
                // Send route data to server
                fetch('../../controllers/route_controller.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'add',
                        name: routeName
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire('Success', 'Route added successfully', 'success');
                        document.getElementById('routeName').value = '';
                        loadRoutes();
                    } else {
                        Swal.fire('Error!', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error!', 'Something went wrong', 'error');
                });
            });
            
            function loadRoutes() {
                fetch('../../controllers/route_controller.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ action: 'list' }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const routesTableBody = document.getElementById('routesTableBody');
                        routesTableBody.innerHTML = '';
                        
                        data.data.forEach(route => {
                            const row = document.createElement('tr');
                            
                            const idCell = document.createElement('td');
                            idCell.textContent = route.id;
                            
                            const nameCell = document.createElement('td');
                            nameCell.textContent = route.name;
                            
                            const actionsCell = document.createElement('td');
                            const deleteBtn = document.createElement('button');
                            deleteBtn.className = 'btn btn-sm btn-danger';
                            deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
                            deleteBtn.onclick = function() {
                                deleteRoute(route.id);
                            };
                            
                            actionsCell.appendChild(deleteBtn);
                            
                            row.appendChild(idCell);
                            row.appendChild(nameCell);
                            row.appendChild(actionsCell);
                            
                            routesTableBody.appendChild(row);
                        });
                    } else {
                        Swal.fire('Error!', data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error!', 'Failed to load routes', 'error');
                });
            }
            
            function deleteRoute(routeId) {
                Swal.fire({
                    title: 'Delete Route',
                    text: 'Are you sure you want to delete this route?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('../../controllers/route_controller.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                action: 'delete',
                                id: routeId
                            }),
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                Swal.fire('Deleted!', 'Route has been deleted.', 'success');
                                loadRoutes();
                            } else {
                                Swal.fire('Error!', data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Error!', 'Failed to delete route', 'error');
                        });
                    }
                });
            }
        });
    </script>
</body>

</html>