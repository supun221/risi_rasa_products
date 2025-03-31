<?php
require_once '../../../config/databade.php';

header('Content-Type: application/json');

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 5; // Number of records per page
$offset = ($page - 1) * $limit;

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
    // Paginated query
    $query = "SELECT id, supplier_name, telephone_no, company, credit_balance FROM suppliers LIMIT $limit OFFSET $offset";
    $result = mysqli_query($conn, $query);

    $suppliers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $suppliers[] = $row;
    }

    // Total record count
    $countQuery = "SELECT COUNT(*) as total FROM suppliers";
    $countResult = mysqli_query($conn, $countQuery);
    $totalRows = mysqli_fetch_assoc($countResult)['total'];
    $totalPages = ceil($totalRows / $limit);

    echo json_encode(['suppliers' => $suppliers, 'totalPages' => $totalPages]);
}
?>
