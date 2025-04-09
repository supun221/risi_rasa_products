<?php
// Connect to database
require_once '../../../../config/databade.php';

// Initialize response array
$response = [
    'success' => false,
    'bill_number' => ''
];

try {
    // Generate a unique bill number
    $prefix = 'ADV';
    $date = date('Ymd');
    
    // Get the last used number for today
    $stmt = $conn->prepare("SELECT MAX(advance_bill_number) as max_num FROM advance_payments WHERE advance_bill_number LIKE ?");
    $search_pattern = $prefix . $date . '%';
    $stmt->bind_param("s", $search_pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['max_num']) {
        // Extract the sequence number and increment
        $last_num = intval(substr($row['max_num'], -4));
        $next_num = $last_num + 1;
    } else {
        // No bills yet today, start with 1
        $next_num = 1;
    }
    
    // Format with leading zeros (4 digits)
    $sequence = str_pad($next_num, 4, '0', STR_PAD_LEFT);
    $newBillNumber = $prefix . $date . $sequence;
    
    // Return success
    $response['success'] = true;
    $response['bill_number'] = $newBillNumber;
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log("Error in get_new_advance_bill_number.php: " . $e->getMessage());
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
