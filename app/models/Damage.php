<?php

class Damage
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    //damage adds

    public function addDamage($product_name, $damage_description, $damage_quantity, $damage_price, $barcode) {
        // Start a transaction
        mysqli_begin_transaction($this->conn);
    
        try {
            // Insert into damages table
            $query = "INSERT INTO damages (product_name, damage_description, damage_quantity, price, barcode, date) 
                      VALUES (?, ?, ?, ?, ?, DEFAULT)";
            $stmt = mysqli_prepare($this->conn, $query);
            if (!$stmt) {
                throw new Exception("Failed to prepare statement: " . mysqli_error($this->conn));
            }
            mysqli_stmt_bind_param($stmt, "sssss", $product_name, $damage_description, $damage_quantity, $damage_price, $barcode);
            $result = mysqli_stmt_execute($stmt);
            if (!$result) {
                throw new Exception("Failed to execute statement: " . mysqli_stmt_error($stmt));
            }
            mysqli_stmt_close($stmt);
    
            // Update stock_entries table
            $updateQuery = "UPDATE stock_entries SET available_stock = available_stock - ? WHERE barcode = ?";
            $updateStmt = mysqli_prepare($this->conn, $updateQuery);
            if (!$updateStmt) {
                throw new Exception("Failed to prepare statement: " . mysqli_error($this->conn));
            }
            mysqli_stmt_bind_param($updateStmt, "is", $damage_quantity, $barcode);
            $updateResult = mysqli_stmt_execute($updateStmt);
            if (!$updateResult) {
                throw new Exception("Failed to execute update statement: " . mysqli_stmt_error($updateStmt));
            }
            mysqli_stmt_close($updateStmt);
    
            // Commit transaction
            mysqli_commit($this->conn);
            return true;
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($this->conn);
            throw $e;
        }
    }
    
    // damage gets
    
    public function getDamages(){
        $query = "SELECT * FROM damages";
        $result = mysqli_query($this->conn, $query);
        if (!$result) {
            throw new Exception("Failed to execute query: ".mysqli_error($this->conn));
        }
        return $result;
    }
    //damage gets by id
    
    public function getDamageById($id){
        $query = "SELECT * FROM damages WHERE id=?";
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: ".mysqli_error($this->conn));
        }
        mysqli_stmt_bind_param($stmt, "i", $id);
        $result = mysqli_stmt_execute($stmt);
        if (!$result) {
            throw new Exception("Failed to execute statement: ".mysqli_stmt_error($stmt));
        }
        $row = mysqli_stmt_get_result($stmt)->fetch_assoc();
        mysqli_stmt_close($stmt);
        return $row;
    }

    //damage update
    
    public function updateDamage($id, $product_name, $damage_description, $new_damage_quantity, $damage_price, $barcode)
    {
        // Start a transaction
        mysqli_begin_transaction($this->conn);
    
        try {
            // Fetch the current damage quantity for the specified damage ID
            $query = "SELECT damage_quantity FROM damages WHERE id = ?";
            $stmt = mysqli_prepare($this->conn, $query);
            if (!$stmt) {
                throw new Exception("Failed to prepare statement: " . mysqli_error($this->conn));
            }
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $current_damage = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
    
            if (!$current_damage) {
                throw new Exception("Damage record not found for the given ID.");
            }
    
            $current_damage_quantity = $current_damage['damage_quantity'];
    
            // Update the damages table
            $query = "UPDATE damages SET product_name = ?, damage_description = ?, damage_quantity = ?, price = ? WHERE id = ?";
            $stmt = mysqli_prepare($this->conn, $query);
            if (!$stmt) {
                throw new Exception("Failed to prepare update statement: " . mysqli_error($this->conn));
            }
            mysqli_stmt_bind_param($stmt, "sssii", $product_name, $damage_description, $new_damage_quantity, $damage_price, $id);
            $result = mysqli_stmt_execute($stmt);
            if (!$result) {
                throw new Exception("Failed to execute update statement: " . mysqli_stmt_error($stmt));
            }
            mysqli_stmt_close($stmt);
    
            // Calculate the difference in quantity
            $quantity_difference = $new_damage_quantity - $current_damage_quantity;
    
            // Update the stock_entries table
            $updateQuery = "UPDATE stock_entries SET available_stock = available_stock - ? WHERE barcode = ?";
            $updateStmt = mysqli_prepare($this->conn, $updateQuery);
            if (!$updateStmt) {
                throw new Exception("Failed to prepare stock update statement: " . mysqli_error($this->conn));
            }
            mysqli_stmt_bind_param($updateStmt, "is", $quantity_difference, $barcode);
            $updateResult = mysqli_stmt_execute($updateStmt);
            if (!$updateResult) {
                throw new Exception("Failed to execute stock update statement: " . mysqli_stmt_error($updateStmt));
            }
            mysqli_stmt_close($updateStmt);
    
            // Commit transaction
            mysqli_commit($this->conn);
            return true;
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($this->conn);
            throw $e;
        }
    }
    
    // damage delete
    
    public function deleteDamage($id){
        $query = "DELETE FROM damages WHERE id=?";
        $stmt = mysqli_prepare($this->conn,$query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: ".mysqli_error($this->conn));
        }
        mysqli_stmt_bind_param($stmt, "i", $id);
        $result = mysqli_stmt_execute($stmt);
        if (!$result) {
            throw new Exception("Failed to execute statement: ".mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
        return $result;
    }
    //damge get by barcode

    public function getitemseByBarcode($barcode)
    {
        // $barcode = "4798578861302"; //
        $query = "SELECT product_name, cost_price, available_stock FROM stock_entries WHERE barcode = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . mysqli_error($this->conn));
        }
        mysqli_stmt_bind_param($stmt, 's', $barcode);
        mysqli_stmt_execute($stmt);
    
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    
        if ($row) {
            return $row; // Return product_name, price, and quantity
        } else {
            throw new Exception("Product not found for the provided barcode.");
        }
    }
    
}