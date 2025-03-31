<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Supplier</title>
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
        <!-- <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addSupplierModal">
            Add Supplier
        </button> -->

        <!-- Modal -->
        <div class="modal fade" id="addSupplierModal" tabindex="-1" role="dialog" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addSupplierModalLabel">Add New Supplier</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="supplierForm">
                    <input type="hidden" name="action" value="add">
                        
                        <!-- Step 1 -->
                        <div class="modal-body step active" id="step-1">
                            <div class="form-group">
                                <label for="supplier_name">Supplier Name</label>
                                <input type="text" class="form-control" id="supplier_name" name="supplier_name" required>
                            </div>
                            <div class="form-group">
                                <label for="telephone">Telephone</label>
                                <input type="number" class="form-control" id="telephone" name="telephone" required>
                            </div>
                            <div class="form-group">
                                <label for="company">Company</label>
                                <input type="text" class="form-control" id="company" name="company" required>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="modal-body step" id="step-2">
                            <div class="form-group">
                                <label for="area_manager">Area Manager</label>
                                <input type="text" class="form-control" id="area_manager" name="area_manager">
                            </div>
                            <div class="form-group">
                                <label for="agent_details">Agent Details</label>
                                <textarea class="form-control" id="agent_details" name="agent_details" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="ref_details">Reference Details</label>
                                <textarea class="form-control" id="ref_details" name="ref_details" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" id="prevBtn" style="display: none;">Back</button>
                            <button type="button" class="btn btn-primary" id="nextBtn">Next</button>
                            <button type="submit" class="btn btn-primary" id="submitBtn" style="display: none;">Save Supplier</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
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
            $('#nextBtn').on('click', function () {
                if (currentStep < totalSteps) {
                    currentStep++;
                    showStep(currentStep);
                }
            });

            // Previous button click
            $('#prevBtn').on('click', function () {
                if (currentStep > 1) {
                    currentStep--;
                    showStep(currentStep);
                }
            });

            // Initialize the form with the first step
            showStep(currentStep);
        });

        $(document).ready(function () {
    $('#supplierForm').on('submit', function (event) {
        event.preventDefault(); // Prevent default form submission

        const formData = {
            action: 'add', // Specify the action
            supplier_name: $('#supplier_name').val(),
            telephone: $('#telephone').val(),
            company: $('#company').val(),
            area_manager: $('#area_manager').val(),
            agent_details: $('#agent_details').val(),
            ref_details: $('#ref_details').val()
        };

        $.ajax({
            url: '../../controllers/supplier_controller.php', // Update to your PHP script path
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function (response) {
                console.log(response); // Handle success
                alert(response.message);
                location.reload();
                $('#addSupplierModal').modal('hide');
            },
            error: function (xhr) {
                console.error(xhr.responseText); // Handle error
                alert('An error occurred');
            }
        });
    });
});

    </script>
</body>
</html>
