<!DOCTYPE html>
<html>

<head>
    <?php
    require_once '../header1.php';
    ?>
    <title>Damage List</title>
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

        .action-buttons a,
        .action-buttons button {
            margin-right: 5px;
            text-decoration: none;
            color: #007bff;
            cursor: pointer;
        }

        .action-buttons button:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <h1>Damage List</h1>
    <button type="button" class="add-customer" data-toggle="modal" data-target="#addDamageModal">Add Damage</button>
    <?php
    require_once 'add_damage.php'; // Include add damage modal

    // Database connection
    require_once '../../../config/databade.php';

    // Fetch damages from the database
    $damages = [];
    $query = "SELECT id, product_name, damage_description, damage_quantity, price ,barcode FROM damages";
    $result = mysqli_query($conn, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $damages[] = $row;
        }
    } else {
        echo "<p>Error fetching damages: " . mysqli_error($conn) . "</p>";
    }

    include 'update_damage.php'; // Include update damage modal
    ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Product Name</th>
                    <th>Barcode</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($damages)) {
                    foreach ($damages as $index => $damage) {
                        echo '<tr id="damage-row-' . htmlspecialchars($damage['id']) . '">';
                        echo '<td>' . ($index + 1) . '</td>';
                        echo '<td>' . htmlspecialchars($damage['product_name']) . '</td>';
                        echo '<td>' . htmlspecialchars($damage['barcode']) . '</td>';
                        echo '<td>' . htmlspecialchars($damage['damage_description']) . '</td>';
                        echo '<td>' . htmlspecialchars($damage['damage_quantity']) . '</td>';
                        echo '<td>' . htmlspecialchars($damage['price']) . '</td>';
                        echo '<td class="action-buttons">
                            <a href="javascript:void(0);" onclick="openUpdateDamageModal(' . htmlspecialchars($damage['id']) . ')" class="edit">Edit</a>
                            <button onclick="confirmDelete(' . htmlspecialchars($damage['id']) . ')" class="delete">Delete</button>
                        </td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="6">No damages found.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(damageId) {
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
                    fetch('../../controllers/damage_controller.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                action: 'delete',
                                id: damageId,
                            }),
                        })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.status === 'success') {
                                Swal.fire('Deleted!', data.message, 'success');
                                const row = document.getElementById('damage-row-' + damageId);
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

        // Function to open the update modal and populate the form
function openUpdateDamageModal(damageId) {
    $.ajax({
        url: '../../controllers/damage_controller.php',
        type: 'post',
        contentType: 'application/json',
        data: JSON.stringify({
            action: 'get_by_id',
            id: damageId
        }),
        success: function (response) {
            if (response.status === 'success') {
                const damage = response.data;

                // Populate form fields with fetched data
                $('#update-damage-id').val(damage.id);
                $('#update-product-name').val(damage.product_name);
                $('#update-damage-description').val(damage.damage_description);
                $('#update-damage-quantity').val(damage.damage_quantity);
                $('#update-damage-price').val(damage.price);
                $('#update-barcode').val(damage.barcode);

                // Show the modal
                $('#updateDamageModal').modal('show');
            } else {
                Swal.fire('Error!', response.message, 'error');
            }
        },
        error: function () {
            Swal.fire('Error!', 'There was a problem fetching the damage details.', 'error');
        }
    });
}
document.addEventListener("keydown", function (event) {
        if (event.code === "Home") {
            window.location.href = "../dashboard/index.php";
        }
    });

    </script>
</body>

</html>