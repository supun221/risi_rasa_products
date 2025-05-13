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
    // Get route name
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    
    // Validate input
    if (empty($name)) {
        $response['message'] = 'Route name is required';
    } else {
        try {
            // Check if route already exists
            $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM routes WHERE name = ?");
            $checkStmt->bind_param("s", $name);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['count'] > 0) {
                $response['message'] = 'A route with this name already exists';
            } else {
                // Prepare and execute query to add new route
                $stmt = $conn->prepare("INSERT INTO routes (name) VALUES (?)");
                $stmt->bind_param("s", $name);
                $stmt->execute();
                
                if ($stmt->affected_rows > 0) {
                    $response['success'] = true;
                    $response['message'] = 'Route added successfully';
                    $response['route_id'] = $conn->insert_id;
                } else {
                    $response['message'] = 'Failed to add route';
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
