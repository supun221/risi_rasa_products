<?php
// Process POS form data
// Connect to database, validate input, process sale, etc.

// Example:
// $product = $_POST['product'];
// $quantity = $_POST['quantity'];
// $customer = $_POST['customer'];

// Return JSON response
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Sale completed']);
