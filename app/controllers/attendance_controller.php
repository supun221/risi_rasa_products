<?php
require_once '../../config/databade.php';
require_once '../models/Employee.php';
require_once '../models/Attendance.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $data['action'] ?? null;

    if ($action === 'record') {
        $empId = $data['emp_id'];
        $employee = new Employee($conn);
        $empData = $employee->getEmployeeById($empId);

        if (!$empData) {
            echo json_encode(['success' => false, 'message' => 'Employee not found']);
            exit;
        }

        $attendance = new Attendance($conn);
        $result = $attendance->recordAttendance($empId);

        echo json_encode($result);
        exit;
    } elseif ($action === 'get_attendance') {
        $empId = $data['emp_id'] ?? null;
        $startDate = $data['start_date'] ?? null;
        $endDate = $data['end_date'] ?? null;
        $month = $data['month'] ?? null;

        $attendance = new Attendance($conn);
        $result = $attendance->getAttendance($empId, $startDate, $endDate, $month);

        echo json_encode($result);
        exit;
    }
}
