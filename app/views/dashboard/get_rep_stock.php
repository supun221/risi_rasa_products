<?php
// Include database configuration
require_once '../../../config/databade.php'; // Fix to database.php in your setup

// Set headers to return JSON
header('Content-Type: application/json');

// Check if rep_id is provided
if (!isset($_GET['rep_id']) || empty($_GET['rep_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Rep ID is required'
    ]);
    exit;
}

$rep_id = intval($_GET['rep_id']);

try {
    // Get rep information using correct column names
    $rep_sql = "SELECT username as rep_name FROM signup WHERE id = ? AND job_role = 'rep'";
    $stmt = mysqli_prepare($conn, $rep_sql);
    mysqli_stmt_bind_param($stmt, 'i', $rep_id);
    mysqli_stmt_execute($stmt);
    $rep_result = mysqli_stmt_get_result($stmt);
    
    if ($rep_result && $rep_data = mysqli_fetch_assoc($rep_result)) {
        $rep_name = $rep_data['rep_name'];
    } else {
        throw new Exception("Representative not found");
    }
    
    // Get all active stock items for the rep
    $stock_sql = "SELECT 
                    id, 
                    product_name, 
                    itemcode, 
                    barcode, 
                    quantity, 
                    unit_price, 
                    total_amount, 
                    status,
                    date_added 
                  FROM lorry_stock 
                  WHERE rep_id = ? AND status = 'active' 
                  ORDER BY product_name";
                  
    $stmt = mysqli_prepare($conn, $stock_sql);
    mysqli_stmt_bind_param($stmt, 'i', $rep_id);
    mysqli_stmt_execute($stmt);
    $stock_result = mysqli_stmt_get_result($stmt);
    
    // Fetch all items
    $items = [];
    $total_quantity = 0;
    $calculated_grand_total_amount = 0; // Renamed for clarity during calculation
    
    while ($row = mysqli_fetch_assoc($stock_result)) {
        $item_calculated_total = 0;
        if (isset($row['quantity'], $row['unit_price']) && is_numeric($row['quantity']) && is_numeric($row['unit_price'])) {
            $item_calculated_total = $row['quantity'] * $row['unit_price'];
        }
        $row['total_amount'] = $item_calculated_total; // Overwrite total_amount with calculated value

        $items[] = $row;
        $total_quantity += (is_numeric($row['quantity']) ? $row['quantity'] : 0);
        $calculated_grand_total_amount += $row['total_amount'];
    }
    
    // Return the data as JSON
    echo json_encode([
        'status' => 'success',
        'rep_name' => $rep_name,
        'items' => $items,
        'summary' => [
            'total_quantity' => $total_quantity,
            'total_amount' => $calculated_grand_total_amount // Use the sum of calculated item totals
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>
