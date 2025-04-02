<?php
// Process retrieve stock form data
// Connect to database, validate input, retrieve from lorry, etc.

// Example:
// $product = $_POST['product'];
// $quantity = $_POST['quantity'];
// $reason = $_POST['reason'];
// $customer = $_POST['customer'];

// Return JSON response
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Stock retrieved']);
