<?php
// Connect to database
require_once '../../../../config/databade.php';
session_start();

// Initialize response array
$response = [
    'success' => false,
    'sale' => null,
    'items' => [],
    'message' => ''
];

// Get invoice number from query parameters
$invoice_number = isset($_GET['invoice']) ? $_GET['invoice'] : '';
$rep_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;

if (empty($invoice_number)) {
    $response['message'] = 'Invoice number is required';
} else {
    try {
        // Query to get sale details
        $stmt = $conn->prepare("
            SELECT * FROM pos_sales 
            WHERE invoice_number = ? AND rep_id = ?
        ");
        $stmt->bind_param("si", $invoice_number, $rep_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $response['sale'] = $result->fetch_assoc();
            
            // Get sale items
            $stmt = $conn->prepare("
                SELECT * FROM pos_sale_items 
                WHERE sale_id = ?
            ");
            $stmt->bind_param("i", $response['sale']['id']);
            $stmt->execute();
            $items_result = $stmt->get_result();
            
            while ($item = $items_result->fetch_assoc()) {
                $response['items'][] = $item;
            }
            
            $response['success'] = true;
        } else {
            $response['message'] = 'Sale not found';
        }
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
