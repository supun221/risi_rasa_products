<!DOCTYPE html>
<html>

<head>
    <title>Add Employee</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- Add SweetAlert -->
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
        <div class="modal fade" id="addEmployeeModal" tabindex="-1" role="dialog" aria-labelledby="addEmployeeModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addEmployeeModalLabel">Add New Employee</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="employeeForm">
                            <input type="hidden" name="action" value="add">
                            <div class="form-group">
                                <label for="employeeName">Employee Name:</label>
                                <input type="text" class="form-control" id="employeeName" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="employeeNIC">Employee NIC:</label>
                                <input type="text" class="form-control" id="employeeNIC" name="nic" required>
                            </div>
                            <div class="form-group">
                                <label for="employeeTelephone">Employee Telephone:</label>
                                <input type="number" class="form-control" id="employeeTelephone" name="telephone" required>
                            </div>
                            <div class="form-group">
                                <label for="employeeAddress">Employee Address:</label>
                                <input type="text" class="form-control" id="employeeAddress" name="address" required>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Add Employee</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#employeeForm').on('submit', function(event) {
                event.preventDefault();
                var formData = {
                    action: 'add',
                    name: $('#employeeName').val(),
                    nic: $('#employeeNIC').val(),
                    telephone: $('#employeeTelephone').val(),
                    address: $('#employeeAddress').val()
                };

                $.ajax({
                    url: '../../controllers/employee_controller.php',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(formData),
                    success: function(response) {
                        if (response.success) {
                            // Show SweetAlert success message
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // Hide the modal
                                    $('#addEmployeeModal').modal('hide');
                                    // Reload the page
                                    location.reload();
                                }
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        // Show SweetAlert error message
                        Swal.fire({
                            title: 'Error!',
                            text: 'Error adding employee.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>