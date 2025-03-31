<?php

class Supplier
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Add a new supplier
    public function addSupplier($supplier_name, $telephone, $company, $credit_balance, $area_manager, $agent_details, $ref_details)
    {
        // Get the max supplier_id from the database
        $result = $this->conn->query("SELECT MAX(supplier_id) AS max_id FROM supplier");
        if (!$result) {
            throw new Exception("Failed to fetch max supplier_id: " . mysqli_error($this->conn));
        }
        $row = $result->fetch_assoc();
        $next_supplier_id = $row['max_id'] ? $row['max_id'] + 1 : 1; // If no records, start with 1

        $query = "INSERT INTO supplier (supplier_id, supplier_name, telephone_no, company, credit_balance, Area_manager, Agent_details, Ref_details) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . mysqli_error($this->conn));
        }

        mysqli_stmt_bind_param($stmt, "isssdsss", $next_supplier_id, $supplier_name, $telephone, $company, $credit_balance, $area_manager, $agent_details, $ref_details);

        $result = mysqli_stmt_execute($stmt);
        if (!$result) {
            throw new Exception("Failed to execute statement: " . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
        return $result;
    }
    public function searchSupplier($searchQuery)
    {
        // Correct table name: 'supplier'
        $query = "SELECT * FROM suppliers WHERE supplier_name LIKE ? OR supplier_id LIKE ?";
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . mysqli_error($this->conn));
        }

        // Add wildcards for partial matching
        $likeQuery = "%" . $searchQuery . "%";
        mysqli_stmt_bind_param($stmt, "ss", $likeQuery, $likeQuery);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to execute statement: " . mysqli_stmt_error($stmt));
        }

        $result = mysqli_stmt_get_result($stmt);
        $suppliers = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $suppliers[] = $row;
        }
        mysqli_stmt_close($stmt);

        // Debugging: Check if results are empty
        if (empty($suppliers)) {
            error_log("Search returned no results for query: $searchQuery");
        }

        return $suppliers;
    }


    // Get supplier data by ID
    public function getSupplierById($id)
    {
        $query = "SELECT * FROM suppliers WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . mysqli_error($this->conn));
        }
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }

    // Update supplier data
    public function updateSupplier($supplier_id, $supplier_name, $telephone_no, $company, $credit_balance, $area_manager, $agent_details, $ref_details)
    {
        $query = "UPDATE suppliers 
                  SET supplier_name = ?, telephone_no = ?, company = ?, credit_balance = ?, Area_manager = ?, Agent_details = ?, Ref_details = ? 
                  WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . mysqli_error($this->conn));
        }

        mysqli_stmt_bind_param($stmt, "sssdsdsi", $supplier_name, $telephone_no, $company, $credit_balance, $area_manager, $agent_details, $ref_details, $supplier_id);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Failed to execute statement: " . mysqli_stmt_error($stmt));
        }

        // Log the number of affected rows
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        error_log("Affected Rows: $affected_rows");

        mysqli_stmt_close($stmt);

        // Return true if rows were updated
        return $affected_rows > 0;
    }

    // Delete a supplier by ID
    public function deleteSupplier($supplier_id)
    {
        $query = "DELETE FROM suppliers WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . mysqli_error($this->conn));
        }
        mysqli_stmt_bind_param($stmt, "i", $supplier_id);
        $result = mysqli_stmt_execute($stmt);
        if (!$result) {
            throw new Exception("Failed to execute statement: " . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
        return $result;
    }
}
