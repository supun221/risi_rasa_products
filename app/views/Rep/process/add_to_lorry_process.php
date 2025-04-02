<?php
// Process add to lorry form data
// Connect to database, validate input, add to lorry, etc.

// Example:
// $product = $_POST['product'];
// $quantity = $_POST['quantity'];

// Return JSON response
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Product added to lorry']);
