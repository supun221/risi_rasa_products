<?php
require_once '../../../config/databade.php';
// Detect the selected option from the query parameter
$selectedOption = isset($_GET['type']) ? htmlspecialchars($_GET['type']) : 'supplier';
require_once '../header1.php';
require_once 'advance_supplier.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$itemsPerPage = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * $itemsPerPage;

// Fetch the total number of records with search applied
$totalQuery = "SELECT COUNT(*) AS total FROM advance_payments_supplier WHERE supplier_name LIKE ? OR advance_bill_number LIKE ?";
$totalStmt = $conn->prepare($totalQuery);
$searchParam = "%$searchQuery%";
$totalStmt->bind_param("ss", $searchParam, $searchParam);
$totalStmt->execute();
$totalResult = $totalStmt->get_result();
$total = $totalResult->fetch_assoc()['total'];
$totalStmt->close();

// Fetch the advance payment records for the current page with search applied
$query = "SELECT * FROM advance_payments_supplier WHERE supplier_name LIKE ? OR advance_bill_number LIKE ? ORDER BY id DESC LIMIT ?, ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssii", $searchParam, $searchParam, $offset, $itemsPerPage);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advance Supplier Payment List</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            justify-content: space-between;
            align-items: center;
        }

        .add {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px 15px;
            font-size: 16px;
            border-radius: 5px;
            margin-left: auto;
            display: inline-block;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .add:hover {
            background-color: #0056b3;
        }

        .select-custom {
            width: 200px;
            padding: 5px;
            border-radius: 5px;
            border: 1px solid #ced4da;
            background: #fff;
            font-size: 16px;
            color: #495057;
            appearance: none;
        }

        #pageSelector {
            padding: 5px;
            border-radius: 5px;
            border: 1px solid #ced4da;
            background: #fff;
            font-size: 16px;
            color: #495057;
            appearance: none;
            margin-left: 10px;
            cursor: pointer;
        }

        .form-inline {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .form-control {
            width: 300px;
        }

        .pagination .page-link {
            color: #007bff;
        }

        .pagination .page-link:hover {
            background-color: #f0f0f0;
            text-decoration: none;
        }

        .pagination .active .page-link {
            background-color: #007bff;
            border-color: #007bff;
            color: #fff;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center">Advance Supplier Payment List</h1>

        <!-- Search Bar -->
        <form method="GET" class="form-inline mb-4">
            <input
                type="text"
                name="search"
                value="<?= htmlspecialchars($searchQuery) ?>"
                class="form-control mr-2"
                placeholder="Search by Supplier Name or Bill Number">
            <button type="submit" class="btn btn-primary">Search</button>
            <a href="advance_supplier_list.php" class="btn btn-secondary ml-2">Reset</a>
            <select id="pageSelector" class="select-custom ml-2">
                <option value="advance_list.php?type=customer" <?= $selectedOption === 'customer' ? 'selected' : '' ?>>Customer</option>
                <option value="advance_supplier_list.php?type=supplier" <?= $selectedOption === 'supplier' ? 'selected' : '' ?>>Suppliers</option>
            </select>
            <button type="button"   class="add" data-toggle="modal" data-target="#advancePaymentModal">Add Advance</button>
        </form>
        
        <script>
            // JavaScript to handle selection and redirection
            document.getElementById('pageSelector').addEventListener('change', function() {
                const selectedValue = this.value; // Get the selected option value
                if (selectedValue) {
                    window.location.href = selectedValue; // Redirect to the selected page
                }
            });
        </script>

        <!-- Table -->
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Advance Bill Number</th>
                    <th>Supplier Name</th>
                    <th>Payment Type</th>
                    <th>Reason</th>
                    <th>Net Amount</th>
                    <th>Print Bill</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['advance_bill_number']) ?></td>
                            <td><?= htmlspecialchars($row['supplier_name']) ?></td>
                            <td><?= htmlspecialchars($row['payment_type']) ?></td>
                            <td><?= htmlspecialchars($row['reason']) ?></td>
                            <td>LKR <?= number_format($row['net_amount'], 2) ?></td>
                            <td><?= $row['print_bill'] ? 'Yes' : 'No' ?></td>
                            <td>
                                <a href="advance_supplier_bill.php?advance_bill_number=<?= urlencode($row['advance_bill_number']) ?>" target="_blank" class="btn btn-primary btn-sm">View</a>
                                <button class="btn btn-danger btn-sm delete-btn" data-id="<?= $row['id'] ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">No records found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php
        $totalPages = ceil($total / $itemsPerPage);
        if ($totalPages > 1):
        ?>
            <nav>
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($searchQuery) ?>&type=<?= urlencode($selectedOption) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('.delete-btn').on('click', function() {
                const id = $(this).data('id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This action cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'advance_supplier_fetch.php',
                            type: 'DELETE',
                            data: JSON.stringify({
                                id
                            }),
                            contentType: 'application/json',
                            success: function(response) {
                                const result = JSON.parse(response);
                                if (result.success) {
                                    Swal.fire(
                                        'Deleted!',
                                        result.message,
                                        'success'
                                    ).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Error!', result.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error!', 'An error occurred while deleting the record.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>