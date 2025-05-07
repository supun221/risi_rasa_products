<?php
require_once '../../../../config/databade.php';
session_start();

// Get the search term
$term = isset($_GET['term']) ? $_GET['term'] : '';
$response = ['success' => false, 'customers' => []];

if (strlen($term) >= 2) {
    // Prepare the query with LIKE for partial matches on name
    $stmt = $conn->prepare("SELECT id, name, telephone, nic FROM customers WHERE name LIKE ? LIMIT 10");
    $searchTerm = '%' . $term . '%';
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'telephone' => $row['telephone'],
            'nic' => $row['nic']
        ];
    }
    
    if (count($customers) > 0) {
        $response = ['success' => true, 'customers' => $customers];
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
