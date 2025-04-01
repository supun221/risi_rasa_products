<?php
require_once '../../../config/databade.php';

header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    error_log("Received Supplier ID: $id"); // Log the ID
    $query = "SELECT id, supplier_name, telephone_no, company FROM suppliers WHERE id = $id";
    $result = mysqli_query($conn, $query);

    if ($result) {
        $supplier = mysqli_fetch_assoc($result);
        echo json_encode($supplier);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Supplier not found.']);
    }
} else {
    // Get all suppliers without pagination
    $query = "SELECT id, supplier_name, telephone_no, company, credit_balance FROM suppliers";
    $result = mysqli_query($conn, $query);

    $suppliers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $suppliers[] = $row;
    }

    echo json_encode(['suppliers' => $suppliers]);
}
?>
