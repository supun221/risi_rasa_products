<?php
require_once '../header1.php';

require_once '../../../config/databade.php';

// Fetch branches from database
$sql = "SELECT branch_id, branch_name FROM branch";
$result = $conn->query($sql);

// Store branches in an array
$branches = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $branches[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <link rel="stylesheet" href="../../assets/css/login_styles.css">

</head>

<body>
    <br>
    <form action="../../controllers/authController.php" method="POST" class="login-form">
        <h2>Login</h2>
        <select name="store" id="storeSelect" required>
            <option value="" disabled selected>Select Store Type</option>
            <?php
            foreach($branches as $branch) {
                echo "<option value='" . htmlspecialchars($branch['branch_name']) . "'>" . htmlspecialchars($branch['branch_name']) . "</option>";
            }
            ?>
        </select><br>
        <input type="text" name="username" placeholder="User name" required><br>
        <input type="password" name="password" placeholder="Password" required><br><br><br>
        <button type="submit" name="login">Login</button>
    </form>
</body>

</html>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const formElements = document.querySelectorAll('.login-form select, .login-form input, .login-form button');
        const elementsArray = Array.from(formElements);

        document.addEventListener('keydown', function(event) {
            const activeElement = document.activeElement;
            const currentIndex = elementsArray.indexOf(activeElement);

            // Down arrow key navigation
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                if (currentIndex >= 0 && currentIndex < elementsArray.length - 1) {
                    elementsArray[currentIndex + 1].focus();
                }
            }

            // Up arrow key navigation
            if (event.key === 'ArrowUp') {
                event.preventDefault();
                if (currentIndex > 0) {
                    elementsArray[currentIndex - 1].focus();
                }
            }

            // Enter key handling for navigating and submitting
            if (event.key === 'Enter') {
                if (activeElement.tagName === 'SELECT' || activeElement.tagName === 'INPUT') {
                    event.preventDefault(); // Prevent default behavior for navigation
                    if (currentIndex < elementsArray.length - 1) {
                        elementsArray[currentIndex + 1].focus();
                    } else {
                        elementsArray[currentIndex].form.submit(); // Submit the form if last element
                    }
                } else if (activeElement.tagName === 'BUTTON') {
                    // Allow default action (form submission) on the button
                    return;
                }
            }
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);

        if (urlParams.has('error')) {
            swal({
                title: "Login Failed",
                text: "Invalid username, password, or store.",
                icon: "error",
                button: "Try Again"
            }).then(() => {
                // Clean the URL after showing the alert
                window.history.replaceState({}, document.title, window.location.pathname);
            });
        }
    });
</script>