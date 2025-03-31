<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Customer</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        .step {
            display: none;
        }

        .step.active {
            display: block;
        }
    </style>
</head>

<body>
    <div class="">
        <!-- Button to trigger modal -->
        <!-- <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addCustomerModal">
            Add Customer
        </button> -->

        <!-- Modal -->
        <div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCustomerModalLabel">Add New Customer</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="customerForm" action="../../controllers/customer_controller.php" method="POST">
                        <input type="hidden" name="action" value="add">

                        <!-- Step 1 -->
                        <div class="modal-body step active" id="step-1">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="telephone">Telephone</label>
                                <input type="number" class="form-control" id="telephone" name="telephone" maxlength="10" pattern="\d{10}" required>

                            </div>
                            <div class="form-group">
                                <label for="nic">NIC</label>
                                <input type="text" class="form-control" id="nic" name="nic">
                            </div>
                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="whatsapp">WhatsApp</label>
                                <input type="number" class="form-control" id="whatsapp" name="whatsapp">
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="modal-body step" id="step-2">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                            <div class="form-group">
                                <label for="birthday">Birthday</label>
                                <input type="date" class="form-control" id="birthday" name="birthday">
                            </div>
                            <div class="form-group">
                                <label for="credit_limit">Credit Limit</label>
                                <input type="text" class="form-control" id="credit_limit" name="credit_limit">
                            </div>
                            <div class="form-group">
                                <label for="discount">Discount</label>
                                <input type="text" class="form-control" id="discount" name="discount">
                            </div>
                            <div class="form-group">
                                <label for="price_type">Price Type</label>
                                <input type="text" class="form-control" id="price_type" name="price_type">
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" id="prevBtn" style="display: none;">Back</button>
                            <button type="button" class="btn btn-primary" id="nextBtn">Next</button>
                            <button type="submit" class="btn btn-primary" id="submitBtn" style="display: none;">Save Customer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            let currentStep = 1; // Start on Step 1
            const totalSteps = 2;

            // Show the current step
            function showStep(step) {
                $('.step').removeClass('active');
                $(`#step-${step}`).addClass('active');

                // Show/Hide navigation buttons
                if (step === 1) {
                    $('#prevBtn').hide();
                    $('#nextBtn').show();
                    $('#submitBtn').hide();
                } else if (step === totalSteps) {
                    $('#prevBtn').show();
                    $('#nextBtn').hide();
                    $('#submitBtn').show();
                } else {
                    $('#prevBtn').show();
                    $('#nextBtn').show();
                    $('#submitBtn').hide();
                }
            }

            // Next button click
            $('#nextBtn').on('click', function() {
                if (currentStep < totalSteps) {
                    currentStep++;
                    showStep(currentStep);
                }
            });

            // Previous button click
            $('#prevBtn').on('click', function() {
                if (currentStep > 1) {
                    currentStep--;
                    showStep(currentStep);
                }
            });

            // Initialize the form with the first step
            showStep(currentStep);

            // Reset the form and modal on close
            $('#addCustomerModal').on('hidden.bs.modal', function() {
                $('#customerForm')[0].reset();
                currentStep = 1;
                showStep(currentStep);
            });

            // Handle form submission
            $('#customerForm').on('submit', function(event) {
                event.preventDefault(); // Prevent default form submission

                const formData = $(this).serialize();
                $.ajax({
                    url: '../../controllers/customer_controller.php',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.status === 'success') {
                            alert('Customer added successfully.');
                            $('#addCustomerModal').modal('hide');
                            location.reload(); // Reload the page after successful addition
                        } else {
                            alert(response.message || 'Error adding customer.');
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('An error occurred: ' + (xhr.responseText || error));
                    }
                });
            });
        });
    </script>
</body>

</html>