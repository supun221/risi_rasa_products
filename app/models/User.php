<?php

class User {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

   // Check if a username already exists
public function usernameExists($username) {
    $query = "SELECT id FROM signup WHERE username = ?";
    $stmt = mysqli_prepare($this->conn, $query);
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . mysqli_error($this->conn));
    }
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return $result && mysqli_num_rows($result) > 0;
}


public function createUser($store, $username, $email, $password, $telephone , $role) {
    $query = "INSERT INTO signup (store, username, Email, password, telephone, job_role) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($this->conn, $query);
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . mysqli_error($this->conn));
    }
    mysqli_stmt_bind_param($stmt, "ssssss", $store, $username, $email, $password, $telephone, $role);
    $result = mysqli_stmt_execute($stmt);
    if (!$result) {
        throw new Exception("Failed to execute statement: " . mysqli_stmt_error($stmt));
    }
    mysqli_stmt_close($stmt);
    return $result;
}
public function getUserByUsernameAndStore($username, $store) {
    $query = "SELECT * FROM signup WHERE username = ? AND store = ?";
    $stmt = mysqli_prepare($this->conn, $query);
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . mysqli_error($this->conn));
    }
    mysqli_stmt_bind_param($stmt, "ss", $username, $store);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}



    // Get user data by username
    public function getUserByUsername($username) {
        $query = "SELECT * FROM signup WHERE username = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . mysqli_error($this->conn));
        }
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }
    //delete user

    public function deleteUser($id) {
        $query = "DELETE FROM signup WHERE id =?";
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: ". mysqli_error($this->conn));
        }
        mysqli_stmt_bind_param($stmt, "i", $id);
        $result = mysqli_stmt_execute($stmt);
        if (!$result) {
            throw new Exception("Failed to execute statement: ". mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
        return $result;
    }

}
?>
