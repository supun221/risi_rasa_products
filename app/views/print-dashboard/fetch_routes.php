<?php
// Connect to database
require_once '../../../config/databade.php';

// Initialize response
$routes = [];

try {
    // Query to fetch all routes
    $query = "SELECT id, name FROM routes ORDER BY name";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $routes[] = [
                'id' => $row['id'],
                'name' => $row['name']
            ];
        }
    }
    
    // Return routes as JSON
    header('Content-Type: application/json');
    echo json_encode($routes);
    
} catch (Exception $e) {
    // Return error
    header('Content-Type: application/json');
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>
