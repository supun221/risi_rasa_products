<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add damage</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</head>


<body>

    <!-- Add Damage Modal -->
    <div class="modal fade" id="addDamageModal" tabindex="-1" role="dialog" aria-labelledby="addDamageModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addDamageModalLabel">Add Damage</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="damageForm" class="p-3">
                    <input type="hidden" name="action" value="add">

                    <div class="form-group">
                        <label for="barcode">Barcode</label>
                        <input type="text" id="barcode" name="barcode" class="form-control" required>
                    </div>


                    <div class="form-group">
                        <label for="product_name">Product Name</label>
                        <input type="text" id="product_name" name="product_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="damage_description">Damage Description</label>
                        <textarea id="damage_description" name="damage_description" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="damage_quantity">Quantity</label>
                        <input type="number" id="damage_quantity" name="damage_quantity" class="form-control" required min="1">
                    </div>

                    <div class="form-group">
                        <label for="damage_price">Price</label>
                        <input type="number" id="damage_price" name="damage_price" class="form-control" required step="0.01">
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="submit" name="add_damage" class="btn btn-primary">Add</button>
                        <button type="button" class="btn btn-secondary" onclick="closeAddDamageModal()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Function to fetch product details by barcode
        $('#barcode').on('blur', function() {
            const barcode = $(this).val().trim();
            if (barcode === '') return;

            $.ajax({
                url: '../../controllers/damage_controller.php',
                type: 'post',
                contentType: 'application/json',
                data: JSON.stringify({
                    action: 'get_by_barcode',
                    query: barcode
                }),
                success: function(response) {
                    if (response.status === 'success') {
                        const {
                            product_name,
                            cost_price,
                            damage_quantity
                        } = response.data;
                        $('#product_name').val(product_name);
                        $('#damage_price').val(cost_price);
                        $('#damage_quantity').attr('max', damage_quantity); // Set max quantity
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to fetch product details.', 'error');
                }
            });
        });

        function closeAddDamageModal() {
            $('#addDamageModal').modal('hide');
        }
       $('#damageForm').on('submit', function (e) {
    e.preventDefault();

    const formData = {
        action: 'add',
        barcode: $('#barcode').val(),
        product_name: $('#product_name').val(),
        damage_description: $('#damage_description').val(),
        damage_quantity: $('#damage_quantity').val(),
        damage_price: $('#damage_price').val(),
    };

    console.log(formData);

    $.ajax({
        url: '../../controllers/damage_controller.php',
        type: 'post',
        contentType: 'application/json',
        data: JSON.stringify(formData),

        success: function (response) {
            if (response.status === 'success') {
                // Show success message
                Swal.fire({
                    title: "Added!",
                    text: response.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Reload the page after the user clicks "OK"
                        location.reload();
                    }
                });

                // Reset the form and hide the modal
                $('#damageForm').trigger('reset');
                $('#addDamageModal').modal('hide');
            } else if (response.status === 'error') {
                // Show error message
                Swal.fire({
                    title: "Error!",
                    text: response.message,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        },
        error: function () {
            // Show a generic error message if the AJAX request fails
            Swal.fire({
                title: "Error!",
                text: "There was a problem processing your request.",
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });
});

    </script>

</body>