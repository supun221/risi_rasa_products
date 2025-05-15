<?php
// Database connection
require_once '../../../config/databade.php';

header('Content-Type: application/json');

try {
    // Query to get all suppliers
    $query = "SELECT DISTINCT supplier_id as id, supplier_id as name FROM stock_entries_raw WHERE supplier_id IS NOT NULL AND supplier_id != '' ORDER BY supplier_id ASC";
    $result = $conn->query($query);
    
    $suppliers = array();
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $suppliers[] = $row;
        }
    }
    
    // Return JSON response
    echo json_encode($suppliers);
} catch (Exception $e) {
    // Error handling - return empty array with no HTML error messages
    echo json_encode([]);
    // Log error instead of displaying it
    error_log("Error fetching suppliers: " . $e->getMessage());
}
?>
