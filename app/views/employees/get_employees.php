<?php
require_once '../../../config/databade.php'; // Correct the filename typo

header('Content-Type: application/json');

if (isset($_GET['emp_id'])) {
    $emp_id = $_GET['emp_id'];
    
    // Fetch employee details
    $stmt = mysqli_prepare($conn, "SELECT emp_id, name FROM employees WHERE emp_id = ?");
    mysqli_stmt_bind_param($stmt, "s", $emp_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    
    if (!$result) {
        echo json_encode(['error' => 'Employee not found']);
        exit;
    }
    
    // Calculate total overtime hours for current month
    $stmt_att = mysqli_prepare($conn, "SELECT SUM(total_hours - 9) AS overtime FROM attendance WHERE emp_id = ? AND total_hours > 9 AND MONTH(date) = MONTH(CURRENT_DATE()) AND YEAR(date) = YEAR(CURRENT_DATE())");
    mysqli_stmt_bind_param($stmt_att, "s", $emp_id);
    mysqli_stmt_execute($stmt_att);
    $att_result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_att));
    
    $result['overtime_hours'] = $att_result['overtime'] ?? 0;
    
    echo json_encode($result);
    exit;
} else {
    echo json_encode(['error' => 'No employee ID provided']);
    exit;
}
?>