<?php
// Connect to database
require_once '../../../../config/databade.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'routes' => []
];

try {
    // Fetch all routes
    $query = "SELECT id, name FROM routes ORDER BY name";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        throw new Exception(mysqli_error($conn));
    }
    
    $routes = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $routes[] = [
            'id' => $row['id'],
            'name' => $row['name']
        ];
    }
    
    $response['success'] = true;
    $response['routes'] = $routes;
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
