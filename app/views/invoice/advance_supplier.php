<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advance Supplier Payment</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        body {
            background-color: #f4f4f4;


        }

        .form-check {
            display: flex;
            align-items: left;
            gap: 10px;
            /* Optional: Add spacing between the checkbox and the label */
        }

        .form-check-input {
            margin: 0;
        }


        .form-check-label {
            margin-left: -16px;
            /* Remove default margin from the label */
            font-weight: bold;
            /* Match label style with other form labels */
        }


        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            max-width: 400px;
            /* Set a consistent width, similar to .total-amount */
            /* margin: 0 auto; */
            display: block;
        }

        .form-group textarea {
            resize: none;
            /* Optional: prevent textarea from being resized */
        }
        .dropdown-list {
            position: absolute;
            width: calc(86% - 30px);
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
    </style>
</head>

<body>
    <!-- Add Button to Open Modal -->
    <!-- <div class="text-center mt-5">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#advancePaymentModal">
            Add Advance Payment
        </button>
    </div> -->

    <!-- Bootstrap Modal -->
    <div class="modal fade" id="advancePaymentModal" tabindex="-1" aria-labelledby="advancePaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="advancePaymentModalLabel">Advance Supplier Payment</h5>
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
                        <div class="form-group">
                            <label for="supplier-name">Supplier Name:</label>
                            <input type="text" class="form-control" id="search-supplier" name="supplier_name" placeholder="Search supplier...">
                            <ul id="supplier-list" class="dropdown-list"></ul>
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

            const searchsupplierInput = document.getElementById('search-supplier');
            const supplierDropdown = document.getElementById('supplier-list');
            let activeIndex = -1;

            const fetchSuppliers = (query) => {
                if (query.length < 2) {
                    supplierDropdown.innerHTML = '';
                    supplierDropdown.style.display = 'none';
                    activeIndex = -1;
                    return;
                }

                fetch('../../controllers/supplier_controller.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'search',
                            query,
                        }),
                    })
                    .then((response) => response.json())
                    .then((data) => {
                        supplierDropdown.innerHTML = '';
                        activeIndex = -1;

                        if (data.status === 'success' && data.data.length > 0) {
                            data.data.forEach((supplier, index) => {
                                const li = document.createElement('li');
                                li.textContent = supplier.supplier_name;
                                li.dataset.id = supplier.id;
                                li.classList.add('dropdown-item');
                                li.setAttribute('tabindex', index);
                                li.addEventListener('click', () => {
                                    selectsupplier(supplier.supplier_name);
                                });
                                supplierDropdown.appendChild(li);
                            });
                            supplierDropdown.style.display = 'block';
                        } else {
                            supplierDropdown.innerHTML = '<li class="dropdown-item">No suppliers found</li>';
                            supplierDropdown.style.display = 'block';
                        }
                    })
                    .catch((err) => console.error('Error fetching suppliers:', err));
            };

            const selectsupplier = (name) => {
                searchsupplierInput.value = name;
                supplierDropdown.innerHTML = '';
                supplierDropdown.style.display = 'none';
            };

            const updateActiveItem = (items) => {
                items.forEach((item, index) => {
                    item.classList.toggle('active', index === activeIndex);
                });
                items[activeIndex]?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
            };

            searchsupplierInput.addEventListener('input', () => fetchSuppliers(searchsupplierInput.value.trim()));

            searchsupplierInput.addEventListener('keydown', (e) => {
                const items = supplierDropdown.querySelectorAll('.dropdown-item');

                if (e.key === 'ArrowDown') {
                    // Navigate to the next item
                    e.preventDefault();
                    activeIndex = (activeIndex + 1) % items.length;
                    updateActiveItem(items);
                } else if (e.key === 'ArrowUp') {
                    // Navigate to the previous item
                    e.preventDefault();
                    activeIndex = (activeIndex - 1 + items.length) % items.length;
                    updateActiveItem(items);
                } else if (e.key === 'Enter') {
                    // Select the active item
                    e.preventDefault();
                    if (activeIndex >= 0) {
                        items[activeIndex].click();
                    }
                } else if (e.key === 'Escape') {
                    // Close the dropdown on Escape
                    supplierDropdown.innerHTML = '';
                    supplierDropdown.style.display = 'none';
                    activeIndex = -1;
                }
            });

            $('#advance-payment-form').on('submit', function(e) {
                e.preventDefault();

                const data = {
                    supplier_name: $('#search-supplier').val().trim(),
                    payment_type: $('#payment-type').val(),
                    reason: $('#reason').val().trim(),
                    net_amount: $('#net-amount').val().trim(),
                    print_bill: $('#print-bill').is(':checked') ? 1 : 0,
                };

                fetch('advance_supplier_fetch.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(data),
                    })
                    .then((response) => response.json())
                    .then((result) => {
                        if (result.success) {
                            Swal.fire(
                                'Advance Payment',
                                `Bill Number: ${result.bill_number}\n${result.message}`,
                                'success'
                            ).then(() => {
                                if (data.print_bill) {
                                    const printWindow = window.open(
                                        `advance_supplier_bill.php?advance_bill_number=${result.bill_number}`,
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

            $(document).on('keydown', function(e) {
                if (e.key === 'PageDown') {
                    // Check the checkbox programmatically
                    $('#print-bill').prop('checked', true);

                    // Trigger the form submission
                    $('#advance-payment-form').submit();
                }
            });
        });
    </script>

</body>

</html>