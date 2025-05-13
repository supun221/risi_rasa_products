<?php
// Connect to database
require_once '../../../../config/databade.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'products' => []
];

// Get barcode from request
$barcode = isset($_GET['barcode']) ? trim($_GET['barcode']) : '';

if (empty($barcode)) {
    $response['message'] = 'No barcode provided';
} else {
    try {
        // Get rep_id from session for lorry stock check
        session_start();
        $rep_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

        // Query lorry_stock table first using barcode or itemcode fields
        $query = "
            SELECT 
                ls.id, 
                ls.product_name, 
                ls.barcode, 
                ls.itemcode,
                ls.quantity, 
                ls.unit_price as price,
                ls.status,
                ls.rep_id,
                ls.id as lorry_stock_id
            FROM 
                lorry_stock ls
            WHERE 
                (ls.barcode = ? OR ls.itemcode = ?) 
                AND ls.rep_id = ? 
                AND ls.status = 'active'
                AND ls.quantity > 0
            ORDER BY 
                ls.product_name
        ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $barcode, $barcode, $rep_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $response['products'][] = $row;
            }
            $response['success'] = true;
        } else {
            // If not found in lorry_stock, try the products table as fallback
            $query = "
                SELECT 
                    p.id, 
                    p.product_name, 
                    p.item_code as itemcode,
                    p.barcode,
                    p.quantity, 
                    p.price,
                    'n/a' as status,
                    0 as rep_id,
                    0 as lorry_stock_id
                FROM 
                    products p
                WHERE 
                    p.barcode = ? OR p.item_code = ?
                ORDER BY 
                    p.product_name
            ";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $barcode, $barcode);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $response['products'][] = $row;
                }
                $response['success'] = true;
            } else {
                $response['message'] = 'No products found with the given barcode';
            }
        }
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
}

// Return response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
