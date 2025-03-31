<?php
require 'connection_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'fetchProducts') {
    // Fetch all products and item codes
    $query = "SELECT product_name, item_code FROM products"; // Replace 'products' with your table name
    $result = $conn->query($query);

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    echo json_encode($products);
    exit;
}
?>
