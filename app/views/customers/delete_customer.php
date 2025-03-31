<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Customer</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- For a better popup -->
</head>
<body>
    <!-- Delete Button -->
    <button onclick="confirmDelete(1)">Delete Customer</button> <!-- Replace '1' with the customer ID dynamically -->

    <script>
        function confirmDelete(customerId) {
            // Show confirmation dialog
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send delete request via AJAX
                    fetch('delete_customer.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `id=${customerId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            Swal.fire(
                                'Deleted!',
                                'Customer has been deleted.',
                                'success'
                            ).then(() => {
                                // Redirect or refresh the page
                                window.location.href = 'customer_list.php';
                            });
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Something went wrong!', 'error');
                        console.error(error);
                    });
                }
            });
        }
    </script>
</body>
</html>
