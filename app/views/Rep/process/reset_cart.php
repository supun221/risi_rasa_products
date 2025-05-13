<?php
// Start session
session_start();

// Clear cart items
$_SESSION['pos_cart'] = [];

// Return success response
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Cart reset successful'
]);
?>
