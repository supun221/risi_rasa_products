<!-- update_salesman.php -->
<div class="modal fade" id="updateSalesmanModal" tabindex="-1" role="dialog" aria-labelledby="updateSalesmanModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateSalesmanModalLabel">Update Salesman</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="updateSalesmanForm" action="../../controllers/salesman_controller.php" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="update-salesman-id">

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
                        <input type="text" class="form-control" id="update-address" name="address" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Salesman</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // function loadSalesmanDetails(id) {
    //     // Fetch salesman details using AJAX
    //     $.ajax({
    //         url: '../../controllers/salesman_controller.php',
    //         method: 'POST',
    //         contentType: 'application/json',
    //         data: JSON.stringify({ action: 'get', id: id }),
    //         success: function(response) {
    //             if (response.status === 'success') {
    //                 const salesman = response.data;
    //                 // Populate the modal fields
    //                 $('#update-salesman-id').val(salesman.id);
    //                 $('#update-name').val(salesman.name);
    //                 $('#update-telephone').val(salesman.telephone);
    //                 $('#update-nic').val(salesman.nic);
    //                 $('#update-address').val(salesman.address);
    //                 // Show the modal
    //                 $('#updateSalesmanModal').modal('show');
    //             } else {
    //                 alert(response.message);
    //             }
    //         },
    //         error: function() {
    //             alert('Error fetching salesman details.');
    //         }
    //     });
    // }
    $(document).ready(function () {
    $('#updateSalesmanForm').on('submit', function (event) {
        event.preventDefault();

        const formData = {
            action: 'update',
            id: $('#update-salesman-id').val(),
            name: $('#update-name').val(),
            telephone: $('#update-telephone').val(),
            nic: $('#update-nic').val(),
            address: $('#update-address').val(),
        };

        $.ajax({
            url: '../../controllers/salesman_controller.php',
            type: 'POST',
            dataType: 'json', // Expect a JSON response
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function (response) {
                if (response.status === 'success') {
                    alert(response.message);
                    $('#updateSalesmanModal').modal('hide');
                    location.reload(); // Refreshes the page
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error('Error:', error);
                console.error('Response:', xhr.responseText);
                alert('An error occurred while updating the salesman.');
            },
        });
    });
});

</script>
