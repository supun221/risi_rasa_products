<?php
// Connect to database
require_once '../../../../config/databade.php';
session_start();

// Initialize response array
$response = [
    'success' => false,
    'item' => null,
    'message' => ''
];

// Get stock_id from query parameters
$stock_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$rep_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;

if ($stock_id <= 0) {
    $response['message'] = 'Invalid stock ID';
} else {
    try {
        // Query to get stock item details
        $stmt = $conn->prepare("
            SELECT ls.*, se.itemcode, se.available_stock as parent_stock
            FROM lorry_stock ls
            LEFT JOIN stock_entries se ON ls.stock_entry_id = se.id
            WHERE ls.id = ? AND ls.rep_id = ?
        ");
        $stmt->bind_param("ii", $stock_id, $rep_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $response['item'] = $result->fetch_assoc();
            $response['success'] = true;
        } else {
            $response['message'] = 'Stock item not found';
        }
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
