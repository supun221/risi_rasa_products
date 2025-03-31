<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advance Customer Payment</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        body {
            background-color: #f4f4f4;
        }

        .dropdown-list {
            position: absolute;
            width: calc(92% - 30px);
            max-height: 200px;
            overflow-y: auto;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 4px;
            z-index: 1000;
            display: none;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .dropdown-item {
            padding: 10px;
            cursor: pointer;
        }

        .dropdown-item:hover,
        .dropdown-item.active {
            background-color: #007bff;
            color: #fff;
        }

        .form-check {
            display: flex;
            align-items: left;
            gap: 10px;
        }

        .form-check-label {
            margin-left: -16px;
            font-weight: bold;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            max-width: 400px;
            display: block;
        }

        .form-group textarea {
            resize: none;
        }

        .total-amount {
            background-color: #333;
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 20px;
        }

        .total-amount h1 {
            font-size: 24px;
            font-weight: bold;
        }

        .total-amount h2 {
            font-size: 36px;
            font-weight: bold;
            color: #4CAF50;
        }

        .modal-body .form-group label {
            font-weight: bold;
        }

        .relative-position {
            position: relative;
        }
    </style>
</head>

<body>
    <div class="modal fade" id="advancePaymentModal" tabindex="-1" aria-labelledby="advancePaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="advancePaymentModalLabel">Advance Payment</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="total-amount">
                        <h1>Advance Amount:</h1>
                        <h2>LKR: <span id="total-amount-display">0.00</span></h2>
                    </div>

                    <form id="advance-payment-form">
                        <div class="form-group relative-position">
                            <label for="customer-name">Customer Name:</label>
                            <input type="text" class="form-control" id="search-customer" name="customer_name" placeholder="Search customer...">
                            <ul id="customer-list" class="dropdown-list"></ul>
                        </div>

                        <div class="form-group">
                            <label for="payment-type">Payment Type:</label>
                            <select class="form-control" id="payment-type" name="payment_type">
                                <option value="cash">Cash Payment</option>
                                <option value="card">Card Payment</option>
                                <option value="bank">Bank Transfer</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="reason">Reason:</label>
                            <textarea class="form-control" id="reason" name="reason" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="net-amount">Total Amount:</label>
                            <input type="text" class="form-control" id="net-amount" name="net_amount" value="0.00">
                        </div>

                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="print-bill" name="print_bill">
                            <label class="form-check-label" for="print-bill">Print Bill</label>
                        </div>

                        <div class="btn-group">
                            <button type="submit" class="btn btn-success" id="pay-bill">Pay Bill</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Update total amount display
            $('#net-amount').on('input', function() {
                const updatedAmount = $(this).val();
                $('#total-amount-display').text(updatedAmount);
            });

            const searchCustomerInput = document.getElementById('search-customer');
            const customerDropdown = document.getElementById('customer-list');
            let activeIndex = -1;

            const fetchCustomers = (query) => {
                if (query.length < 2) {
                    customerDropdown.innerHTML = '';
                    customerDropdown.style.display = 'none';
                    activeIndex = -1;
                    return;
                }

                fetch('../../controllers/customer_controller.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'searchCustomerByPhoneNumber',
                            query,
                        }),
                    })
                    .then((response) => response.json())
                    .then((data) => {
                        customerDropdown.innerHTML = '';
                        activeIndex = -1;

                        if (data.status === 'success' && data.data.length > 0) {
                            data.data.forEach((customer, index) => {
                                const li = document.createElement('li');
                                li.textContent = customer.name;
                                li.dataset.id = customer.id;
                                li.classList.add('dropdown-item');
                                li.setAttribute('tabindex', index);
                                li.addEventListener('click', () => {
                                    selectCustomer(customer.name);
                                });
                                customerDropdown.appendChild(li);
                            });
                            customerDropdown.style.display = 'block';
                        } else {
                            customerDropdown.innerHTML =
                                '<li class="dropdown-item">No customers found</li>';
                            customerDropdown.style.display = 'block';
                        }
                    })
                    .catch((err) => console.error('Error fetching customers:', err));
            };

            const selectCustomer = (name) => {
                searchCustomerInput.value = name;
                customerDropdown.innerHTML = '';
                customerDropdown.style.display = 'none';
            };

            searchCustomerInput.addEventListener('input', () =>
                fetchCustomers(searchCustomerInput.value.trim())
            );

            searchCustomerInput.addEventListener('keydown', (e) => {
                const items = customerDropdown.querySelectorAll('.dropdown-item');

                if (e.key === 'ArrowDown') {
                    activeIndex = (activeIndex + 1) % items.length;
                    updateActiveItem(items);
                } else if (e.key === 'ArrowUp') {
                    activeIndex = (activeIndex - 1 + items.length) % items.length;
                    updateActiveItem(items);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (activeIndex >= 0) {
                        items[activeIndex].click();
                    }
                }
            });

            const updateActiveItem = (items) => {
                items.forEach((item, index) => {
                    item.classList.toggle('active', index === activeIndex);
                });
                items[activeIndex]?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
            };

            $('#advance-payment-form').on('submit', function(e) {
                e.preventDefault();

                const data = {
                    customer_name: $('#search-customer').val().trim(),
                    payment_type: $('#payment-type').val(),
                    reason: $('#reason').val().trim(),
                    net_amount: $('#net-amount').val().trim(),
                    print_bill: $('#print-bill').is(':checked') ? 1 : 0,
                };

                if (!data.customer_name || !data.net_amount) {
                    Swal.fire(
                        'Validation Error',
                        'Customer Name and Total Amount are required.',
                        'error'
                    );
                    return;
                }

                fetch('advance_payment_fetch.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(data),
                    })
                    .then((response) => response.json())
                    .then((result) => {
    if (result.success) {
        Swal.fire({
            title: 'Advance Payment',
            text: `Bill Number: ${result.bill_number}\n${result.message}`,
            icon: 'success',
            timer: 3000, // Display for 3 seconds (3000 milliseconds)
            timerProgressBar: true, // Show progress bar
        }).then(() => {
            if (data.print_bill) {
                const printWindow = window.open(
                    `advance_bill_new.php?advance_bill_number=${result.bill_number}`,
                    '_blank'
                );
                printWindow.focus();
                printWindow.print();
                location.reload();
            }
            $('#advancePaymentModal').modal('hide');
            $('#advance-payment-form')[0].reset();
        });
    } else {
        Swal.fire('Error', result.message, 'error');
    }
})

                    .catch((err) => {
                        Swal.fire('Error', 'An unexpected error occurred.', 'error');
                    });
            });
        });

        $(document).on('keydown', function(e) {
            if (e.key === 'PageDown') {
                // Check the checkbox programmatically
                $('#print-bill').prop('checked', true);

                // Trigger the form submission
                $('#advance-payment-form').submit();
            }
        })
    </script>
</body>

</html>