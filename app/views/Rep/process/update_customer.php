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
    $route_id = isset($_POST['route_id']) && !empty($_POST['route_id']) ? (int)$_POST['route_id'] : null;
    
    // Log the received data for debugging
    error_log("Update customer - POST data: " . json_encode($_POST));
    error_log("Update customer - Processed route_id: " . var_export($route_id, true));
    
    // Validate input
    if (empty($name) || empty($telephone) || empty($nic) || empty($address)) {
        $response['message'] = 'Required fields are missing';
    } else if ($id <= 0) {
        $response['message'] = 'Invalid customer ID';
    } else {
        try {
            // Prepare and execute query with or without route_id
            if ($route_id !== null) {
                $stmt = $conn->prepare("
                    UPDATE customers 
                    SET name = ?, telephone = ?, nic = ?, address = ?, whatsapp = ?, credit_limit = ?, route_id = ?
                    WHERE id = ?
                ");
                $stmt->bind_param("ssssssii", $name, $telephone, $nic, $address, $whatsapp, $credit_limit, $route_id, $id);
                error_log("Update SQL with route_id: $route_id");
            } else {
                $stmt = $conn->prepare("
                    UPDATE customers 
                    SET name = ?, telephone = ?, nic = ?, address = ?, whatsapp = ?, credit_limit = ?, route_id = NULL
                    WHERE id = ?
                ");
                $stmt->bind_param("ssssssi", $name, $telephone, $nic, $address, $whatsapp, $credit_limit, $id);
                error_log("Update SQL with NULL route_id");
            }
            
            $stmt->execute();
            
            if ($stmt->affected_rows >= 0) { // Note: affected_rows could be 0 if no changes were made
                $response['success'] = true;
                $response['message'] = 'Customer updated successfully';
                
                // Verify the update
                $verifyStmt = $conn->prepare("SELECT route_id FROM customers WHERE id = ?");
                $verifyStmt->bind_param("i", $id);
                $verifyStmt->execute();
                $verifyResult = $verifyStmt->get_result();
                $verifyRow = $verifyResult->fetch_assoc();
                error_log("Verification - Saved route_id: " . var_export($verifyRow['route_id'], true));
            } else {
                $response['message'] = 'Failed to update customer: ' . $conn->error;
            }
        } catch (Exception $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
            error_log("Update customer error: " . $e->getMessage());
        }
    }
} else {
    $response['message'] = 'Invalid request method';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
