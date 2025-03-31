<?php
// require 'connection_db.php';

// $sql = "SELECT item_code, product_name FROM products";
// $result = $conn->query($sql);

// $products = [];
// while ($row = $result->fetch_assoc()) {
//     $products[] = $row;
// }

// echo json_encode($products);

    require 'connection_db.php';

    // Fetch categories and products
    $sql = "SELECT category, item_code, product_name, sinhala_name, image_path FROM products ORDER BY id DESC";
    $result = $conn->query($sql);

    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($products);
?>
