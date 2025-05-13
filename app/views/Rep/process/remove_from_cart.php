<?php
// Start session
session_start();

// Initialize response array
$response = [
    'success' => false,
    'message' => ''
];

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get index from request
    $index = isset($_POST['index']) ? (int)$_POST['index'] : -1;
    
    // Check if cart exists and index is valid
    if (isset($_SESSION['pos_cart']) && is_array($_SESSION['pos_cart']) && isset($_SESSION['pos_cart'][$index])) {
        // Remove item from cart
        array_splice($_SESSION['pos_cart'], $index, 1);
        $response['success'] = true;
        $response['message'] = 'Item removed from cart';
    } else {
        $response['message'] = 'Invalid cart item index';
    }
} else {
    $response['message'] = 'Invalid request method';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
