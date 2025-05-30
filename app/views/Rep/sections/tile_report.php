<?php
// Tile Report - Dashboard style report with key metrics
require_once '../../../../config/databade.php';
session_start();

// Get rep_id from session
$rep_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

// Get rep name
$rep_name = '';
try {
    $stmt = $conn->prepare("SELECT username FROM signup WHERE id = ?");
    $stmt->bind_param("i", $rep_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $rep_name = $row['username'];
    }
} catch (Exception $e) {
    // Error handling
}

// Get current date for reporting period
$today = date('Y-m-d');
$today_display = date('F j, Y');
$month_start = date('Y-m-01');
$month_end = date('Y-m-t');
$year_start = date('Y-01-01');
$year_end = date('Y-12-31');

// Function to format currency values
function formatCurrency($value) {
    return 'Rs. ' . number_format($value, 2);
}

// 1. Today's sales
$today_sales = 0;
$today_sales_count = 0;
try {
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(net_amount), 0) as total_sales, COUNT(id) as sales_count
        FROM pos_sales
        WHERE rep_id = ? AND DATE(sale_date) = ?
    ");
    $stmt->bind_param("is", $rep_id, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $today_sales = $row['total_sales'];
        $today_sales_count = $row['sales_count'];
    }
} catch (Exception $e) {
    // Error handling
}

// 2. This month's sales
$month_sales = 0;
try {
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(net_amount), 0) as total_sales
        FROM pos_sales
        WHERE rep_id = ? AND DATE(sale_date) BETWEEN ? AND ?
    ");
    $stmt->bind_param("iss", $rep_id, $month_start, $month_end);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $month_sales = $row['total_sales'];
    }
} catch (Exception $e) {
    // Error handling
}

// 3. Current lorry stock value
$lorry_stock_value = 0;
$lorry_stock_items = 0;
try {
    $stmt = $conn->prepare("
        SELECT COUNT(id) as item_count, COALESCE(SUM(quantity * unit_price), 0) as total_value
        FROM lorry_stock
        WHERE rep_id = ? AND status = 'active' AND quantity > 0
    ");
    $stmt->bind_param("i", $rep_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $lorry_stock_value = $row['total_value'];
        $lorry_stock_items = $row['item_count'];
    }
} catch (Exception $e) {
    // Error handling
}

// 4. Returns collected today
$today_returns = 0;
$today_returns_count = 0;
try {
    $stmt = $conn->prepare("
        SELECT COUNT(id) as return_count, COALESCE(SUM(total_amount), 0) as total_returns
        FROM return_collections
        WHERE rep_id = ? AND DATE(created_at) = ?
    ");
    $stmt->bind_param("is", $rep_id, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $today_returns = $row['total_returns'];
        $today_returns_count = $row['return_count'];
    }
} catch (Exception $e) {
    // Error handling
}

// 5. Total customers served today
$today_customers = 0;
try {
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT customer_id) as customer_count
        FROM pos_sales
        WHERE rep_id = ? AND DATE(sale_date) = ? AND customer_id IS NOT NULL
    ");
    $stmt->bind_param("is", $rep_id, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $today_customers = $row['customer_count'];
    }
} catch (Exception $e) {
    // Error handling
}

// 6. Total credit sales today
$today_credit_sales = 0;
try {
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(credit_amount), 0) as credit_sales
        FROM pos_sales
        WHERE rep_id = ? AND DATE(sale_date) = ? AND payment_method = 'credit'
    ");
    $stmt->bind_param("is", $rep_id, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $today_credit_sales = $row['credit_sales'];
    }
} catch (Exception $e) {
    // Error handling
}

// 7. Top selling products this month
$top_products = array();
try {
    $stmt = $conn->prepare("
        SELECT psi.product_name, SUM(psi.quantity) as total_qty
        FROM pos_sale_items psi
        JOIN pos_sales ps ON psi.sale_id = ps.id
        WHERE ps.rep_id = ? AND DATE(ps.sale_date) BETWEEN ? AND ?
        GROUP BY psi.product_name
        ORDER BY total_qty DESC
        LIMIT 5
    ");
    $stmt->bind_param("iss", $rep_id, $month_start, $month_end);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $top_products[] = $row;
    }
} catch (Exception $e) {
    // Error handling
}

