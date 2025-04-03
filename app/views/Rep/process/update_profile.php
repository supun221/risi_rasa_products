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
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $telephone = isset($_POST['telephone']) ? trim($_POST['telephone']) : '';
    
    // Validate email format
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Invalid email format';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    try {
        // Prepare and execute query to update user profile
        $stmt = $conn->prepare("
            UPDATE signup 
            SET email = ?, telephone = ?
            WHERE id = ?
        ");
        $stmt->bind_param("ssi", $email, $telephone, $user_id);
        $stmt->execute();
        
        if ($stmt->affected_rows >= 0) { // Note: might be 0 if nothing changed
            $response['success'] = true;
            $response['message'] = 'Profile updated successfully';
        } else {
            $response['message'] = 'Failed to update profile';
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
