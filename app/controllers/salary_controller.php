<?php
require_once '../models/SalaryModel.php';
require_once '../../config/databade.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['action'])) {
    echo json_encode(["error" => "Invalid request"]);
    exit;
}

$salaryModel = new SalaryModel($conn);

if ($data['action'] === 'get_salary_list') {
    $empId = $data['emp_id'] ?? null;
    $startMonth = $data['start_month'] ?? null;
    $endMonth = $data['end_month'] ?? null;

    $salaryList = $salaryModel->getSalaryList($empId, $startMonth, $endMonth);
    echo json_encode($salaryList);
    exit;
}

echo json_encode(["error" => "Invalid action"]);
