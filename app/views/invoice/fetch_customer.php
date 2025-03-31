<?php
    require_once 'db_connection.php';
    
    $customerID = $_POST['customerID'];
    
    $query = "SELECT * FROM customers WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $customerID);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();
    
    echo json_encode($customer);
?>