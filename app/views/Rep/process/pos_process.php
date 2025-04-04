<?php
// Process POS form data
// Connect to database, validate input, process sale, etc.

// Example:
// $product = $_POST['product'];
// $quantity = $_POST['quantity'];
// $customer = $_POST['customer'];

// Ensure discount amount is properly parsed as float
$discount_amount = isset($_POST['discount_amount']) ? (float)$_POST['discount_amount'] : 0;

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Sale processed successfully',
    'invoice_number' => $invoice_number,
    'subtotal' => number_format($subtotal, 2),
    'discount_amount' => number_format($discount_amount, 2),
    'total' => number_format($total, 2)
]);
