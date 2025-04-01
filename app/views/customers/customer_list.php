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
    </style>
</head>

<body>
    <?php
    require_once '../header1.php';
    require_once '../../../config/databade.php'; // Database connection

    // Fetch customers from the database
    $customers = [];
    $query = "SELECT id, name, telephone, nic,credit_limit, address FROM customers";
    $result = mysqli_query($conn, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $customers[] = $row;
        }
    } else {
        echo "<p>Error fetching customers: " . mysqli_error($conn) . "</p>";
    }

    // Fetch credit balances for each customer
    $creditBalances = [];
    $creditQuery = "SELECT customer_id, SUM(gross_amount) AS total_credit 
                FROM bill_records 
                WHERE payment_type = 'credit_payment' 
                GROUP BY customer_id";
    $creditResult = mysqli_query($conn, $creditQuery);

    if ($creditResult) {
        while ($row = mysqli_fetch_assoc($creditResult)) {
            $creditBalances[$row['customer_id']] = $row['total_credit'];
        }
    } else {
        echo "<p>Error fetching credit balances: " . mysqli_error($conn) . "</p>";
    }

    include 'update_customer.php'; // Include update customer modal
    ?>

    <div class="content-wrapper">
        <div class="page-header">
            <h1>Customer Management</h1>
            <button type="button" class="add-customer" data-toggle="modal" data-target="#addCustomerModal">
                <i class="fas fa-plus-circle"></i> Add Customer
            </button>
        </div>
        
        <?php require_once 'add_customer.php'; // Include add customer modal ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="20%">Customer Name</th>
                        <th width="12%">Telephone</th>
                        <th width="12%">NIC</th>
                        <th width="15%">Address</th>
                        <th width="10%">Credit limit</th>
                        <th width="10%">Credit Balance</th>
                        <th width="16%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($customers)) {
                        foreach ($customers as $index => $customer) {
                            $customerId = $customer['id'];
                            $creditBalance = isset($creditBalances[$customerId]) ? $creditBalances[$customerId] : 0.00;
                            
                            // Ensure credit_limit is numeric
                            $creditLimit = is_numeric($customer['credit_limit']) ? $customer['credit_limit'] : 0.00;

                            echo '<tr id="customer-row-' . htmlspecialchars($customerId) . '">';
                            echo '<td>' . ($index + 1) . '</td>';
                            echo '<td><strong>' . htmlspecialchars($customer['name']) . '</strong></td>';
                            echo '<td>' . htmlspecialchars($customer['telephone']) . '</td>';
                            echo '<td>' . htmlspecialchars($customer['nic']) . '</td>';
                            echo '<td>' . htmlspecialchars($customer['address']) . '</td>';
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
                        echo '<tr><td colspan="8" style="text-align:center;padding:20px;">No customers found.</td></tr>';
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
                .then((response) => response.json())
                .then((data) => {
                    if (data.status === 'success') {
                        const customer = data.data;
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
                        // Show the modal
                        $('#updateCustomerModal').modal('show');
                    } else {
                        Swal.fire('Error!', data.message, 'error');
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    Swal.fire('Error!', 'Something went wrong.', 'error');
                });
        }
        document.addEventListener("keydown", function(event) {
            if (event.code === "Home") {
                window.location.href = "../dashboard/index.php";
            }
        });
    </script>
</body>

</html>