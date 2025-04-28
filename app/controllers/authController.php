<?php
require_once '../../config/databade.php';
require_once '../models/User.php';

// Initialize the User model
$userModel = new User($conn);

// Start the session securely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper function for redirection with delay
function redirectWithMessage($message, $url, $delay = 2)
{
    echo $message;
    header("refresh:$delay;url=$url");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup'])) {
    $username = htmlspecialchars(trim($_POST['username']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $telephone = $_POST['telephone'];
    $store = $_POST['store'];
    $job_role = $_POST['job_role'];

    if (empty($username) || empty($password) || empty($store) || empty($telephone)) {
        redirectWithMessage("All fields are required!", "../views/auth/signup.php");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirectWithMessage("Invalid email format!", "../views/auth/signup.php");
    }

    // if ($userModel->emailExists($email)) {
    //     redirectWithMessage("Email already exists!", "../views/auth/signup.php");
    // }
    if ($userModel->usernameExists($username)) {
        redirectWithMessage("Username already exists!", "../views/auth/signup.php");
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    if ($userModel->createUser($store, $username, $email, $hashedPassword, $telephone, $job_role)) {
        redirectWithMessage("Signup successful! Redirecting to login.", "../views/auth/login.php");
    } else {
        redirectWithMessage("Signup failed! Please try again.", "../views/auth/signup.php");
    }
}

// Login Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    // Sanitize user inputs
    $username = htmlspecialchars(trim($_POST['username']));
    $password = $_POST['password'];
    $store = htmlspecialchars(trim($_POST['store']));

    // Validate inputs
    if (empty($username) || empty($password) || empty($store)) {
        redirectWithMessage("All fields are required!", "../views/auth/login.php");
    }

    // Get the user by username and store
    $user = $userModel->getUserByUsernameAndStore($username, $store);
    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['store'] = $user['store'];
        $_SESSION['job_role'] = $user['job_role'];

        // Redirect to the dashboard
        header("Location: ../views/dashboard/index.php");
        exit();
    } else {
        // Redirect with error parameter instead of using redirectWithMessage
        header("Location: ../views/auth/login.php?error=invalid");
        exit();
    }
}
// Delete User Logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $userId = intval($_POST['user_id']); // Use 'user_id'

    if (empty($userId)) {
        echo json_encode(['status' => 'error', 'message' => 'User ID is required.']);
        exit();
    }

    $query = "DELETE FROM signup WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare statement.']);
        exit();
    }

    mysqli_stmt_bind_param($stmt, "i", $userId);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'User deleted successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete user.']);
    }

    mysqli_stmt_close($stmt);
    exit();
}

// Add this code to handle the update_user form submission
if (isset($_POST['update_user'])) {
    $original_username = $_POST['original_username'];
    $username = $_POST['username'];
    $store = $_POST['store'];
    $job_role = $_POST['job_role'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $password = $_POST['password'];

    // Check if we need to update the password
    $password_update = '';
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $password_update = ", password = '$hashed_password'";
    }

    // Update user information
    $sql = "UPDATE signup SET 
            username = '$username', 
            store = '$store', 
            job_role = '$job_role', 
            Email = '$email', 
            telephone = '$telephone'
            $password_update 
            WHERE username = '$original_username'";

    if ($conn->query($sql) === TRUE) {
        // Redirect to user list with success message
        header("Location: ../views/users/users_list.php?update=success");
        exit();
    } else {
        // Redirect back with error
        header("Location: ../views/auth/update_user.php?username=$original_username&error=1");
        exit();
    }
}

// Show User Logic
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['user_id'])) {
    $userId = intval($_GET['user_id']);

    if (empty($userId)) {
        redirectWithMessage("User ID is required!", "../views/dashboard/users.php");
    }

    $query = "SELECT * FROM signup WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        redirectWithMessage("Failed to prepare query statement.", "../views/dashboard/users.php");
    }

    mysqli_stmt_bind_param($stmt, "i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $user = mysqli_fetch_assoc($result);

    if ($user) {
        // Display user information (this part would ideally be handled in the view layer)
        echo "<h2>User Information</h2>";
        echo "<p>Username: " . htmlspecialchars($user['username']) . "</p>";
        echo "<p>Email: " . htmlspecialchars($user['Email']) . "</p>";
        echo "<p>Telephone: " . htmlspecialchars($user['telephone']) . "</p>";
        echo "<p>Store: " . htmlspecialchars($user['store']) . "</p>";
    } else {
        redirectWithMessage("User not found!", "../views/dashboard/users.php");
    }

    mysqli_stmt_close($stmt);
}
?>