<?php
// Connect to database
require_once '../../../../config/databade.php';

// Initialize response array
$response = [
    'success' => false,
    'customers' => [],
    'message' => ''
];

// Check if search term is provided
if (isset($_GET['term']) && !empty($_GET['term'])) {
    $search_term = $_GET['term'];
    
    // Format search term for SQL query
    $search_term = '%' . $search_term . '%';
    
    try {
        // Prepare and execute query for telephone or whatsapp numbers
        $stmt = $conn->prepare("
            SELECT id, name, telephone, whatsapp
            FROM customers 
            WHERE telephone LIKE ? OR whatsapp LIKE ?
            ORDER BY name
            LIMIT 8
        ");
        $stmt->bind_param("ss", $search_term, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch customers
        while ($row = $result->fetch_assoc()) {
            $response['customers'][] = [
                'id' => $row['id'],
                'name' => htmlspecialchars($row['name']),
                'telephone' => htmlspecialchars($row['telephone']),
                'whatsapp' => htmlspecialchars($row['whatsapp']),
            ];
        }
        
        $response['success'] = true;
        
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'No search term provided';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
