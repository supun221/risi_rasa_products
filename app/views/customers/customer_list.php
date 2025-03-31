<!DOCTYPE html>
<html>

<head>

    <title>Customer List</title>
    <link rel="stylesheet" href="../../assets/css/user_styles.css">
    <style>
        .add-customer {
            background-color: rgb(30, 21, 203);
            /* Green for adding */
            color: #ffffff;
            /* White text */
            border: none;
            border-radius: 4px;
            /* Rounded corners */
            padding: 10px 20px;
            /* Padding for a nice size */
            font-size: 16px;
            /* Text size */
            cursor: pointer;
            /* Pointer cursor on hover */
            transition: background-color 0.3s ease, transform 0.2s ease;
            /* Smooth transition */
        }

        .add-customer:hover {
            background-color: rgb(46, 26, 180);
            /* Darker green on hover */
            transform: scale(1.05);
            /* Slight zoom effect on hover */
        }

        .payment {
            background-color: rgb(255, 193, 7);
            /* Yellow background */
            color: white;
            /* Ensures text is white */
            border: none;
            /* Removes any borders */
            border-radius: 4px;
            /* Slightly rounded corners */
            padding: 10px 20px;
            /* Padding for button size */
            font-size: 16px;
            /* Text size */
            cursor: pointer;
            /* Pointer cursor on hover */
            transition: background-color 0.3s ease, transform 0.2s ease;
            /* Smooth hover effects */
        }

        .payment:hover {
            background-color: rgb(255, 179, 0);
            /* Darker yellow on hover */
            transform: scale(1.05);
            /* Slight zoom effect */
            color: white;
            /* Ensures text remains white on hover */
        }

        /* Popup and Table Styles */
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

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }

        .action-buttons a {
            margin-right: 5px;
            text-decoration: none;
            color: #007bff;
        }

        .action-buttons a:hover {
            text-decoration: underline;
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


    <!DOCTYPE html>
    <html>

    <head>
        <title>Customer List</title>
        <link rel="stylesheet" href="../../assets/css/user_styles.css">
        <style>
            /* Your existing CSS styles */
        </style>
    </head>

    <body>
        <h1>Customer List</h1>
        <button type="button" class="add-customer" data-toggle="modal" data-target="#addCustomerModal">Add customer</button>
        <?php require_once 'add_customer.php'; // Include add customer modal 
        ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Customer Name</th>
                        <th>Telephone</th>
                        <th>NIC</th>
                        <th>Address</th>
                        <th>Credit limit</th>
                        <th>Credit Balance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($customers)) {
                        foreach ($customers as $index => $customer) {
                            $customerId = $customer['id'];
                            $creditBalance = isset($creditBalances[$customerId]) ? $creditBalances[$customerId] : 0.00;

                            echo '<tr id="customer-row-' . htmlspecialchars($customerId) . '">';
                            echo '<td>' . ($index + 1) . '</td>';
                            echo '<td>' . htmlspecialchars($customer['name']) . '</td>';
                            echo '<td>' . htmlspecialchars($customer['telephone']) . '</td>';
                            echo '<td>' . htmlspecialchars($customer['nic']) . '</td>';
                            echo '<td>' . htmlspecialchars($customer['address']) . '</td>';
                            echo '<td>' . htmlspecialchars($customer['credit_limit']) . '</td>';
                            echo '<td>' . number_format($creditBalance, 2) . '</td>'; // Display credit balance
                            echo '<td class="action-buttons">
                            <a href="javascript:void(0);" onclick="loadCustomerDetails(' . htmlspecialchars($customerId) . ')" class="edit">Edit</a>
                            <button onclick="confirmDelete(' . htmlspecialchars($customerId) . ')" class="delete">Delete</button>
                            <a href="payment.php?id=' . urlencode($customerId) . '" class="payment">Payment</a>
                        </td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="7">No customers found.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
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