<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Damage</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<body>

    <!-- Update Damage Modal -->
    <div class="modal fade" id="updateDamageModal" tabindex="-1" role="dialog" aria-labelledby="updateDamageModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateDamageModalLabel">Update Damage</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="updateDamageForm" class="p-3">
                    <input type="hidden" id="update-damage-id" name="id">

                    <div class="form-group">
                        <label for="update-product-name">Product Name</label>
                        <input type="text" id="update-product-name" name="product_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="update-damage-description">Damage Description</label>
                        <textarea id="update-damage-description" name="damage_description" class="form-control" rows="3" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="update-damage-quantity">Quantity</label>
                        <input type="number" id="update-damage-quantity" name="damage_quantity" class="form-control" required min="1">
                    </div>

                    <div class="form-group">
                        <label for="update-damage-price">Price</label>
                        <input type="number" id="update-damage-price" name="damage_price" class="form-control" required step="0.01">
                    </div>

                    <div class="form-group">
                        <label for="update-barcode">Barcode</label>
                        <input type="text" id="update-barcode" name="barcode" class="form-control" required>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <button type="button" class="btn btn-secondary" onclick="closeUpdateDamageModal()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function closeUpdateDamageModal() {
            $('#updateDamageModal').modal('hide');
        }

        $('#updateDamageForm').on('submit', function (e) {
            e.preventDefault();

            const formData = {
                action: 'update',
                id: $('#update-damage-id').val(),
                product_name: $('#update-product-name').val(),
                damage_description: $('#update-damage-description').val(),
                damage_quantity: parseInt($('#update-damage-quantity').val(), 10),
                damage_price: parseFloat($('#update-damage-price').val()),
                barcode: $('#update-barcode').val()
            };

            $.ajax({
                url: '../../controllers/damage_controller.php',
                type: 'post',
                contentType: 'application/json',
                data: JSON.stringify(formData),
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            title: 'Updated!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Reload the page after the user clicks "OK"
                                location.reload();
                            }
                        });
                        $('#updateDamageForm').trigger('reset');
                        $('#updateDamageModal').modal('hide');
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function () {
                    Swal.fire('Error!', 'There was a problem updating the damage.', 'error');
                }
            });
        });
    </script>

</body>

</html>
