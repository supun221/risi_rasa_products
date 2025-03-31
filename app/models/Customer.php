<?php
class Customer
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    // Check if a customer with the given name already exists
    public function customerExists($name)
    {
        $query = "SELECT id FROM customers WHERE name = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . mysqli_error($this->conn));
        }
        mysqli_stmt_bind_param($stmt, "s", $name);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return $result && mysqli_num_rows($result) > 0;
    }
    public function searchCustomers($query)
    {
        $query = "%" . $query . "%";
        $sql = "SELECT id, name FROM customers WHERE name LIKE ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . mysqli_error($this->conn));
        }
        mysqli_stmt_bind_param($stmt, "s", $query);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $customers = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $customers[] = $row;
        }
        return $customers;
    }

    // Add a new customer
    public function addCustomer($name, $telephone, $nic, $address, $whatsapp, $email, $birthday, $credit_limit, $discount, $price_type)
    {
        $query = "INSERT INTO customers (name, telephone, nic, address, whatsapp, email, birthday, credit_limit, discount, price_type) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . mysqli_error($this->conn));
        }
        mysqli_stmt_bind_param($stmt, "ssssssssss", $name, $telephone, $nic, $address, $whatsapp, $email, $birthday, $credit_limit, $discount, $price_type);
        $result = mysqli_stmt_execute($stmt);
        if (!$result) {
            throw new Exception("Failed to execute statement: " . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
        return $result;
    }

    // Get customer data by name
    public function getCustomerByName($name)
    {
        $query = "SELECT * FROM customers WHERE name = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . mysqli_error($this->conn));
        }
        mysqli_stmt_bind_param($stmt, "s", $name);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }
    // In Customer class
    public function getCustomerCreditInfo($id)
    {
        // Get customer's credit limit
        $query = "SELECT credit_limit FROM customers WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $customer = mysqli_fetch_assoc($result);

        if (!$customer) return null;

        // Get total credit balance from bill_records
        $creditQuery = "SELECT SUM(gross_amount) AS total_credit 
                    FROM bill_records 
                    WHERE customer_id = ? AND payment_type = 'credit_payment'";
        $creditStmt = mysqli_prepare($this->conn, $creditQuery);
        mysqli_stmt_bind_param($creditStmt, "i", $id);
        mysqli_stmt_execute($creditStmt);
        $creditResult = mysqli_stmt_get_result($creditStmt);
        $creditData = mysqli_fetch_assoc($creditResult);

        return [
            'credit_limit' => $customer['credit_limit'],
            'credit_balance' => $creditData['total_credit'] ?? 0
        ];
    }

    // Get customer data by phone
    public function getCustomerByPhone($phone_number)
    {
        $query = "%" . $phone_number . "%";
        $sql = "SELECT id, name FROM customers WHERE telephone LIKE ?";
        $stmt = mysqli_prepare($this->conn, $sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . mysqli_error($this->conn));
        }
        mysqli_stmt_bind_param($stmt, "s", $query);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $customers = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $customers[] = $row;
        }
        return $customers;
    }

    // Get all customers
    function getAllCustomer()
    {
        $query = "SELECT * FROM customers";
        $result = mysqli_query($this->conn, $query);
        if (!$result) {
            throw new Exception("Failed to execute query " . mysqli_error($this->conn));
        }
        $customers = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $customers[] = $row;
        }
        return $customers;
    }

    // Get customer data by ID
    public function getCustomerById($id)
    {
        $query = "SELECT * FROM customers WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . mysqli_error($this->conn));
        }
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        return mysqli_fetch_assoc($result);
    }

    // Update customer data by ID
    public function updateCustomer($id, $name, $telephone, $nic, $address, $whatsapp, $email, $birthday, $credit_limit, $discount, $price_type)
    {
        $query = "UPDATE customers SET name = ?, telephone = ?, nic = ?, address = ?, whatsapp = ?, email = ?, birthday = ?, credit_limit = ?, discount = ?, price_type = ? 
                  WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . mysqli_error($this->conn));
        }
        mysqli_stmt_bind_param($stmt, "ssssssssssi", $name, $telephone, $nic, $address, $whatsapp, $email, $birthday, $credit_limit, $discount, $price_type, $id);
        $result = mysqli_stmt_execute($stmt);
        if (!$result) {
            throw new Exception("Failed to execute statement: " . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
        return $result;
    }

    // Delete a customer by ID
    public function deleteCustomer($id)
    {
        $query = "DELETE FROM customers WHERE id = ?";
        $stmt = mysqli_prepare($this->conn, $query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . mysqli_error($this->conn));
        }
        mysqli_stmt_bind_param($stmt, "i", $id);
        $result = mysqli_stmt_execute($stmt);
        if (!$result) {
            throw new Exception("Failed to execute statement: " . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
        return $result;
    }
}
