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
    // Get form data
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $telephone = isset($_POST['telephone']) ? $_POST['telephone'] : '';
    $nic = isset($_POST['nic']) ? $_POST['nic'] : '';
    $address = isset($_POST['address']) ? $_POST['address'] : '';
    $whatsapp = isset($_POST['whatsapp']) ? $_POST['whatsapp'] : '';
    $credit_limit = isset($_POST['credit_limit']) ? $_POST['credit_limit'] : '';
    $branch = isset($_POST['branch']) ? $_POST['branch'] : 'main';
    
    // Validate input
    if (empty($name) || empty($telephone) || empty($nic) || empty($address) || empty($whatsapp) || empty($credit_limit)) {
        $response['message'] = 'All fields are required';
    } else {
        try {
            // Prepare and execute query
            $stmt = $conn->prepare("
                INSERT INTO customers (name, telephone, nic, address, whatsapp, credit_limit, branch)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("sssssss", $name, $telephone, $nic, $address, $whatsapp, $credit_limit, $branch);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $response['success'] = true;
                $response['message'] = 'Customer added successfully';
                $response['customer_id'] = $conn->insert_id;
            } else {
                $response['message'] = 'Failed to add customer';
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
