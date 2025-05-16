
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash Book Entry</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<body>
    <div class="">
        <!-- Button to trigger modal -->
        <!-- <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#cashBookModal">
            Add Cash Entry
        </button> -->

        <!-- Modal -->
        <div class="modal fade" id="cashBookModal" tabindex="-1" role="dialog" aria-labelledby="cashBookModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cashBookModalLabel">Add Cash Entry</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="cashBookForm" action="" method="POST">
                        <input type="hidden" name="action" value="add">

                        <div class="modal-body">
                            <div class="form-group">
                                <label for="user_name">User Name</label>
                                <input type="text" class="form-control" id="user_name" name="user_name" value="<?php echo htmlspecialchars($user_name); ?>" required readonly>
                            </div>
                            <div class="form-group">
                                <label for="reason">Reason</label>
                                <textarea class="form-control" id="reason" name="reason" rows="3" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="amount">Amount</label>
                                <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                            </div>
                            <div class="form-group">
                                <label for="branch">Branch</label>
                                <input type="text" class="form-control" id="branch" name="branch" value="<?php echo htmlspecialchars($user_branch); ?>" required readonly>
                            </div>
                            <div class="form-group">
                                <label for="created_timestamp">Created Timestamp</label>
                                <input type="text" class="form-control" id="created_timestamp" name="created_timestamp" readonly>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save Entry</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Set current timestamp in the created_timestamp field
            function setTimestamp() {
                let now = new Date();
                let formattedTimestamp = now.getFullYear() + "-" +
                    ("0" + (now.getMonth() + 1)).slice(-2) + "-" +
                    ("0" + now.getDate()).slice(-2) + " " +
                    ("0" + now.getHours()).slice(-2) + ":" +
                    ("0" + now.getMinutes()).slice(-2) + ":" +
                    ("0" + now.getSeconds()).slice(-2);
                $("#created_timestamp").val(formattedTimestamp);
            }

            // Open modal and set timestamp
            $('#cashBookModal').on('show.bs.modal', function() {
                setTimestamp();
            });

            // Reset form when modal closes
            $('#cashBookModal').on('hidden.bs.modal', function() {
                $('#cashBookForm')[0].reset();
                setTimestamp();
            });

            // Handle form submission
            $('#cashBookForm').on('submit', function(event) {
                event.preventDefault();
                const formData = $(this).serialize();

                $.ajax({
                    url: '../../controllers/cash_book_controller.php',
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        console.log("Server Response:", response);

                        if (response.status === 'success') {
                            Swal.fire({
                                title: "Success!",
                                text: "Cash entry added successfully.",
                                icon: "success",
                                confirmButtonText: "OK"
                            }).then(() => {
                                $('#cashBookModal').modal('hide');
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: "Error!",
                                text: response.message || "Error adding entry.",
                                icon: "error",
                                confirmButtonText: "OK"
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("AJAX Error:", xhr.responseText);
                        Swal.fire({
                            title: "Error!",
                            text: "An error occurred: " + (xhr.responseText || error),
                            icon: "error",
                            confirmButtonText: "OK"
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>