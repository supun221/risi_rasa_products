<?php
require_once '../../../config/databade.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplierId = $_POST['supplierId'] ?? null;
    $supplierName = $_POST['supplierName'];
    $telephoneNo = $_POST['telephoneNo'];
    $company = $_POST['company'];

    if (empty($supplierName) || empty($telephoneNo) || empty($company)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    if ($supplierId) {
        // Update supplier
        $query = "UPDATE suppliers SET supplier_name = '$supplierName', telephone_no = '$telephoneNo', company = '$company' WHERE id = $supplierId";
        $action = "updated";
    } else {
        // Add supplier
        $query = "INSERT INTO suppliers (supplier_name, telephone_no, company) VALUES ('$supplierName', '$telephoneNo', '$company')";
        $action = "added";
    }

    if (mysqli_query($conn, $query)) {
        echo json_encode(['status' => 'success', 'message' => "Supplier $action successfully."]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
