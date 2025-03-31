<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Salesman</title>
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
        <!-- <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addSalesmanModal">+ +
        </button> -->

        <!-- Modal -->
        <div class="modal fade" id="addSalesmanModal" tabindex="-1" role="dialog" aria-labelledby="addSalesmanModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addSalesmanModalLabel">Add New Salesman</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form id="salesmanForm">
                        <input type="hidden" name="action" value="add">

                        <!-- Step 1 -->
                        <div class="modal-body step active" id="step-1">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="telephone">Telephone</label>
                                <input type="number" class="form-control" id="telephone" name="telephone" required>
                            </div>
                            <div class="form-group">
                                <label for="nic">Nic</label>
                                <input type="text" class="form-control" id="nic" name="nic" required>
                            </div>
                            <div class="form-group">
                                <label for="address">Address</label>
                                <input type="text" class="form-control" id="address" name="address" required>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save Salesman</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Reset modal inputs when closed
            $('.modal').on('hidden.bs.modal', function () {
                $(this).find('input, textarea').val('');
            });

            // Handle form submission
            $('#salesmanForm').on('submit', function (event) {
                event.preventDefault(); // Prevent default form submission

                const formData = {
                    action: 'add',
                    name: $('#name').val(),
                    telephone: $('#telephone').val(),
                    nic: $('#nic').val(),
                    address: $('#address').val(),
                };

                $.ajax({
                    url: '../../controllers/salesman_controller.php',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(formData),
                    success: function (response) {
                        console.log(response);
                        alert("Salesman added successfully!"); // Display success message

                        // Close modal and reload page
                        $('#addSalesmanModal').modal('hide');
                        location.reload();
                    },
                    error: function () {
                        alert('An error occurred while processing your request.');
                    }
                });
            });
        });
    </script>
</body>

</html>
