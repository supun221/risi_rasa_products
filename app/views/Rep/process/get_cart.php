<?php
// Start session
session_start();

// Initialize response array
$response = [
    'success' => true,
    'cart' => [],
    'totals' => [
        'subtotal' => 0,
        'totalDiscount' => 0,
        'grandTotal' => 0
    ]
];

// Get cart from session
if (isset($_SESSION['pos_cart']) && is_array($_SESSION['pos_cart'])) {
    $response['cart'] = $_SESSION['pos_cart'];
    
    // Calculate totals
    $subtotal = 0;
    $totalDiscount = 0;
    
    foreach ($_SESSION['pos_cart'] as $item) {
        $itemSubtotal = $item['quantity'] * $item['unit_price'];
        $subtotal += $itemSubtotal;
        
        // Calculate discounts
        $itemDiscountAmount = $item['discount_amount'] ?? 0;
        $itemDiscountPercentage = $item['discount_percent'] ?? 0;
        
        // Calculate percentage discount amount
        $percentageDiscountAmount = 0;
        if ($itemDiscountPercentage > 0) {
            $percentageDiscountAmount = $itemSubtotal * ($itemDiscountPercentage / 100);
        }
        
        // Total discount for this item
        $itemTotalDiscount = $itemDiscountAmount + $percentageDiscountAmount;
        $totalDiscount += $itemTotalDiscount;
    }
    
    // Calculate grand total
    $grandTotal = $subtotal - $totalDiscount;
    
    // Set totals in response
    $response['totals'] = [
        'subtotal' => $subtotal,
        'totalDiscount' => $totalDiscount,
        'grandTotal' => $grandTotal
    ];
} else {
    // Initialize empty cart if not set
    $_SESSION['pos_cart'] = [];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
