<?php
// Connect to database
require_once '../../../../config/databade.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => ''
];

// Check if form data is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get route ID
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    // Validate input
    if ($id <= 0) {
        $response['message'] = 'Invalid route ID';
    } else {
        try {
            // Check if any customers are using this route
            $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM customers WHERE route_id = ?");
            $checkStmt->bind_param("i", $id);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['count'] > 0) {
                $response['message'] = 'Cannot delete route: There are customers assigned to this route';
            } else {
                // Prepare and execute query to delete route
                $stmt = $conn->prepare("DELETE FROM routes WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                
                if ($stmt->affected_rows > 0) {
                    $response['success'] = true;
                    $response['message'] = 'Route deleted successfully';
                } else {
                    $response['message'] = 'Route not found or could not be deleted';
                }
            }
        } catch (Exception $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    }
} else {
    $response['message'] = 'Invalid request method';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
