<?php
require_once '../../../../config/databade.php';
session_start();

// Get the search term
$name = isset($_GET['name']) ? $_GET['name'] : '';
$response = ['success' => false, 'customers' => []];

if (!empty($name)) {
    // Prepare the query to search by name
    $stmt = $conn->prepare("SELECT id, name, telephone, address, nic FROM customers WHERE name LIKE ? LIMIT 15");
    $searchTerm = '%' . $name . '%';
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $customers = [];
    while ($row = $result->fetch_assoc()) {
        $customers[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'telephone' => $row['telephone'],
            'address' => $row['address'],
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
