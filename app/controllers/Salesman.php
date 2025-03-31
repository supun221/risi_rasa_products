<?php

class Salesman
{
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // Check if a salesman with the given ID already exists
    public function salesmanExists($id) {
        $query = "SELECT id FROM salesman WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . mysqli_error($this->conn));
        }
        mysqli_stmt_bind_param($stmt, "s", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $exists = mysqli_num_rows($result) > 0; // Check if rows exist
        mysqli_stmt_close($stmt);
        return $exists;
    }
    
    
    // Create a new salesman
    public function createSalesman( $name, $telephone, $nic, $address) {
        $query = "INSERT INTO salesman ( name, telephone, nic, address) VALUES (?,?,?,?)";
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . mysqli_error($this->conn));
        }
        mysqli_stmt_bind_param($stmt, "ssss", $name, $telephone, $nic, $address);
        $result = mysqli_stmt_execute($stmt);
        if (!$result) {
            throw new Exception("Failed to execute statement: " . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
        return $result;
    }
    
    // Update a salesman's details by ID
    public function updateSalesman($id, $name, $telephone, $nic, $address) {
        $query = "UPDATE salesman SET name =?, telephone =?, nic =?, address =? WHERE id =?";
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . mysqli_error($this->conn));
        }
        mysqli_stmt_bind_param($stmt, "sssss", $name, $telephone, $nic, $address, $id);
        $result = mysqli_stmt_execute($stmt);
        if (!$result) {
            throw new Exception("Failed to execute statement: " . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
        return $result;
    }
    
    // Delete a salesman by ID
    public function deleteSalesman($id) {
        $query = "DELETE FROM salesman WHERE id =?";
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . mysqli_error($this->conn));
        }
        mysqli_stmt_bind_param($stmt, "s", $id);
        $result = mysqli_stmt_execute($stmt);
        if (!$result) {
            throw new Exception("Failed to execute statement: " . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
        return $result;
    }
    
    // Get salesman details by ID
    public function getSalesmanById($id) {
        $query = "SELECT * FROM salesman WHERE id =?";
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . mysqli_error($this->conn));
        }
        mysqli_stmt_bind_param($stmt, "s", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (!$result) {
            throw new Exception("Failed to execute statement: " . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
        return $result->fetch_assoc();
    }
    
    // Get all salesman details
    public function getAllSalesman() {
        $query = "SELECT * FROM salesman";
        $result = mysqli_query($this->conn, $query);
        if (!$result) {
            throw new Exception("Failed to execute query: ". mysqli_error($this->conn));
        }
        return $result;
    }

}