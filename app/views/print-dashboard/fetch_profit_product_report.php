<?php
// Start session to access session variables
session_start();
// Database connection
require_once '../../../config/databade.php';

// Initialize response array
$response = [
    'success' => false,
    'data' => [],
    'message' => ''
];

// Get parameters from request
$category = isset($_GET['category']) ? $_GET['category'] : '';
$barcode = isset($_GET['barcode']) ? $_GET['barcode'] : '';
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';

// Initialize params array for main POS
$paramsMain = [];
$paramsRep = [];

try {
    // Query for main POS sales (bill_records + purchase_items + stock_entries)
    $sqlMain = "
        SELECT 
            pi.product_name,
            pi.product_barcode AS barcode,
            prod.category,
            pi.purchase_qty as quantity,
            pi.price AS selling_price,
            pi.subtotal AS revenue,
            se.cost_price,
            (pi.purchase_qty * se.cost_price) as item_cost,
            0 as discount_percent,
            'main_pos' as source,
            pi.id as item_id
        FROM 
            purchase_items pi
        JOIN 
            bill_records br ON pi.bill_id = br.bill_id
        LEFT JOIN 
            stock_entries se ON pi.stock_id = se.stock_id
        LEFT JOIN 
            products prod ON prod.product_name = pi.product_name
        WHERE 1=1
    ";

    if (!empty($category)) {
        $sqlMain .= " AND prod.category = ?";
        $paramsMain[] = $category;
    }

    if (!empty($barcode)) {
        $sqlMain .= " AND pi.product_barcode = ?";
        $paramsMain[] = $barcode;
    }

    if (!empty($startDate)) {
        $sqlMain .= " AND STR_TO_DATE(br.bill_date, '%Y-%m-%d') >= ?";
        $paramsMain[] = $startDate;
    }

    if (!empty($endDate)) {
        $sqlMain .= " AND STR_TO_DATE(br.bill_date, '%Y-%m-%d') <= ?";
        $paramsMain[] = $endDate;
    }

    // Query for rep sales (pos_sales + pos_sale_items + lorry_stock + stock_entries + products)
    $sqlRep = "
        SELECT 
            psi.product_name,
            COALESCE(ls.barcode, p.item_code) as barcode,
            p.category,
            psi.quantity,
            psi.unit_price AS selling_price,
            psi.subtotal AS revenue,
            se.cost_price,
            (psi.quantity * se.cost_price) as item_cost,
            psi.discount_percent,
            'rep_sales' as source,
            psi.id as item_id
        FROM 
            pos_sale_items psi
        JOIN 
            pos_sales ps ON psi.sale_id = ps.id
        LEFT JOIN 
            lorry_stock ls ON psi.lorry_stock_id = ls.id
        LEFT JOIN 
            stock_entries se ON ls.stock_entry_id = se.id
        LEFT JOIN 
            products p ON p.product_name = psi.product_name
        WHERE 1=1
    ";

    if (!empty($category)) {
        $sqlRep .= " AND p.category = ?";
        $paramsRep[] = $category;
    }

    if (!empty($barcode)) {
        $sqlRep .= " AND (ls.barcode = ? OR p.item_code = ?)";
        $paramsRep[] = $barcode;
        $paramsRep[] = $barcode;
    }

    if (!empty($startDate)) {
        $sqlRep .= " AND DATE(ps.sale_date) >= ?";
        $paramsRep[] = $startDate;
    }

    if (!empty($endDate)) {
        $sqlRep .= " AND DATE(ps.sale_date) <= ?";
        $paramsRep[] = $endDate;
    }

    // Execute main POS query
    $stmtMain = $conn->prepare($sqlMain);
    if (!empty($paramsMain)) {
        $types = str_repeat('s', count($paramsMain));
        $stmtMain->bind_param($types, ...$paramsMain);
    }
    $stmtMain->execute();
    $resultMain = $stmtMain->get_result();
    
    // Execute rep sales query
    $stmtRep = $conn->prepare($sqlRep);
    if (!empty($paramsRep)) {
        $types = str_repeat('s', count($paramsRep));
        $stmtRep->bind_param($types, ...$paramsRep);
    }
    $stmtRep->execute();
    $resultRep = $stmtRep->get_result();
    
    // Collect all sales data
    $allSalesData = [];
    
    // Process main POS data
    while ($row = $resultMain->fetch_assoc()) {
        $allSalesData[] = $row;
    }
    
    // Process rep sales data
    while ($row = $resultRep->fetch_assoc()) {
        $allSalesData[] = $row;
    }
    
    // Group by product name and calculate totals
    $productData = [];
    
    foreach ($allSalesData as $sale) {
        $productName = $sale['product_name'];
        
        if (!isset($productData[$productName])) {
            $productData[$productName] = [
                'product_name' => $productName,
                'barcode' => $sale['barcode'] ?? '',
                'category' => $sale['category'] ?? '',
                'total_qty' => 0,
                'total_revenue' => 0,
                'total_cost' => 0,
                'cost_prices' => [],
                'selling_prices' => [],
                'discount_percents' => [],
                'sources' => []
            ];
        }
        
        $quantity = intval($sale['quantity']);
        $revenue = floatval($sale['revenue']);
        $costPrice = floatval($sale['cost_price'] ?? 0);
        $sellingPrice = floatval($sale['selling_price']);
        $itemCost = floatval($sale['item_cost'] ?? 0);
        $discountPercent = floatval($sale['discount_percent'] ?? 0);
        
        // Accumulate data
        $productData[$productName]['total_qty'] += $quantity;
        $productData[$productName]['total_revenue'] += $revenue;
        $productData[$productName]['total_cost'] += $itemCost;
        
        // Collect cost prices for average calculation (only non-zero values)
        if ($costPrice > 0) {
            $productData[$productName]['cost_prices'][] = $costPrice;
        }
        
        // Collect selling prices for average calculation
        if ($sellingPrice > 0) {
            $productData[$productName]['selling_prices'][] = $sellingPrice;
        }
        
        // Collect discount percentages
        if ($discountPercent > 0) {
            $productData[$productName]['discount_percents'][] = $discountPercent;
        }
        
        // Track sources
        if (!in_array($sale['source'], $productData[$productName]['sources'])) {
            $productData[$productName]['sources'][] = $sale['source'];
        }
        
        // Use barcode from current sale if product barcode is empty
        if (empty($productData[$productName]['barcode']) && !empty($sale['barcode'])) {
            $productData[$productName]['barcode'] = $sale['barcode'];
        }
        
        // Use category from current sale if product category is empty
        if (empty($productData[$productName]['category']) && !empty($sale['category'])) {
            $productData[$productName]['category'] = $sale['category'];
        }
    }
    
    // Calculate averages and format data for response
    $data = [];
    foreach ($productData as $productName => $item) {
        // Calculate average cost price
        $avgCostPrice = 0;
        if (count($item['cost_prices']) > 0) {
            $avgCostPrice = array_sum($item['cost_prices']) / count($item['cost_prices']);
        }
        
        // Calculate average selling price
        $avgSellingPrice = 0;
        if (count($item['selling_prices']) > 0) {
            $avgSellingPrice = array_sum($item['selling_prices']) / count($item['selling_prices']);
        }
        
        // Calculate average discount percentage
        $avgDiscount = 0;
        if (count($item['discount_percents']) > 0) {
            $avgDiscount = array_sum($item['discount_percents']) / count($item['discount_percents']);
        }
        
        // Calculate profit
        $profit = $item['total_revenue'] - $item['total_cost'];
        
        // Calculate profit percentage
        $profitPercentage = $item['total_cost'] > 0 ? 
            (($profit / $item['total_cost']) * 100) : 0;
        
        $data[] = [
            'product_name' => $item['product_name'],
            'barcode' => $item['barcode'],
            'category' => $item['category'],
            'total_qty' => $item['total_qty'],
            'cost_price' => round($avgCostPrice, 2),
            'selling_price' => round($avgSellingPrice, 2),
            'avg_discount' => round($avgDiscount, 2),
            'total_revenue' => round($item['total_revenue'], 2),
            'total_cost' => round($item['total_cost'], 2),
            'profit' => round($profit, 2),
            'profit_percentage' => round($profitPercentage, 2),
            'sources' => implode(', ', $item['sources'])
        ];
    }
    
    if (count($data) > 0) {
        $response['success'] = true;
        $response['data'] = $data;
        $response['message'] = "Profit data retrieved successfully from " . count($allSalesData) . " sale items.";
    } else {
        $response['message'] = "No profit data found for the specified criteria.";
    }
    
} catch (Exception $e) {
    $response['message'] = "Database error: " . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);
?>