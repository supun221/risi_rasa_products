<?php


class Employee
{
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Generate Employee ID
    public function generateEmpId() {
        $query = "SELECT emp_id FROM employees ORDER BY emp_id DESC LIMIT 1";
        $result = $this->conn->query($query);

        if ($result && $row = $result->fetch_assoc()) {
            $lastId = (int)substr($row['emp_id'], 3); // Extract numeric part
            $newId = 'emp' . str_pad($lastId + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newId = 'emp001'; // First ID if no records exist
        }
        return $newId;
    }

    // Create a new employee
    public function addEmployee($name, $nic, $telephone, $address) {
        $empId = $this->generateEmpId();
    
        // Save employee details in the database
        $query = "INSERT INTO employees (emp_id, name, nic, telephone, address) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }
    
        $stmt->bind_param("sssss", $empId, $name, $nic, $telephone, $address);
        $stmt->execute();
    
        return $stmt->affected_rows > 0 ? $empId : false; // Return Employee ID for frontend barcode generation
    }

    // Update employee details
    public function updateEmployee($empId, $name, $nic, $telephone, $address) {
        $query = "UPDATE employees SET name = ?, nic = ?, telephone = ?, address = ? WHERE emp_id = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }

        $stmt->bind_param("sssss", $name, $nic, $telephone, $address, $empId);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }

    // Delete an employee by ID
    public function deleteEmployee($empId) {
        $query = "DELETE FROM employees WHERE emp_id = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }

        $stmt->bind_param("s", $empId);
        $stmt->execute();

        return $stmt->affected_rows > 0;
    }

    // Get an employee by ID
    public function getEmployeeById($empId) {
        $query = "SELECT * FROM employees WHERE emp_id = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->conn->error);
        }

        $stmt->bind_param("s", $empId);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result->fetch_assoc(); // Return employee data as an associative array
    }
}
