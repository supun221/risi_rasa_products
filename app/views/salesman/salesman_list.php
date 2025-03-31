<!DOCTYPE html>
<html>

<head>
    <?php
    require_once '../header1.php';
    ?>
    <title>Salesman List</title>
    <link rel="stylesheet" href="../../assets/css/user_styles.css">
    <style>
        /* Popup and Table Styles */
        .popup-container {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .popup {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .popup h2 {
            margin-top: 0;
        }

        .popup form {
            display: flex;
            flex-direction: column;
        }

        .popup form input {
            margin-bottom: 10px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .popup form button {
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .popup form button:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }

        .action-buttons a {
            margin-right: 5px;
            text-decoration: none;
            color: #007bff;
        }

        .action-buttons a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <h1>Salesman List</h1>
    <button type="button" class="add-customer" data-toggle="modal" data-target="#addSalesmanModal">Add Salesman</button>
    <?php
    require_once 'add_salesman.php'; // Include add salesman modal

    // Database connection
    require_once '../../../config/databade.php';

    // Fetch salesmen from the database
    $salesmen = [];
    $query = "SELECT id, name, telephone, nic, address FROM salesman";
    $result = mysqli_query($conn, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $salesmen[] = $row;
        }
    } else {
        echo "<p>Error fetching salesmen: " . mysqli_error($conn) . "</p>";
    }

    include 'update_salesman.php'; // Include update salesman modal
    ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Salesman Name</th>
                    <th>Telephone</th>
                    <th>NIC</th>
                    <th>Address</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($salesmen)) {
                    foreach ($salesmen as $index => $salesman) {
                        echo '<tr id="salesman-row-' . htmlspecialchars($salesman['id']) . '">';
                        echo '<td>' . ($index + 1) . '</td>';
                        echo '<td>' . htmlspecialchars($salesman['name']) . '</td>';
                        echo '<td>' . htmlspecialchars($salesman['telephone']) . '</td>';
                        echo '<td>' . htmlspecialchars($salesman['nic']) . '</td>';
                        echo '<td>' . htmlspecialchars($salesman['address']) . '</td>';
                        echo '<td class="action-buttons">
                            <a href="javascript:void(0);" onclick="loadSalesmanDetails(' . htmlspecialchars($salesman['id']) . ')" class="edit">Edit</a>
                            <button onclick="confirmDelete(' . htmlspecialchars($salesman['id']) . ')"class="delete">Delete</button>
                        </td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="6">No salesmen found.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(salesmanId) {
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
                    // Send DELETE request using POST
                    fetch('../../controllers/salesman_controller.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                action: 'delete',
                                id: salesmanId,
                            }),
                        })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.status === 'success') {
                                Swal.fire('Deleted!', data.message, 'success');
                                // Remove the row from the table
                                const row = document.getElementById('salesman-row-' + salesmanId);
                                if (row) row.remove();
                            } else {
                                Swal.fire('Error!', data.message, 'error');
                            }
                        })
                        .catch((error) => {
                            console.error('Error:', error);
                            Swal.fire('Error!', 'Something went wrong.', 'error');
                        });
                }
            });
        }

        function loadSalesmanDetails(salesmanId) {
            // Fetch salesman details using AJAX
            fetch('../../controllers/salesman_controller.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'get',
                        id: salesmanId,
                    }),
                })
                .then((response) => response.json())
                .then((data) => {
                    if (data.status === 'success') {
                        const salesman = data.data;
                        // Populate the modal fields
                        document.getElementById('update-salesman-id').value = salesman.id;
                        document.getElementById('update-name').value = salesman.name;
                        document.getElementById('update-telephone').value = salesman.telephone;
                        document.getElementById('update-nic').value = salesman.nic;
                        document.getElementById('update-address').value = salesman.address;
                        // Show the modal
                        $('#updateSalesmanModal').modal('show');
                    } else {
                        alert(data.message);
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert('Error fetching salesman details.');
                });
        }
    </script>
</body>

</html>
