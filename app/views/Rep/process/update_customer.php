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
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $telephone = isset($_POST['telephone']) ? $_POST['telephone'] : '';
    $nic = isset($_POST['nic']) ? $_POST['nic'] : '';
    $address = isset($_POST['address']) ? $_POST['address'] : '';
    $whatsapp = isset($_POST['whatsapp']) ? $_POST['whatsapp'] : '';
    $credit_limit = isset($_POST['credit_limit']) ? $_POST['credit_limit'] : '';
    
    // Validate input
    if ($id <= 0 || empty($name) || empty($telephone) || empty($nic) || empty($address) || empty($whatsapp) || empty($credit_limit)) {
        $response['message'] = 'All fields are required';
    } else {
        try {
            // Prepare and execute query
            $stmt = $conn->prepare("
                UPDATE customers
                SET name = ?, telephone = ?, nic = ?, address = ?, whatsapp = ?, credit_limit = ?
                WHERE id = ?
            ");
            $stmt->bind_param("ssssssi", $name, $telephone, $nic, $address, $whatsapp, $credit_limit, $id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $response['success'] = true;
                $response['message'] = 'Customer updated successfully';
            } else {
                $response['success'] = true; // Still mark as successful if no changes were made
                $response['message'] = 'No changes made to customer';
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
