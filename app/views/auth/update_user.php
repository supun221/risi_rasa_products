<?php

include '../../models/Database.php';
session_start();
$user_name = $_SESSION['username'];
$user_role = $_SESSION['job_role'];
$user_branch = $_SESSION['store'];
require_once '../header1.php';
if($user_role !== 'admin'){
    header('Location:../unauthorized/unauthorized_access.php');
}

// Get username from URL parameter
$username_to_edit = isset($_GET['username']) ? $_GET['username'] : '';

if (empty($username_to_edit)) {
    echo "<script>alert('No user specified'); window.location.href='../users/users_list.php';</script>";
    exit;
}

// Fetch user data
$sql = "SELECT * FROM signup WHERE username = ?";
$stmt = $db_conn->prepare($sql);
$stmt->bind_param("s", $username_to_edit);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('User not found'); window.location.href='../users/users_list.php';</script>";
    exit;
}

$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Update User</title>
    <link rel="stylesheet" href="../../assets/css/signup_styles.css">
    <script>
        function validateForm() {
            const password = document.getElementById("password").value;
            const repassword = document.getElementById("repassword").value;

            if (password !== '' && password !== repassword) {
                alert("Passwords do not match!");
                return false;
            }
            return true;
        }
    </script>
</head>
<style>
    option,input{
        text-transform: capitalize;
    }
    .update-container {
        max-width: 500px;
        margin: 0 auto;
    }
    .note {
        font-size: 0.8em;
        color: #666;
        margin-top: 10px;
    }
</style>
<body>
    <div class="signup-container update-container">
        <form action="../../controllers/authController.php" method="POST" class="signup-form"
            onsubmit="return validateForm()">
            <h2>Update User</h2>
            
            <input type="hidden" name="original_username" value="<?php echo htmlspecialchars($user['username']); ?>">
            
            <input type="text" name="username" placeholder="Username" value="<?php echo htmlspecialchars($user['username']); ?>" required><br>
            
            <select name="store" required>
                <option value="" disabled>Select Store Type</option>
                <?php 
                    $sql = "SELECT branch_id, branch_name FROM branch";
                    $branch_result = $db_conn->query($sql);
                
                    if ($branch_result->num_rows > 0) {
                        while ($row = $branch_result->fetch_assoc()) {
                            $selected = ($row["branch_name"] == $user['store']) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($row["branch_name"]) . "' $selected>" . htmlspecialchars($row["branch_name"]) . "</option>";
                        }
                    } else {
                        echo "<option value='' disabled>No branches available</option>";
                    }
                ?>
            </select><br>

            <select name="job_role" required>
                <option value="" disabled>Select Job Role</option>
                <option value="staff" <?php echo ($user['job_role'] == 'staff') ? 'selected' : ''; ?>>Staff</option>
                <option value="admin" <?php echo ($user['job_role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>    
            </select>
            
            <input type="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($user['Email']); ?>" required><br>
            <input type="text" name="telephone" placeholder="Tel" value="<?php echo htmlspecialchars($user['telephone']); ?>" required><br>
            
            <input type="password" name="password" id="password" placeholder="New Password (leave blank to keep current)"><br>
            <input type="password" name="repassword" id="repassword" placeholder="Confirm New Password"><br>
            
            <p class="note">Leave password fields empty to keep the current password.</p>
            
            <button type="submit" name="update_user">Update User</button>
        </form>
        <p><a href="../users/users_list.php">Back to User List</a></p>
    </div>
</body>

</html>
