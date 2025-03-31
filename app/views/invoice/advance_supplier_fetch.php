<?php
// Database connection
require_once '../../../config/databade.php';

// Check connection
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}

// Handle API requests
$requestMethod = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

if ($requestMethod === 'POST') {
    handlePostRequest($conn, $data);
} elseif ($requestMethod === 'DELETE') {
    handleDeleteRequest($conn, $data);
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();

/**
 * Handle POST request to save advance payments
 */
function handlePostRequest($conn, $data) {
    if (!isset($data['supplier_name'], $data['payment_type'], $data['net_amount'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }

    $supplierName = trim($data['supplier_name']);
    $paymentType = trim($data['payment_type']);
    $reason = isset($data['reason']) ? trim($data['reason']) : "No reason provided";
    $netAmount = filter_var($data['net_amount'], FILTER_VALIDATE_FLOAT);
    $printBill = isset($data['print_bill']) ? (int)$data['print_bill'] : 0;

    if ($netAmount === false) {
        echo json_encode(['success' => false, 'message' => 'Invalid net amount']);
        return;
    }

    try {
        // Generate unique advance bill number
        $advanceBillNumber = generateBillNumber($conn);

        // Insert the advance payment into the database
        $stmt = $conn->prepare(
            "INSERT INTO advance_payments_supplier (advance_bill_number, supplier_name, payment_type, reason, net_amount, print_bill) 
            VALUES (?, ?, ?, ?, ?, ?)"
        );

        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Failed to prepare statement: ' . $conn->error]);
            return;
        }

        $stmt->bind_param("ssssdi", $advanceBillNumber, $supplierName, $paymentType, $reason, $netAmount, $printBill);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Advance payment saved successfully', 'bill_number' => $advanceBillNumber]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save data: ' . $stmt->error]);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
    }
}

/**
 * Handle DELETE request to delete a record
 */
function handleDeleteRequest($conn, $data) {
    if (!isset($data['id']) || !is_numeric($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid or missing ID']);
        return;
    }

    $id = intval($data['id']);

    $stmt = $conn->prepare("DELETE FROM advance_payments_supplier WHERE id = ?");

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare statement: ' . $conn->error]);
        return;
    }

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Record deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete record: ' . $stmt->error]);
    }

    $stmt->close();
}

/**
 * Generate a unique advance bill number
 */
function generateBillNumber($conn) {
    $likePattern = "SAV-%";

    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM advance_payments_supplier WHERE advance_bill_number LIKE ?");

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Failed to prepare statement: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("s", $likePattern);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $count = isset($result['count']) ? $result['count'] : 0;
    $nextNumber = str_pad($count + 1, 6, '0', STR_PAD_LEFT);

    return "SAV-$nextNumber";
}
