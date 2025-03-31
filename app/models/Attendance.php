<?php
class Attendance
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function recordAttendance($empId)
    {
        date_default_timezone_set('Asia/Colombo');
        $currentDateTime = date('Y-m-d H:i:s');
        $currentDate = date('Y-m-d');

        $query = "SELECT * FROM attendance WHERE emp_id = ? AND date = ? ORDER BY in_time DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $empId, $currentDate);
        $stmt->execute();
        $result = $stmt->get_result();
        $latest = $result->fetch_assoc();

        if ($latest && !$latest['out_time']) {
            $updateQuery = "UPDATE attendance SET out_time = ?, total_hours = TIMESTAMPDIFF(HOUR, in_time, ?) WHERE attendance_id = ?";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bind_param("ssi", $currentDateTime, $currentDateTime, $latest['attendance_id']);
            $updateStmt->execute();
            return ['success' => $updateStmt->affected_rows > 0, 'message' => $updateStmt->affected_rows > 0 ? 'Out time recorded successfully!' : 'Failed to record out time'];
        } else {
            $insertQuery = "INSERT INTO attendance (emp_id, date, in_time) VALUES (?, ?, ?)";
            $insertStmt = $this->conn->prepare($insertQuery);
            $insertStmt->bind_param("sss", $empId, $currentDate, $currentDateTime);
            $insertStmt->execute();
            return ['success' => $insertStmt->affected_rows > 0, 'message' => $insertStmt->affected_rows > 0 ? 'In time recorded successfully!' : 'Failed to record in time'];
        }
    }

    public function getAttendance($empId = null, $startDate = null, $endDate = null, $month = null)
    {
        $query = "SELECT a.*, e.name 
                  FROM attendance a
                  JOIN employees e ON a.emp_id = e.emp_id
                  WHERE 1=1";

        $params = [];
        $types = '';

        if ($empId) {
            $query .= " AND a.emp_id = ?";
            $params[] = $empId;
            $types .= 's';
        }
        if ($startDate && $endDate) {
            $query .= " AND a.date BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
            $types .= 'ss';
        }
        if ($month) {
            $query .= " AND DATE_FORMAT(a.date, '%Y-%m') = ?";
            $params[] = $month;
            $types .= 's';
        }

        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $attendance = $result->fetch_all(MYSQLI_ASSOC);

        // Properly separate attendance records for each employee
        $formattedAttendance = [];
        foreach ($attendance as $record) {
            $formattedAttendance[] = [
                'emp_id' => $record['emp_id'],
                'name' => $record['name'],
                'date' => $record['date'],
                'in_time' => $record['in_time'],
                'out_time' => $record['out_time'] ?? 'N/A',
                'total_hours' => $record['total_hours'] ?? 0,
            ];
        }

        return $formattedAttendance;
    }
}
