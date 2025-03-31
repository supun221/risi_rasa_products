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
    <title>Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/user_styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../../assets/notifier/style.css">
    <script src="../../assets/notifier/index.var.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Maname&family=Noto+Serif:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Yaldevi:wght@200..700&display=swap" rel="stylesheet">
</head>
<style>
   .add-customer{
    margin-left: -1200px;

   }
   
   body{
    overflow-x: hidden;
    font-family: 'Poppins', serif;
   }

   .branch-reg-modal{
        bottom: 0;
        right: 0;
        position: absolute;
        padding: 10px;
        border-radius: 0 5px 0 0;
        box-shadow: 4px 4px 8px rgba(0,0,0,.4);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background-color: white;
        transition: .5s;
   }

   .reg-br-cont{
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        align-items: flex-start;
        margin: 10px 0;
   }

   .reg-br-label{
        color: grey;
        font-size: .8em;
   }

   .reg-br-inp{
        border: 2px solid grey;
        padding: 5px 10px;
        border-radius: 5px;
   }

   .reg-branch-btn{
        border: none;
        outline: none;
        background-color: #d35400;
        color: white;
        padding: 4px 10px;
        border-radius: 5px;
   }

   .reg-branch-btn:hover{
        cursor: pointer;
   }

   .reg-heading{
        position: absolute;
        top: -20px;
        left: 0;
        padding: 4px 10px;
        font-size: .8em;
        color: white;
        background-color: #2980b9;
        border-radius: 5px 5px 0 0;
   }

   .hide-br-reg{
        transform: translateX(100%);
   }

   .toggle-shop-btn{
        padding: 5px;
        border-radius: 4px 0 0 4px;
        background-color: #2980b9;
        color: white;
        position: absolute;
        left: -30px;
        bottom: 0;
        
   }

</style>
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
            Register
        </button>
        <span class="toggle-shop-btn" id="toggle-shop-btn" onclick="toggleBranchRegistrationModal()">
            <i class="fa-solid fa-shop"></i>
        </span>
     </div>

    <h1>User List</h1>
    <!-- <a href="../auth/signup.php" class="add-customer">Add User</a> -->
    <button class="add-customer" onclick="window.location.href='../auth/signup.php'">Add User</button>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Store</th>
                    <th>Email</th>
                    <th>Telephone</th>
                    <th>Actions</th>
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
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($user['username']) . '</td>';
                        echo '<td>' . htmlspecialchars($user['store']) . '</td>';
                        echo '<td>' . htmlspecialchars($user['Email']) . '</td>';
                        echo '<td>' . htmlspecialchars($user['telephone']) . '</td>';
                        echo '<td class="action-buttons">
                                <a href="show_user.php?id=' . $user['id'] . '" class="view">View</a>
                               
                                <button onclick="confirmDelete(' . $user['id'] . ')" class="delete">Delete</button>
                              </td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="5">No users found.</td></tr>';
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
            let notifier = new AWN()
            const branchName = document.getElementById("branch-name-reg").value.trim();
            const branchAddress = document.getElementById("branch-address-reg").value.trim();
            const branchPhone = document.getElementById("branch-phone-reg").value.trim();

            if (branchName === "" || branchAddress === "" || branchPhone === "") {
                alert("Please fill all fields!");
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
                body: `delete=true&user_id=${userId}`, // Send user_id
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
document.addEventListener("keydown", function(event) {
        if (event.code === "Home") {
            window.location.href = "../dashboard/index.php";
        }
    });


    </script>
</body>

</html>