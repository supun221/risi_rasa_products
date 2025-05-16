<?php
session_start(); // Start the session

// Fetch the logged-in user's name and branch from the session
$user_name = $_SESSION['username'] ?? '';
$user_branch = $_SESSION['store'] ?? '';

// Fetch cashbook entries for the logged-in user
$cashbookEntries = [];
include '../../../config/databade.php'; // Include database connection
require_once '../header1.php';
require_once 'add_cash_book.php';
if ($conn) {
    $query = "SELECT id, user_name, reason, amount, branch, created_timestamp 
              FROM cash_book 
              WHERE user_name = ? AND branch = ? 
              ORDER BY created_timestamp DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $user_name, $user_branch);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $cashbookEntries[] = $row;
        }
    } else {
        echo "<p>Error fetching cashbook entries: " . $conn->error . "</p>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<p>Error connecting to the database.</p>";
}
?>

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
    <link rel="stylesheet" href="../../assets/css/user_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>

<style>
    .button-container {
        text-align: left;
        margin-bottom: 10px;
    }
    .delete-btn {
        color: #dc3545;
        cursor: pointer;
    }
    .delete-btn:hover {
        color: #c82333;
    }
</style>

<body>

    <div class="container mt-5">
        <!-- Button to trigger modal -->

        <!-- Cashbook Entries Table -->
        <div class="mt-4">
            <h2>Cashbook Entries</h2>
            <div class="button-container">
                <button type="button" class="add-customer" data-toggle="modal" data-target="#cashBookModal">
                    Add Cash Entry
                </button>
            </div>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User Name</th>
                        <th>Reason</th>
                        <th>Amount</th>
                        <th>Branch</th>
                        <th>Created Timestamp</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($cashbookEntries)) : ?>
                        <?php foreach ($cashbookEntries as $entry) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($entry['id']); ?></td>
                                <td><?php echo htmlspecialchars($entry['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($entry['reason']); ?></td>
                                <td><?php echo number_format($entry['amount'], 2); ?></td>
                                <td><?php echo htmlspecialchars($entry['branch']); ?></td>
                                <td><?php echo htmlspecialchars($entry['created_timestamp']); ?></td>
                                <td class="text-center">
                                    <i class="fas fa-trash delete-btn" data-id="<?php echo $entry['id']; ?>"></i>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7" class="text-center">No cashbook entries found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this cashbook entry?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
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

            // Handle delete button click
            let entryIdToDelete;
            
            $('.delete-btn').on('click', function() {
                entryIdToDelete = $(this).data('id');
                $('#deleteConfirmModal').modal('show');
            });
            
            $('#confirmDelete').on('click', function() {
                $.ajax({
                    url: '../../controllers/delete_cashbook_entry.php',
                    type: 'POST',
                    data: { id: entryIdToDelete },
                    dataType: 'json',
                    success: function(response) {
                        $('#deleteConfirmModal').modal('hide');
                        
                        if (response.status === 'success') {
                            Swal.fire({
                                title: "Success!",
                                text: "Cashbook entry deleted successfully.",
                                icon: "success",
                                confirmButtonText: "OK"
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: "Error!",
                                text: response.message || "Error deleting entry.",
                                icon: "error",
                                confirmButtonText: "OK"
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#deleteConfirmModal').modal('hide');
                        Swal.fire({
                            title: "Error!",
                            text: "An error occurred: " + (xhr.responseText || error),
                            icon: "error",
                            confirmButtonText: "OK"
                        });
                    }
                });
            });

            document.addEventListener("keydown", function(event) {
                if (event.code === "Home") {
                    window.location.href = "../dashboard/index.php";
                }
            });
        });
    </script>
</body>

</html>