<?php
class SalaryModel
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getSalaryList($empId = null, $startMonth = null, $endMonth = null)
    {
        $query = "SELECT emp_id, DATE_FORMAT(month_year, '%Y-%m') AS month_year, 
                         basic_salary, total_addition, total_deductions,over_time ,provident_fund, net_salary,employer_pf,trust_fund 
                  FROM salaries WHERE 1=1";

        $params = [];
        $types = '';

        if (!empty($empId)) {
            $query .= " AND emp_id = ?";
            $params[] = $empId;
            $types .= 's';
        }

        if (!empty($startMonth)) {
            $query .= " AND month_year >= ?";
            $params[] = $startMonth . "-01";
            $types .= 's';
        }

        if (!empty($endMonth)) {
            $query .= " AND month_year <= ?";
            $params[] = $endMonth . "-31";
            $types .= 's';
        }

        $stmt = $this->conn->prepare($query);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $salaryList = [];
        while ($row = $result->fetch_assoc()) {
            $salaryList[] = $row;
        }

        $stmt->close();
        return $salaryList;
    }
}
