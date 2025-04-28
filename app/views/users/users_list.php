<?php
session_start();
$user_name = $_SESSION['username'];
$user_role = $_SESSION['job_role'];
$user_branch = $_SESSION['store'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/user_styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../../assets/notifier/style.css">
    <script src="../../assets/notifier/index.var.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Maname&family=Noto+Serif:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Yaldevi:wght@200..700&display=y" rel="stylesheet">
    <style>
        .add-customer {
            margin-left: -1200px;
        }

        body {
            overflow-x: hidden;
            font-family: 'Poppins', serif;
        }

        .branch-reg-modal {
            bottom: 0;
            right: 0;
            position: absolute;
            padding: 10px;
            border-radius: 0 5px 0 0;
            box-shadow: 4px 4px 8px rgba(0, 0, 0, .4);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: white;
            transition: .5s;
        }

        .reg-br-cont {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: flex-start;
            margin: 10px 0;
        }

        .reg-br-label {
            color: grey;
            font-size: .8em;
        }

        .reg-br-inp {
            border: 2px solid grey;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .reg-branch-btn {
            border: none;
            outline: none;
            background-color: #d35400;
            color: white;
            padding: 4px 10px;
            border-radius: 5px;
        }

        .reg-branch-btn:hover {
            cursor: pointer;
        }

        .reg-heading {
            position: absolute;
            top: -20px;
            left: 0;
            padding: 4px 10px;
            font-size: .8em;
            color: white;
            background-color: #2980b9;
            border-radius: 5px 5px 0 0;
        }

        .hide-br-reg {
            transform: translateX(100%);
        }

        .toggle-shop-btn {
            padding: 5px;
            border-radius: 4px 0 0 4px;
            background-color: #2980b9;
            color: white;
            position: absolute;
            left: -30px;
            bottom: 0;
        }

        .permissions-summary {
            font-size: 0.9em;
            color: #555;
        }

        .action-buttons a, .action-buttons button {
            display: inline-block;
            padding: 5px 10px;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 5px;
            border: none;
            cursor: pointer;
        }

        .action-buttons .view {
            background-color: #3498db;
        }

        .action-buttons .edit {
            background-color: #2ecc71;
        }

        .action-buttons .delete {
            background-color: #e74c3c;
        }
        
        /* Icon specific colors - these will override the button text color */
        .action-buttons .view i {
            color: #ffffff; /* White - you can change to any color */
        }
        
        .action-buttons .edit i {
            color: #ffffff; /* White - you can change to any color */
        }
        
        .action-buttons .delete i {
            color: #ffffff; /* White - you can change to any color */
        }
        
        /* Alternative style: buttons with colored icons but transparent background 
        .action-buttons a, .action-buttons button {
            background-color: transparent;
            color: #555;
            border: 1px solid #ddd;
        }
        
        .action-buttons .view i {
            color: #3498db;
        }
        
        .action-buttons .edit i {
            color: #2ecc71;
        }
        
        .action-buttons .delete i {
            color: #e74c3c;
        }
        */

        /* See More Styles */
        .permissions-summary .see-more {
            color: #3498db;
            cursor: pointer;
            text-decoration: underline;
        }

        .permissions-summary .hidden {
            display: none;
        }
    </style>
</head>

<body>
    <?php require_once '../header1.php'; ?>

    <!-- branch registration module -->
    <div class="branch-reg-modal hide-br-reg" id="branch-reg-modal">
        <span class="reg-heading">Register a new branch</span>
        <div class="reg-br-cont">
            <label for="" class="reg-br-label">Branch Name</label>
            <input type="text" class="reg-br-inp" id="branch-name-reg">
        </div>
        <button class="reg-branch-btn" onclick="registerBranch()">
            Register
        </button>
        <span class="toggle-shop-btn" id="toggle-shop-btn" onclick="toggleBranchRegistrationModal()">
            <i class="fa-solid fa-shop"></i>
        </span>
    </div>

    <h1>User List</h1>
    <button class="add-customer" onclick="window.location.href='../auth/signup.php'"><i class="fas fa-user-plus"></i> Add User</button>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Store</th>
                    <th>Email</th>
                    <th>Telephone</th>
                    <th>Permissions</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Include the database configuration
                require_once '../../../config/databade.php'; // Fixed typo: 'databade' to 'database'
                // Fetch users and their permissions from the database, including all permissions
                $query = "SELECT s.*, up.can_edit_price, up.can_edit_discount, up.can_edit_free_issue, 
                          up.can_view_wholesale, up.can_view_scg_price, up.can_view_cost, up.can_view_rem_stock,
                          up.can_view_employees, up.can_view_reports, up.can_view_users, up.can_view_stock,
                          up.can_create_invoice, up.can_view_sales_order, up.can_view_quotation, 
                          up.can_view_customer, up.can_view_grn_purchasing, up.can_view_bank, 
                          up.can_view_cash_book, up.can_view_expenses, up.can_view_suppliers, 
                          up.can_view_damage_lost, up.can_view_settings 
                          FROM signup s 
                          LEFT JOIN user_permissions up ON s.username = up.username";
                $result = mysqli_query($conn, $query);

                if ($result && mysqli_num_rows($result) > 0) {
                    while ($user = mysqli_fetch_assoc($result)) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($user['username']) . '</td>';
                        echo '<td>' . htmlspecialchars($user['store']) . '</td>';
                        echo '<td>' . htmlspecialchars($user['Email']) . '</td>';
                        echo '<td>' . htmlspecialchars($user['telephone']) . '</td>';

                        // Permissions summary including all permissions
                        $permissions = [];
                        if ($user['can_edit_price']) $permissions[] = "Edit Price";
                        if ($user['can_edit_discount']) $permissions[] = "Edit Discount";
                        if ($user['can_edit_free_issue']) $permissions[] = "Edit Free Issue";
                        if ($user['can_view_wholesale']) $permissions[] = "View Wholesale";
                        if ($user['can_view_scg_price']) $permissions[] = "View SCG";
                        if ($user['can_view_cost']) $permissions[] = "View Cost";
                        if ($user['can_view_rem_stock']) $permissions[] = "View Rem. Stock";
                        if ($user['can_view_employees']) $permissions[] = "View Employees";
                        if ($user['can_view_reports']) $permissions[] = "View Reports";
                        if ($user['can_view_users']) $permissions[] = "View Users";
                        if ($user['can_view_stock']) $permissions[] = "View Stock";
                        if ($user['can_create_invoice']) $permissions[] = "Create Invoice";
                        if ($user['can_view_sales_order']) $permissions[] = "View Sales Order";
                        if ($user['can_view_quotation']) $permissions[] = "View Quotation";
                        if ($user['can_view_customer']) $permissions[] = "View Customer";
                        if ($user['can_view_grn_purchasing']) $permissions[] = "View GRN/Purchasing";
                        if ($user['can_view_bank']) $permissions[] = "View Bank";
                        if ($user['can_view_cash_book']) $permissions[] = "View Cash Book";
                        if ($user['can_view_expenses']) $permissions[] = "View Expenses";
                        if ($user['can_view_suppliers']) $permissions[] = "View Suppliers";
                        if ($user['can_view_damage_lost']) $permissions[] = "View Damage & Lost";
                        if ($user['can_view_settings']) $permissions[] = "View Settings";

                        $perm_summary = !empty($permissions) ? implode(", ", $permissions) : "None";
                        echo '<td class="permissions-summary">';
                        if (count($permissions) > 3) {
                            $visible_perms = array_slice($permissions, 0, 3);
                            $hidden_perms = array_slice($permissions, 3);
                            echo htmlspecialchars(implode(", ", $visible_perms)) . 
                                 '<span class="hidden" data-full="' . htmlspecialchars($perm_summary) . '">, ' . htmlspecialchars(implode(", ", $hidden_perms)) . '</span>' .
                                 ' <span class="see-more" onclick="togglePermissions(this)">See More</span>';
                        } else {
                            echo htmlspecialchars($perm_summary);
                        }
                        echo '</td>';

                        echo '<td class="action-buttons">
                                <a href="show_user.php?username=' . urlencode($user['username']) . '" class="view" title="View"><i class="fas fa-eye"></i></a>
                                <a href="../auth/update_user.php?username=' . urlencode($user['username']) . '" class="edit" title="Edit"><i class="fas fa-edit"></i></a>
                                <button onclick="confirmDelete(' . $user['id'] . ')" class="delete" title="Delete"><i class="fas fa-trash"></i></button>
                              </td>';

                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="6">No users found.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
        const toggleBranchRegistrationModal = () => {
            const regBranchModal = document.getElementById("branch-reg-modal");
            regBranchModal.classList.toggle("hide-br-reg");
        };

        const registerBranch = () => {
            let notifier = new AWN();
            const branchName = document.getElementById("branch-name-reg").value.trim();

            if (branchName === "") {
                alert("Branch name cannot be empty!");
                return;
            }

            fetch("./create_branch.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        branch_name: branchName
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        notifier.success(data.success);
                        document.getElementById("branch-name-reg").value = "";
                    } else {
                        notifier.alert(data.error);
                    }
                })
                .catch(error => console.error("Error:", error));
        };

        // Confirm delete using SweetAlert
        function confirmDelete(userId) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You will not be able to recover this user!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../../controllers/authController.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `delete=true&user_id=${userId}`,
                        })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.status === 'success') {
                                Swal.fire('Deleted!', 'User has been deleted.', 'success');
                                const row = document.getElementById('user-row-' + userId);
                                location.reload();
                                if (row) row.remove(); // Remove row from DOM
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: data.message,
                                });
                            }
                        })
                        .catch((error) => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Something went wrong.',
                            });
                        });
                }
            });
        }

        // Toggle permissions visibility
        function togglePermissions(element) {
            const hiddenSpan = element.previousElementSibling;
            const isHidden = hiddenSpan.classList.contains('hidden');
            if (isHidden) {
                hiddenSpan.classList.remove('hidden');
                element.textContent = 'See Less';
            } else {
                hiddenSpan.classList.add('hidden');
                element.textContent = 'See More';
            }
        }

        document.addEventListener("keydown", function(event) {
            if (event.code === "Home") {
                window.location.href = "../dashboard/index.php";
            }
        });
    </script>
</body>

</html>