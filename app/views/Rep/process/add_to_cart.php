<?php
// Connect to database
require_once '../../../../config/databade.php';
session_start();

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'item' => null
];

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $lorryStockId = isset($_POST['lorry_stock_id']) ? (int)$_POST['lorry_stock_id'] : 0;
    $productName = isset($_POST['product_name']) ? trim($_POST['product_name']) : '';
    $barcode = isset($_POST['barcode']) ? trim($_POST['barcode']) : '';
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    $freeQuantity = isset($_POST['free_quantity']) ? (int)$_POST['free_quantity'] : 0;
    $unitPrice = isset($_POST['unit_price']) ? (float)$_POST['unit_price'] : 0;
    $discountPercent = isset($_POST['discount_percent']) ? (float)$_POST['discount_percent'] : 0;
    $discountAmount = isset($_POST['discount_amount']) ? (float)$_POST['discount_amount'] : 0;
    $subtotal = isset($_POST['subtotal']) ? (float)$_POST['subtotal'] : 0;
    
    // Validate required fields
    if (empty($productName) || $quantity <= 0 || $unitPrice <= 0) {
        $response['message'] = 'Missing required fields. Please check product name, quantity, and price.';
    } else {
        // If subtotal is not provided, calculate it
        if ($subtotal <= 0) {
            $subtotal = $quantity * $unitPrice;
            
            // Apply percentage discount
            if ($discountPercent > 0) {
                $subtotal = $subtotal - ($subtotal * ($discountPercent / 100));
            }
            
            // Apply fixed discount
            if ($discountAmount > 0) {
                $subtotal = max(0, $subtotal - $discountAmount);
            }
        }
        
        // Create item for cart
        $item = [
            'lorry_stock_id' => $lorryStockId,
            'product_name' => $productName,
            'barcode' => $barcode,
            'quantity' => $quantity,
            'free_quantity' => $freeQuantity,
            'unit_price' => $unitPrice,
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
            'subtotal' => $subtotal
        ];
        
        // Initialize cart in session if it doesn't exist
        if (!isset($_SESSION['pos_cart'])) {
            $_SESSION['pos_cart'] = [];
        }
        
        // Add item to cart
        $_SESSION['pos_cart'][] = $item;
        
        // Set success response
        $response['success'] = true;
        $response['message'] = 'Product added to cart';
        $response['item'] = $item;
    }
} else {
    $response['message'] = 'Invalid request method';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
