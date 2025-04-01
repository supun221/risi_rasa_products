<?php 
session_start();
$user_name = $_SESSION['username'];
$user_role = $_SESSION['job_role'];
$user_branch = $_SESSION['store'];

if($user_role !== 'admin'){
    header('Location:../unauthorized/unauthorized_access.php');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management | Risi Rasa Products</title>
    <link rel="stylesheet" href="../../assets/css/user_styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/notifier/style.css">
    <script src="../../assets/notifier/index.var.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Maname&family=Noto+Serif:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Yaldevi:wght@200..700&display=swap" rel="stylesheet">
    <style>
        /* General Layout & Typography */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
            overflow-x: hidden;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .page-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
            color: #2c3e50;
        }

        .content-wrapper {
            max-width: 1400px;
            margin: 0 auto;
            padding: 25px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        /* Add User Button */
        .action-btn {
            background-color: #4361ee;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            padding: 12px 20px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 3px 6px rgba(67, 97, 238, 0.2);
        }

        .action-btn:hover {
            background-color: #3249c2;
            transform: translateY(-2px);
            box-shadow: 0 5px 8px rgba(67, 97, 238, 0.3);
        }

        .action-btn:active {
            transform: translateY(0);
        }

        .action-btn i {
            font-size: 16px;
        }

        /* Table Styles */
        .table-container {
            overflow-x: auto;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background-color: #fff;
            font-size: 14px;
        }

        table th {
            background-color: #f8f9fa;
            color: #516173;
            font-weight: 600;
            padding: 14px 12px;
            text-align: left;
            border-bottom: 2px solid #e0e0e0;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        table td {
            padding: 14px 12px;
            border-bottom: 1px solid #f0f0f0;
            color: #3a3f51;
            vertical-align: middle;
        }

        table tr:last-child td {
            border-bottom: none;
        }

        table tr:hover {
            background-color: #f8faff;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .action-buttons a, 
        .action-buttons button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            border-radius: 6px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 36px;
            height: 36px;
        }

        .action-btn-view {
            background-color: #17a2b8;
        }

        .action-btn-view:hover {
            background-color: #138496;
            transform: translateY(-2px);
            box-shadow: 0 3px 6px rgba(23, 162, 184, 0.2);
        }

        .action-btn-delete {
            background-color: #ef476f;
        }

        .action-btn-delete:hover {
            background-color: #d64161;
            transform: translateY(-2px);
            box-shadow: 0 3px 6px rgba(239, 71, 111, 0.2);
        }

        /* Branch Registration Modal Styles */
        .branch-reg-modal {
            bottom: 0;
            right: 0;
            position: absolute;
            padding: 20px;
            border-radius: 8px 0 0 0;
            box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background-color: white;
            transition: .5s;
            z-index: 100;
        }

        .reg-br-cont {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: flex-start;
            margin: 10px 0;
            width: 100%;
        }

        .reg-br-label {
            color: #516173;
            font-size: 14px;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .reg-br-inp {
            border: 1px solid #e0e0e0;
            padding: 10px;
            border-radius: 6px;
            width: 100%;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .reg-br-inp:focus {
            outline: none;
            border-color: #4361ee;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        .reg-branch-btn {
            border: none;
            outline: none;
            background-color: #fca311;
            color: white;
            padding: 10px 15px;
            border-radius: 6px;
            font-weight: 500;
            margin-top: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }

        .reg-branch-btn:hover {
            background-color: #e09010;
            transform: translateY(-2px);
            box-shadow: 0 3px 6px rgba(252, 163, 17, 0.2);
        }

        .reg-heading {
            position: absolute;
            top: -20px;
            left: 0;
            padding: 8px 15px;
            font-size: 14px;
            font-weight: 600;
            color: white;
            background-color: #4361ee;
            border-radius: 8px 8px 0 0;
        }

        .hide-br-reg {
            transform: translateX(100%);
        }

        .toggle-shop-btn {
            padding: 10px;
            border-radius: 6px 0 0 6px;
            background-color: #4361ee;
            color: white;
            position: absolute;
            left: -40px;
            bottom: 20px;
            cursor: pointer;
            box-shadow: -3px 2px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .toggle-shop-btn:hover {
            background-color: #3249c2;
            left: -45px;
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
        <div class="reg-br-cont">
            <label for="" class="reg-br-label">Address</label>
            <input type="text" class="reg-br-inp" id="branch-address-reg">
        </div>
        <div class="reg-br-cont">
            <label for="" class="reg-br-label">Phone number</label>
            <input type="text" class="reg-br-inp" id="branch-phone-reg">
        </div>
        <button class="reg-branch-btn" onclick="registerBranch()">
            <i class="fas fa-plus-circle"></i> Register Branch
        </button>
        <span class="toggle-shop-btn" id="toggle-shop-btn" onclick="toggleBranchRegistrationModal()">
            <i class="fa-solid fa-shop"></i>
        </span>
    </div>

    <div class="content-wrapper">
        <div class="page-header">
            <h1>User Management</h1>
            <button class="action-btn" onclick="window.location.href='../auth/signup.php'">
                <i class="fas fa-user-plus"></i> Add User
            </button>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th width="20%">Username</th>
                        <th width="20%">Store</th>
                        <th width="25%">Email</th>
                        <th width="15%">Telephone</th>
                        <th width="20%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Include the database configuration
                    require_once '../../../config/databade.php';
                    // Fetch users from the database
                    $query = "SELECT * FROM signup";
                    $result = mysqli_query($conn, $query);

                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($user = mysqli_fetch_assoc($result)) {
                            echo '<tr id="user-row-' . $user['id'] . '">';
                            echo '<td><strong>' . htmlspecialchars($user['username']) . '</strong></td>';
                            echo '<td>' . htmlspecialchars($user['store']) . '</td>';
                            echo '<td>' . htmlspecialchars($user['Email']) . '</td>';
                            echo '<td>' . htmlspecialchars($user['telephone']) . '</td>';
                            echo '<td class="action-buttons">
                              
                                <button onclick="confirmDelete(' . $user['id'] . ')" class="action-btn-delete" title="Delete User"><i class="fas fa-trash-alt"></i></button>
                              </td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="5" style="text-align:center;padding:20px;">No users found.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const toggleBranchRegistrationModal = () => {
            const regBranchModal = document.getElementById("branch-reg-modal");
            regBranchModal.classList.toggle("hide-br-reg");
        };

        const registerBranch = () => {
            let notifier = new AWN()
            const branchName = document.getElementById("branch-name-reg").value.trim();
            const branchAddress = document.getElementById("branch-address-reg").value.trim();
            const branchPhone = document.getElementById("branch-phone-reg").value.trim();

            if (branchName === "" || branchAddress === "" || branchPhone === "") {
                notifier.alert("Please fill all fields!");
                return;
            }

            fetch("./create_branch.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ branch_name: branchName, branchAddress:branchAddress, branchPhone:branchPhone})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    notifier.success(data.success)
                    document.getElementById("branch-name-reg").value = "";
                    document.getElementById("branch-address-reg").value = "";
                    document.getElementById("branch-phone-reg").value = "";
                } else {
                    notifier.alert(data.error)
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
                            if (row) row.remove();
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
        
        document.addEventListener("keydown", function(event) {
            if (event.code === "Home") {
                window.location.href = "../dashboard/index.php";
            }
        });
    </script>
</body>

</html>