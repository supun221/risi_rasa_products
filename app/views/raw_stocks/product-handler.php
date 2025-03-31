<?php
include 'connection_db.php'; 

$action = $_POST['action'] ?? '';

if ($action === 'save') {
    $mode = $_POST['mode'];
    $category = $_POST['category'];
    $productName = $_POST['productName'];
    $sinhalaProductName = $_POST['sinhalaProductName'];
    $itemCode = '';

    if ($mode === 'auto') {
        $result = $conn->query("SELECT MAX(item_code) AS max_code FROM products");
        $row = $result->fetch_assoc();
        $itemCode = $row['max_code'] ? $row['max_code'] + 1 : 10000;
    } else {
        $itemCode = $_POST['manualItemCode'];
    }

    $stmt = $conn->prepare("INSERT INTO products (item_code, category, product_name, sinhala_name) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $itemCode, $category, $productName, $sinhalaProductName);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error saving product.']);
    }
    $stmt->close();
}

if ($action === 'fetch') {
    $result = $conn->query("SELECT item_code, product_name FROM products");
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    echo json_encode($products);
}

if ($action === 'delete') {
    $itemCode = $_POST['itemCode'];
    $stmt = $conn->prepare("DELETE FROM products WHERE item_code = ?");
    $stmt->bind_param("s", $itemCode);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting product.']);
    }
    $stmt->close();
}
