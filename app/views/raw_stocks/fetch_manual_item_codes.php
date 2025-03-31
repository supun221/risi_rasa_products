<?php
require 'connection_db.php';

$result = $conn->query("SELECT item_code, product_name FROM products WHERE item_code_type='manual' ORDER BY id DESC");
$products = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode(["products" => $products]);
?>
