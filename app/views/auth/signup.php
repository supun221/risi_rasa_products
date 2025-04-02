<?php

include '../../models/Database.php';
session_start();
$user_name = $_SESSION['username'];
$user_role = $_SESSION['job_role'];
$user_branch = $_SESSION['store'];

if($user_role !== 'admin'){
    header('Location:../unauthorized/unauthorized_access.php');
}
require_once '../header1.php';
?>

<!DOCTYPE html>
<html>

<head>
    <title>Signup</title>
    <link rel="stylesheet" href="../../assets/css/signup_styles.css">
    <script>
        function validateForm() {
            const password = document.getElementById("password").value;
            const repassword = document.getElementById("repassword").value;

            if (password !== repassword) {
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
</style>
<body>
    <div class="signup-container">
        <form action="../../controllers/authController.php" method="POST" class="signup-form"
            onsubmit="return validateForm()">
            <h2>Signup</h2>
            <input type="text" name="username" placeholder="Username" required><br>
            <select name="store" required>
                <option value="" disabled selected>Select Store Type</option>
                <?php 
                    $sql = "SELECT branch_id, branch_name FROM branch";
                    $result = $db_conn->query($sql);
                
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($row["branch_name"]) . "'>" . htmlspecialchars($row["branch_name"]) . "</option>";
                        }
                    } else {
                        echo "<option value='' disabled>No branches available</option>";
                    }
                    $db_conn->close();
                ?>
            </select><br>

            <select name="job_role" required>
                <option value="" disabled selected>Select Job Role</option>
                <option value="staff">Staff</option>
                <option value="admin">Admin</option>    
                <option value="rep">Rep</option>    
            </select>
            
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="text" name="telephone" placeholder="Tel" required><br>
            <input type="password" name="password" id="password" placeholder="Password" required><br>
            <input type="password" name="repassword" id="repassword" placeholder="Re-Password" required><br>
            <button type="submit" name="signup">Signup</button>
        </form>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
</body>

</html>