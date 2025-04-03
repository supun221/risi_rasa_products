<?php
// Make sure session is started before attempting to destroy it
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session variables
$_SESSION = array();

// If session cookie is used, destroy the cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Log the logout event (optional)
error_log("User logged out at " . date('Y-m-d H:i:s'));

// Redirect to login page
header("Location: ../views/auth/login.php");
exit();
?>
