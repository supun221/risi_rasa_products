<?php
// Connect to database
require_once '../../../../config/databade.php';
session_start();

// Initialize response array
$response = [
    'success' => false,
    'message' => ''
];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'User not logged in';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Check if form data is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user ID from session
    $user_id = (int)$_SESSION['user_id'];
    
    // Get form data
    $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    
    // Validate passwords
    if (empty($current_password) || empty($new_password)) {
        $response['message'] = 'Current and new passwords are required';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // Check if new password is at least 6 characters
    if (strlen($new_password) < 6) {
        $response['message'] = 'New password must be at least 6 characters long';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    try {
        // First verify current password
        $stmt = $conn->prepare("SELECT password FROM signup WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Verify current password
            if (password_verify($current_password, $user['password'])) {
                // Hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update password in database
                $update_stmt = $conn->prepare("UPDATE signup SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $hashed_password, $user_id);
                $update_stmt->execute();
                
                if ($update_stmt->affected_rows > 0) {
                    $response['success'] = true;
                    $response['message'] = 'Password changed successfully';
                } else {
                    $response['message'] = 'Failed to update password';
                }
            } else {
                $response['message'] = 'Current password is incorrect';
            }
        } else {
            $response['message'] = 'User not found';
        }
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request method';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
