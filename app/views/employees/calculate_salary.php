<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../../../config/databade.php'; // Make sure this contains MySQLi connection

    // Get form data
    $emp_id = $_POST['employeeId'] ?? '';
    $month_year = date('Y-m-01');
    $basic_salary = floatval($_POST['basicSalary'] ?? 0);
    $relief_allowance = floatval($_POST['reliefAllowance'] ?? 0);
    $performance_incentive = floatval($_POST['performanceIncentive'] ?? 0);
    $over_time = floatval($_POST['overTime'] ?? 0);
    $over_time_double = floatval($_POST['overTimeDouble'] ?? 0);
    $night_allowance = floatval($_POST['nightAllowance'] ?? 0);
    $arrears = floatval($_POST['arrears'] ?? 0);
    $target_incentive = floatval($_POST['targetIncentive'] ?? 0);
    $attendance_incentive = floatval($_POST['attendanceIncentive'] ?? 0);
    $bonus = floatval($_POST['bonus'] ?? 0);
    $total_addition = floatval($_POST['totalAddition'] ?? 0);
    $provident_fund = floatval($_POST['providentFund'] ?? 0);
    $no_pay_deductions = floatval($_POST['noPayDeductions'] ?? 0);
    $welfare_fee = floatval($_POST['welfareFee'] ?? 0);
    $total_deductions = floatval($_POST['totalDeductions'] ?? 0);

    // Calculate employer contributions
    $employer_pf = ($basic_salary + $relief_allowance) * 0.12;
    $trust_fund = ($basic_salary + $relief_allowance) * 0.03;


    // Calculate net salary
    $net_salary = $total_addition - $total_deductions;

    // MySQLi prepared statement
    $query = "INSERT INTO salaries (
        emp_id, month_year, basic_salary, relief_allowance, performance_incentive,
        over_time, over_time_double, night_allowance, arrears, target_incentive,
        attendance_incentive, bonus, total_addition, provident_fund, no_pay_deductions,
        welfare_fee, total_deductions, employer_pf, trust_fund, net_salary
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
    )";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }

    // Bind parameters (20 parameters: 2 strings, 18 doubles)
    $stmt->bind_param(
        'ssdddddddddddddddddd',
        $emp_id,
        $month_year,
        $basic_salary,
        $relief_allowance,
        $performance_incentive,
        $over_time,
        $over_time_double,
        $night_allowance,
        $arrears,
        $target_incentive,
        $attendance_incentive,
        $bonus,
        $total_addition,
        $provident_fund,
        $no_pay_deductions,
        $welfare_fee,
        $total_deductions,
        $employer_pf,
        $trust_fund,
        $net_salary
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Salary calculated and saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Execute failed: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
