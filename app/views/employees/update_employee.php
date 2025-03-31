<head>
    <!-- Include SweetAlert CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Include SweetAlert JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<div id="updateEmployeeModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="updateEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateEmployeeModalLabel">Update Employee</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="updateEmployeeForm" action="../../controllers/employee_controller.php" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" id="update-employee-id" name="emp_id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="update-name">Name:</label>
                        <input type="text" id="update-name" name="name" placeholder="Enter employee name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="update-telephone">Telephone:</label>
                        <input type="text" id="update-telephone" name="telephone" placeholder="Enter telephone number" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="update-nic">NIC:</label>
                        <input type="text" id="update-nic" name="nic" placeholder="Enter NIC" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="update-address">Address:</label>
                        <input type="text" id="update-address" name="address" placeholder="Enter address" class="form-control" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#updateEmployeeForm').on('submit', function(event) {
            event.preventDefault();

            const formData = {
                action: 'update',
                emp_id: $('#update-employee-id').val(),
                name: $('#update-name').val(),
                telephone: $('#update-telephone').val(),
                nic: $('#update-nic').val(),
                address: $('#update-address').val()
            };

            $.ajax({
                url: '../../controllers/employee_controller.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(formData),
                success: function(response) {
                    // Use SweetAlert for success message
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Reload the page after the user clicks "OK"
                            location.reload();
                        }
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    console.error('Response:', xhr.responseText);
                    // Use SweetAlert for error message
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while updating the employee.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });

            // $('#updateEmployeeForm')[0].reset();
        });
    });

    function closeUpdateModal() {
        $('#updateEmployeeModal').modal('hide');
    }
</script>