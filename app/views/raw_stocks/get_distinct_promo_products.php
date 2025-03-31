<?php
require 'connection_db.php';

$products = [];

// Step 1: Fetch distinct barcodes from the promotions table
$sql = "SELECT DISTINCT product_barcode FROM promotions WHERE promo_type = 'item'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $barcode = $row['product_barcode'];

        // Step 2: Try fetching the product name from the products table
        $stmt = $conn->prepare("SELECT product_name FROM products WHERE item_code = ?");
        $stmt->bind_param("s", $barcode);
        $stmt->execute();
        $stmt->bind_result($product_name);
        
        if (!$stmt->fetch()) {
            $product_name = "Unknown Product"; // Default if not found
        }

        $products[] = [
            'barcode' => $barcode,
            'product_name' => $product_name
        ];
        
        $stmt->close();
    }
}

echo json_encode($products);

$conn->close();
?>
