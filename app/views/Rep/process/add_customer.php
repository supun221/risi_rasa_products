<?php
// Connect to database
require_once '../../../../config/databade.php';

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'customer_id' => null
];

// Check if form data is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $telephone = isset($_POST['telephone']) ? trim($_POST['telephone']) : '';
    $nic = isset($_POST['nic']) ? trim($_POST['nic']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $whatsapp = isset($_POST['whatsapp']) ? trim($_POST['whatsapp']) : '';
    $credit_limit = isset($_POST['credit_limit']) ? (float)$_POST['credit_limit'] : 0;
    $branch = isset($_POST['branch']) ? trim($_POST['branch']) : 'main';
    $route_id = isset($_POST['route_id']) && !empty($_POST['route_id']) ? (int)$_POST['route_id'] : null;
    
    // Validate input
    if (empty($name) || empty($telephone)) {
        $response['message'] = 'Customer name and telephone are required';
    } else {
        // Check if phone number already exists
        try {
            $checkStmt = $conn->prepare("SELECT id FROM customers WHERE telephone = ?");
            $checkStmt->bind_param("s", $telephone);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                $response['message'] = 'A customer with this phone number already exists';
            } else {
                try {
                    // Prepare and execute query (with route_id)
                    if ($route_id !== null) {
                        $stmt = $conn->prepare("
                            INSERT INTO customers (name, telephone, nic, address, whatsapp, credit_limit, branch, route_id)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->bind_param("sssssssi", $name, $telephone, $nic, $address, $whatsapp, $credit_limit, $branch, $route_id);
                    } else {
                        $stmt = $conn->prepare("
                            INSERT INTO customers (name, telephone, nic, address, whatsapp, credit_limit, branch)
                            VALUES (?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->bind_param("sssssss", $name, $telephone, $nic, $address, $whatsapp, $credit_limit, $branch);
                    }
                    
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
        } catch (Exception $e) {
            $response['message'] = 'Database error checking for duplicate: ' . $e->getMessage();
        }
    }
} else {
    $response['message'] = 'Invalid request method';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