// 8. Recent stock movements
$recent_movements = array();
try {
    $stmt = $conn->prepare("
        SELECT lt.transaction_type, lt.quantity, lt.product_name, lt.transaction_date
        FROM lorry_transactions lt
        WHERE lt.rep_id = ?
        ORDER BY lt.transaction_date DESC
        LIMIT 5
    ");
    $stmt->bind_param("i", $rep_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $recent_movements[] = $row;
    }
} catch (Exception $e) {
    // Error handling
}

// 9. Get yearly revenue
$yearly_revenue = 0;
try {
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(net_amount), 0) as yearly_revenue
        FROM pos_sales
        WHERE rep_id = ? AND DATE(sale_date) BETWEEN ? AND ?
    ");
    $stmt->bind_param("iss", $rep_id, $year_start, $year_end);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $yearly_revenue = $row['yearly_revenue'];
    }
} catch (Exception $e) {
    // Error handling
}

// 10. Recent advance payments
$recent_advances = array();
try {
    $stmt = $conn->prepare("
        SELECT ap.customer_name, ap.net_amount, ap.payment_type, ap.created_at
        FROM advance_payments ap
        WHERE ap.customer_id IN (
            SELECT DISTINCT ps.customer_id
            FROM pos_sales ps
            WHERE ps.rep_id = ? AND ps.customer_id IS NOT NULL
        )
        ORDER BY ap.created_at DESC
        LIMIT 5
    ");
    $stmt->bind_param("i", $rep_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $recent_advances[] = $row;
    }
} catch (Exception $e) {
    // Error handling
}

// Get sales by payment method for today
$payment_methods_today = array(
    'cash' => 0,
    'card' => 0,
    'credit_card' => 0,
    'cheque' => 0,
    'credit' => 0,
    'bank_transfer' => 0
);

try {
    $stmt = $conn->prepare("
        SELECT 
            payment_method,
            COALESCE(SUM(net_amount), 0) as total_amount,
            COUNT(id) as transaction_count
        FROM pos_sales
        WHERE rep_id = ? AND DATE(sale_date) = ?
        GROUP BY payment_method
    ");
    $stmt->bind_param("is", $rep_id, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $method = strtolower($row['payment_method']);
        if (isset($payment_methods_today[$method])) {
            $payment_methods_today[$method] = $row['total_amount'];
        }
    }
} catch (Exception $e) {
    // Error handling
}

// Get sales by payment method for current month
$payment_methods_month = array(
    'cash' => 0,
    'card' => 0,
    'credit_card' => 0,
    'cheque' => 0,
    'credit' => 0,
    'bank_transfer' => 0
);

try {
    $stmt = $conn->prepare("
        SELECT 
            payment_method,
            COALESCE(SUM(net_amount), 0) as total_amount,
            COUNT(id) as transaction_count
        FROM pos_sales
        WHERE rep_id = ? AND DATE(sale_date) BETWEEN ? AND ?
        GROUP BY payment_method
    ");
    $stmt->bind_param("iss", $rep_id, $month_start, $month_end);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $method = strtolower($row['payment_method']);
        if (isset($payment_methods_month[$method])) {
            $payment_methods_month[$method] = $row['total_amount'];
        }
    }
} catch (Exception $e) {
    // Error handling
}

// Calculate today's profit
$today_profit = 0;
try {
    $stmt = $conn->prepare("
        SELECT 
            SUM((psi.unit_price * psi.quantity) - (COALESCE(se.cost_price, 0) * psi.quantity)) as total_profit
        FROM pos_sale_items psi
        JOIN pos_sales ps ON psi.sale_id = ps.id
        LEFT JOIN lorry_stock ls ON psi.lorry_stock_id = ls.id
        LEFT JOIN stock_entries se ON ls.stock_entry_id = se.id
        WHERE ps.rep_id = ? AND DATE(ps.sale_date) = ?
    ");
    $stmt->bind_param("is", $rep_id, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $today_profit = $row['total_profit'] ?? 0;
    }
} catch (Exception $e) {
    // Error handling
}

// Calculate monthly profit
$monthly_profit = 0;
try {
    $stmt = $conn->prepare("
        SELECT 
            SUM((psi.unit_price * psi.quantity) - (COALESCE(se.cost_price, 0) * psi.quantity)) as total_profit
        FROM pos_sale_items psi
        JOIN pos_sales ps ON psi.sale_id = ps.id
        LEFT JOIN lorry_stock ls ON psi.lorry_stock_id = ls.id
        LEFT JOIN stock_entries se ON ls.stock_entry_id = se.id
        WHERE ps.rep_id = ? AND DATE(ps.sale_date) BETWEEN ? AND ?
    ");
    $stmt->bind_param("iss", $rep_id, $month_start, $month_end);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $monthly_profit = $row['total_profit'] ?? 0;
    }
} catch (Exception $e) {
    // Error handling
}

// Get most profitable products
$profitable_products = array();
try {
    $stmt = $conn->prepare("
        SELECT 
            psi.product_name,
            SUM(psi.quantity) as total_qty,
            SUM((psi.unit_price * psi.quantity) - (COALESCE(se.cost_price, 0) * psi.quantity)) as total_profit
        FROM pos_sale_items psi
        JOIN pos_sales ps ON psi.sale_id = ps.id
        LEFT JOIN lorry_stock ls ON psi.lorry_stock_id = ls.id
        LEFT JOIN stock_entries se ON ls.stock_entry_id = se.id
        WHERE ps.rep_id = ? AND DATE(ps.sale_date) BETWEEN ? AND ?
        GROUP BY psi.product_name
        ORDER BY total_profit DESC
        LIMIT 5
    ");
    $stmt->bind_param("iss", $rep_id, $month_start, $month_end);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if ($row['total_profit'] > 0) {
            $profitable_products[] = $row;
        }
    }
} catch (Exception $e) {
    // Error handling
}
?>

<div class="section-card fade-transition" id="tile-report-section">
    <div class="section-header">
        <i class="fas fa-chart-pie"></i> Dashboard Report
    </div>

    <div class="section-body">
        <a href="#" class="return-link" id="return-from-tile-report">
            <i class="fas fa-chevron-left"></i> Return to Dashboard
        </a>

        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Sales Dashboard for <?php echo htmlspecialchars($rep_name); ?></h4>
                <div>
                    <small class="text-muted">Report Date: <?php echo $today_display; ?></small>
                </div>
            </div>
            <hr>
        </div>

        <div class="row mb-4">
            <!-- Today's Sales -->
            <div class="col-md-4 mb-4">
                <div class="card dashboard-tile h-100">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Today's Sales</h5>
                        <h2 class="mt-3 mb-3"><?php echo formatCurrency($today_sales); ?></h2>
                        <p class="card-text text-muted"><?php echo $today_sales_count; ?> transactions today</p>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <i class="fas fa-shopping-cart fa-2x text-primary"></i>
                    </div>
                </div>
            </div>

            <!-- Today's Profit -->
            <div class="col-md-4 mb-4">
                <div class="card dashboard-tile h-100">
                    <div class="card-body">
                        <h5 class="card-title text-purple">Today's Profit</h5>
                        <h2 class="mt-3 mb-3"><?php echo formatCurrency($today_profit); ?></h2>
                        <p class="card-text text-muted">
                            <?php 
                                $profit_percentage = ($today_sales > 0) ? ($today_profit / $today_sales) * 100 : 0;
                                echo number_format($profit_percentage, 1) . '% margin';
                            ?>
                        </p>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <i class="fas fa-chart-pie fa-2x text-purple"></i>
                    </div>
                </div>
            </div>

            <!-- Monthly Sales -->
            <div class="col-md-4 mb-4">
                <div class="card dashboard-tile h-100">
                    <div class="card-body">
                        <h5 class="card-title text-success">Monthly Sales</h5>
                        <h2 class="mt-3 mb-3"><?php echo formatCurrency($month_sales); ?></h2>
                        <p class="card-text text-muted"><?php echo date('F Y'); ?></p>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <i class="fas fa-calendar-alt fa-2x text-success"></i>
                    </div>
                </div>
            </div>

            <!-- Monthly Profit -->
            <div class="col-md-4 mb-4">
                <div class="card dashboard-tile h-100">
                    <div class="card-body">
                        <h5 class="card-title text-info">Monthly Profit</h5>
                        <h2 class="mt-3 mb-3"><?php echo formatCurrency($monthly_profit); ?></h2>
                        <p class="card-text text-muted">
                            <?php 
                                $monthly_percentage = ($month_sales > 0) ? ($monthly_profit / $month_sales) * 100 : 0;
                                echo number_format($monthly_percentage, 1) . '% margin';
                            ?>
                        </p>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <i class="fas fa-percentage fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Methods Section - Today -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-money-bill-wave text-primary mr-2"></i>
                            Sales by Payment Method (Today)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Cash Sales -->
                            <div class="col-md-2 col-sm-4 mb-3">
                                <div class="card payment-method-card">
                                    <div class="card-body text-center">
                                        <div class="payment-icon mb-2">
                                            <i class="fas fa-money-bill-alt text-success"></i>
                                        </div>
                                        <h6 class="card-title">Cash</h6>
                                        <h5 class="mb-0"><?php echo formatCurrency($payment_methods_today['cash']); ?></h5>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Card Sales -->
                            <div class="col-md-2 col-sm-4 mb-3">
                                <div class="card payment-method-card">
                                    <div class="card-body text-center">
                                        <div class="payment-icon mb-2">
                                            <i class="fas fa-credit-card text-primary"></i>
                                        </div>
                                        <h6 class="card-title">Card</h6>
                                        <h5 class="mb-0"><?php echo formatCurrency($payment_methods_today['card']); ?></h5>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Credit Card Sales -->
                            <div class="col-md-2 col-sm-4 mb-3">
                                <div class="card payment-method-card">
                                    <div class="card-body text-center">
                                        <div class="payment-icon mb-2">
                                            <i class="far fa-credit-card text-info"></i>
                                        </div>
                                        <h6 class="card-title">Credit Card</h6>
                                        <h5 class="mb-0"><?php echo formatCurrency($payment_methods_today['credit_card']); ?></h5>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Cheque Sales -->
                            <div class="col-md-2 col-sm-4 mb-3">
                                <div class="card payment-method-card">
                                    <div class="card-body text-center">
                                        <div class="payment-icon mb-2">
                                            <i class="fas fa-money-check text-warning"></i>
                                        </div>
                                        <h6 class="card-title">Cheque</h6>
                                        <h5 class="mb-0"><?php echo formatCurrency($payment_methods_today['cheque']); ?></h5>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Credit Sales -->
                            <div class="col-md-2 col-sm-4 mb-3">
                                <div class="card payment-method-card">
                                    <div class="card-body text-center">
                                        <div class="payment-icon mb-2">
                                            <i class="fas fa-hand-holding-usd text-danger"></i>
                                        </div>
                                        <h6 class="card-title">Credit</h6>
                                        <h5 class="mb-0"><?php echo formatCurrency($payment_methods_today['credit']); ?></h5>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Bank Transfer -->
                            <div class="col-md-2 col-sm-4 mb-3">
                                <div class="card payment-method-card">
                                    <div class="card-body text-center">
                                        <div class="payment-icon mb-2">
                                            <i class="fas fa-university text-secondary"></i>
                                        </div>
                                        <h6 class="card-title">Bank Transfer</h6>
                                        <h5 class="mb-0"><?php echo formatCurrency($payment_methods_today['bank_transfer']); ?></h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Payment Methods Section - Month -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-money-bill-wave text-success mr-2"></i>
                            Sales by Payment Method (Month)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Cash Sales -->
                            <div class="col-md-2 col-sm-4 mb-3">
                                <div class="card payment-method-card">
                                    <div class="card-body text-center">
                                        <div class="payment-icon mb-2">
                                            <i class="fas fa-money-bill-alt text-success"></i>
                                        </div>
                                        <h6 class="card-title">Cash</h6>
                                        <h5 class="mb-0"><?php echo formatCurrency($payment_methods_month['cash']); ?></h5>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Card Sales -->
                            <div class="col-md-2 col-sm-4 mb-3">
                                <div class="card payment-method-card">
                                    <div class="card-body text-center">
                                        <div class="payment-icon mb-2">
                                            <i class="fas fa-credit-card text-primary"></i>
                                        </div>
                                        <h6 class="card-title">Card</h6>
                                        <h5 class="mb-0"><?php echo formatCurrency($payment_methods_month['card']); ?></h5>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Credit Card Sales -->
                            <div class="col-md-2 col-sm-4 mb-3">
                                <div class="card payment-method-card">
                                    <div class="card-body text-center">
                                        <div class="payment-icon mb-2">
                                            <i class="far fa-credit-card text-info"></i>
                                        </div>
                                        <h6 class="card-title">Credit Card</h6>
                                        <h5 class="mb-0"><?php echo formatCurrency($payment_methods_month['credit_card']); ?></h5>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Cheque Sales -->
                            <div class="col-md-2 col-sm-4 mb-3">
                                <div class="card payment-method-card">
                                    <div class="card-body text-center">
                                        <div class="payment-icon mb-2">
                                            <i class="fas fa-money-check text-warning"></i>
                                        </div>
                                        <h6 class="card-title">Cheque</h6>
                                        <h5 class="mb-0"><?php echo formatCurrency($payment_methods_month['cheque']); ?></h5>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Credit Sales -->
                            <div class="col-md-2 col-sm-4 mb-3">
                                <div class="card payment-method-card">
                                    <div class="card-body text-center">
                                        <div class="payment-icon mb-2">
                                            <i class="fas fa-hand-holding-usd text-danger"></i>
                                        </div>
                                        <h6 class="card-title">Credit</h6>
                                        <h5 class="mb-0"><?php echo formatCurrency($payment_methods_month['credit']); ?></h5>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Bank Transfer -->
                            <div class="col-md-2 col-sm-4 mb-3">
                                <div class="card payment-method-card">
                                    <div class="card-body text-center">
                                        <div class="payment-icon mb-2">
                                            <i class="fas fa-university text-secondary"></i>
                                        </div>
                                        <h6 class="card-title">Bank Transfer</h6>
                                        <h5 class="mb-0"><?php echo formatCurrency($payment_methods_month['bank_transfer']); ?></h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <!-- Lorry Stock Value -->
            <div class="col-md-4 mb-4">
                <div class="card dashboard-tile h-100">
                    <div class="card-body">
                        <h5 class="card-title text-warning">Current Stock Value</h5>
                        <h2 class="mt-3 mb-3"><?php echo formatCurrency($lorry_stock_value); ?></h2>
                        <p class="card-text text-muted"><?php echo $lorry_stock_items; ?> active items</p>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <i class="fas fa-box-open fa-2x text-warning"></i>
                    </div>
                </div>
            </div>

            <!-- Today's Returns -->
            <div class="col-md-4 mb-4">
                <div class="card dashboard-tile h-100">
                    <div class="card-body">
                        <h5 class="card-title text-danger">Today's Returns</h5>
                        <h2 class="mt-3 mb-3"><?php echo formatCurrency($today_returns); ?></h2>
                        <p class="card-text text-muted"><?php echo $today_returns_count; ?> return transactions</p>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <i class="fas fa-undo-alt fa-2x text-danger"></i>
                    </div>
                </div>
            </div>

            <!-- Credit Sales -->
            <div class="col-md-4 mb-4">
                <div class="card dashboard-tile h-100">
                    <div class="card-body">
                        <h5 class="card-title text-secondary">Credit Sales Today</h5>
                        <h2 class="mt-3 mb-3"><?php echo formatCurrency($today_credit_sales); ?></h2>
                        <p class="card-text text-muted"><?php echo $today_customers; ?> customers served</p>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <i class="fas fa-credit-card fa-2x text-secondary"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Top Selling Products -->
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-trophy text-warning mr-2"></i>
                            Top Selling Products
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php if (empty($top_products)): ?>
                                <div class="list-group-item text-center text-muted">
                                    No sales data available
                                </div>
                            <?php else: ?>
                                <?php foreach($top_products as $product): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><?php echo htmlspecialchars($product['product_name']); ?></span>
                                        <span class="badge badge-primary badge-pill"><?php echo $product['total_qty']; ?> units</span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Most Profitable Products -->
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-gem text-purple mr-2"></i>
                            Most Profitable Products
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php if (empty($profitable_products)): ?>
                                <div class="list-group-item text-center text-muted">
                                    No profit data available
                                </div>
                            <?php else: ?>
                                <?php foreach($profitable_products as $product): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><?php echo htmlspecialchars($product['product_name']); ?></span>
                                        <span class="badge badge-success badge-pill"><?php echo formatCurrency($product['total_profit']); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Advance Payments -->
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fas fa-money-bill-wave text-success mr-2"></i>
                            Recent Advance Payments
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php if (empty($recent_advances)): ?>
                                <div class="list-group-item text-center text-muted">
                                    No recent advance payments
                                </div>
                            <?php else: ?>
                                <?php foreach($recent_advances as $advance): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <strong><?php echo htmlspecialchars($advance['customer_name']); ?></strong>
                                            <span class="badge badge-success"><?php echo formatCurrency($advance['net_amount']); ?></span>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo date('M d, Y', strtotime($advance['created_at'])); ?> - 
                                            Payment Type: <?php echo ucfirst($advance['payment_type']); ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-tile {
    border-radius: 10px;
    border: none;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.dashboard-tile:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
}

.dashboard-tile .card-footer {
    text-align: right;
    padding-top: 0;
}

.dashboard-tile h2 {
    font-weight: 600;
}

.dashboard-tile .card-title {
    font-size: 1.1rem;
    font-weight: 500;
}

.payment-method-card {
    transition: all 0.3s ease;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.payment-method-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 8px rgba(0,0,0,0.1);
}

.payment-icon {
    font-size: 1.8rem;
    margin-bottom: 0.5rem;
}

.payment-icon i {
    background-color: rgba(0,0,0,0.04);
    width: 50px;
    height: 50px;
    line-height: 50px;
    border-radius: 50%;
}

.text-purple {
    color: #6f42c1;
}
</style>

<script>
$(document).ready(function() {
    // Return to dashboard link
    $('#return-from-tile-report').click(function(e) {
        e.preventDefault();
        $('.section-card').hide();
        $('#dashboard-section').show();
    });
});
</script>
