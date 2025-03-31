<?php
// Database connection
require_once '../../../config/databade.php';

// Check connection
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}

// Handle POST request (Insert Advance Payment)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate required fields
    if (!isset($data['customer_name'], $data['payment_type'], $data['net_amount'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    // Sanitize and validate input
    $customerName = trim($data['customer_name']);
    $paymentType = trim($data['payment_type']);
    $reason = isset($data['reason']) ? trim($data['reason']) : "No reason provided";
    $netAmount = filter_var($data['net_amount'], FILTER_VALIDATE_FLOAT);
    $printBill = isset($data['print_bill']) ? (int)$data['print_bill'] : 0;

    if ($netAmount === false) {
        echo json_encode(['success' => false, 'message' => 'Invalid net amount']);
        exit;
    }

    try {
        // Step 1: Retrieve the customer ID based on customer name
        $stmt = $conn->prepare("SELECT id FROM customers WHERE name = ? LIMIT 1");
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare customer query: ' . $conn->error]);
            exit;
        }

        $stmt->bind_param("s", $customerName);
        $stmt->execute();
        $customerResult = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // If customer not found, return an error
        if (!$customerResult) {
            echo json_encode(['success' => false, 'message' => 'Customer not found']);
            exit;
        }

        $customerId = $customerResult['id'];

        // Step 2: Generate unique advance bill number
        $likePattern = "AV-%";

        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM advance_payments WHERE advance_bill_number LIKE ?");
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare count statement: ' . $conn->error]);
            exit;
        }

        $stmt->bind_param("s", $likePattern);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $count = isset($result['count']) ? $result['count'] : 0;
        $nextNumber = str_pad($count + 1, 6, '0', STR_PAD_LEFT);
        $advanceBillNumber = "AV-$nextNumber";

        // Step 3: Insert the advance payment into the database
        $stmt = $conn->prepare("
            INSERT INTO advance_payments (advance_bill_number, customer_name, customer_id, payment_type, reason, net_amount, print_bill)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare insert statement: ' . $conn->error]);
            exit;
        }

        // Bind parameters
        $stmt->bind_param("ssissdi", $advanceBillNumber, $customerName, $customerId, $paymentType, $reason, $netAmount, $printBill);

        // Execute the statement
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Advance payment saved successfully', 'bill_number' => $advanceBillNumber]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save data: ' . $stmt->error]);
        }

        // Close the statement
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
    }
} 

// Handle DELETE request (Delete Advance Payment)
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Get the input data
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate required field: 'id'
    if (!isset($data['id']) || !is_numeric($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid or missing ID']);
        exit;
    }

    $id = intval($data['id']); // Sanitize the ID

    try {
        // Prepare the DELETE statement
        $stmt = $conn->prepare("DELETE FROM advance_payments WHERE id = ?");
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare statement: ' . $conn->error]);
            exit;
        }

        // Bind the ID parameter
        $stmt->bind_param("i", $id);

        // Execute the DELETE query
        $stmt->execute();

        // Check if the record was deleted
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Record deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No record found with the given ID']);
        }

        // Close the statement
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
    }
} 

// Invalid Request Method
else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

// Close the database connection
$conn->close();
