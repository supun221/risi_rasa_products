<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Customer</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<body>
    <!-- Modal Structure -->
    <div class="modal fade" id="updateCustomerModal" tabindex="-1" role="dialog" aria-labelledby="updateCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateCustomerModalLabel">Update Customer</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="updateCustomerForm" action="../../controllers/customer_controller.php" method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="update-customer-id">

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="update-name">Name</label>
                            <input type="text" class="form-control" id="update-name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="update-telephone">Telephone</label>
                            <input type="number" class="form-control" id="update-telephone" name="telephone" required>
                        </div>
                        <div class="form-group">
                            <label for="update-nic">NIC</label>
                            <input type="text" class="form-control" id="update-nic" name="nic" required>
                        </div>
                        <div class="form-group">
                            <label for="update-address">Address</label>
                            <textarea class="form-control" id="update-address" name="address" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="update-whatsapp">WhatsApp</label>
                            <input type="number" class="form-control" id="update-whatsapp" name="whatsapp">
                        </div>
                        <div class="form-group">
                            <label for="update-email">Email</label>
                            <input type="email" class="form-control" id="update-email" name="email">
                        </div>
                        <div class="form-group">
                            <label for="update-birthday">Birthday</label>
                            <input type="date" class="form-control" id="update-birthday" name="birthday">
                        </div>
                        <div class="form-group">
                            <label for="update-credit-limit">Credit Limit</label>
                            <input type="text" class="form-control" id="update-credit-limit" name="credit_limit">
                        </div>
                        <div class="form-group">
                            <label for="update-discount">Discount</label>
                            <input type="text" class="form-control" id="update-discount" name="discount">
                        </div>
                        <div class="form-group">
                            <label for="update-price-type">Price Type</label>
                            <input type="text" class="form-control" id="update-price-type" name="price_type">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Function to fetch customer details and populate the form
        function loadCustomerDetails(customerId) {
            // Clear the modal fields
            clearCustomerForm();

            // Fetch customer details using AJAX
            $.ajax({
                url: '../../controllers/customer_controller.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    action: 'get',
                    id: customerId
                }),
                success: function (response) {
                    if (response.status === 'success') {
                        const customer = response.data;

                        // Populate the modal fields
                        $('#update-customer-id').val(customer.id);
                        $('#update-name').val(customer.name);
                        $('#update-telephone').val(customer.telephone);
                        $('#update-nic').val(customer.nic);
                        $('#update-address').val(customer.address);
                        $('#update-whatsapp').val(customer.whatsapp);
                        $('#update-email').val(customer.email);
                        $('#update-birthday').val(customer.birthday);
                        $('#update-credit-limit').val(customer.credit_limit);
                        $('#update-discount').val(customer.discount);
                        $('#update-price-type').val(customer.price_type);

                        // Show the modal
                        $('#updateCustomerModal').modal('show');
                    } else {
                        alert(response.message);
                    }
                },
                error: function () {
                    alert('Error fetching customer details.');
                }
            });
        }

        // Function to clear the modal form
        function clearCustomerForm() {
            $('#updateCustomerForm')[0].reset();
            $('#update-customer-id').val('');
        }

        // Bind the form submission event
        $('#updateCustomerForm').on('submit', function (event) {
            event.preventDefault();

            // Perform AJAX to update customer
            $.ajax({
                url: '../../controllers/customer_controller.php',
                method: 'POST',
                data: $(this).serialize(),
                success: function (response) {
                    if (response.status === 'success') {
                        alert('Customer updated successfully.');
                        $('#updateCustomerModal').modal('hide');
                        location.reload(); // Refresh the page to display updated customer details
                    } else {
                        alert(response.message);
                    }
                },
                error: function () {
                    alert('Error updating customer.');
                }
            });
        });
    </script>
</body>

</html>
