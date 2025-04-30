<!DOCTYPE html>
<?php
// Correct the database connection path - there was a typo in "databade.php"
require_once '../../../config/databade.php';

// Get rep_id from session (assuming it's stored there)
session_start();
$rep_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1; // Default to 1 for testing

// Fetch lorry stock data
try {
    // Make sure connection is established
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed.");
    }
    
    $stmt = $conn->prepare("
        SELECT product_name, SUM(quantity) as total_qty, SUM(total_amount) as total_value
        FROM lorry_stock 
        WHERE rep_id = ? AND status = 'active'
        GROUP BY product_name
        ORDER BY product_name
    ");
    
    if (!$stmt) {
        throw new Exception("Query preparation failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $rep_id);
    $stmt->execute();
    $lorry_stock = $stmt->get_result();
    
    // Calculate grand total
    $grand_total = 0;
    $temp_results = [];
    
    while ($row = $lorry_stock->fetch_assoc()) {
        $temp_results[] = $row;
        $grand_total += $row['total_value'];
    }
    
} catch (Exception $e) {
    // Store error message for debugging
    $error_message = "Error: " . $e->getMessage();
    error_log($error_message);
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Representative Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/rep-dashboard.css">
    <style>
        .logout-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 100;
            font-size: 16px;
            padding: 8px 16px;
            font-weight: 600;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            min-width: 110px;
        }
        
        .logout-btn i {
            margin-right: 8px;
            font-size: 18px;
        }
        
        @media (max-width: 576px) {
            .logout-btn {
                top: 10px;
                right: 10px;
                font-size: 14px;
                padding: 8px 12px;
                min-width: auto;
            }
            
            .logout-btn i {
                margin-right: 5px;
            }
        }
    </style>
</head>
<body>
    <!-- Logout Button -->
    <a href="../../controllers/logout.php" class="btn btn-danger logout-btn" title="Logout">
        <i class="fas fa-sign-out-alt"></i> 
    </a>
    
    <div class="container">
        <div class="dashboard-header">
            <h2 class="dashboard-title">Rep Dashboard</h2>
        </div>
        
        <!-- Sri Lanka Time Clock -->
        <div class="sl-clock-container">
            <div class="sl-clock-label">
                <i class="far fa-clock"></i> Sri Lanka Time (Asia/Colombo)
            </div>
            <div class="sl-clock-time" id="sl-time">Loading...</div>
            <div class="sl-clock-date" id="sl-date">Loading...</div>
        </div>
        
        <!-- Remaining Lorry Stock Section - Now at the top -->
        <div class="section-card fade-transition" id="stock-section">
            <div class="section-header">
                <i class="fas fa-clipboard-list"></i> Remaining Lorry Stock
            </div>
            <div class="section-body">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($temp_results) && !empty($temp_results)): ?>
                                <?php foreach ($temp_results as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['total_qty']); ?></td>
                                        <td>Rs. <?php echo number_format($item['total_value'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center">No items in lorry stock</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2">Total Value</th>
                                <th>Rs. <?php echo isset($grand_total) ? number_format($grand_total, 2) : '0.00'; ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Tile-based Navigation -->
        <div class="tiles-container" id="main-tiles">
            <!-- Add to Lorry Tile -->
            <div class="tile" id="add-to-lorry-tile" data-target="add-section" data-file="add_to_lorry">
                <i class="fas fa-plus-circle"></i>
                <div class="tile-title">Add to Lorry</div>
            </div>
            
            <!-- POS Tile -->
            <div class="tile" id="pos-tile" data-target="pos-section" data-file="pos_system">
                <i class="fas fa-cash-register"></i>
                <div class="tile-title">POS System</div>
            </div>
            
            <!-- Customer Tile -->
            <div class="tile" id="customer-tile" data-target="customer-section" data-file="customers">
                <i class="fas fa-users"></i>
                <div class="tile-title">Customers</div>
            </div>
            
            <!-- Retrieve from Lorry Tile -->
            <!-- <div class="tile" id="retrieve-from-lorry-tile" data-target="retrieve-section" data-file="retrieve_stock">
                <i class="fas fa-dolly"></i>
                <div class="tile-title">Sales Stock</div>
            </div> -->
            
            <!-- Advance Payment Tile -->
            <div class="tile" id="advance-payment-tile" data-target="advance-payment-section" data-file="advance_payment">
                <i class="fas fa-money-check-alt"></i>
                <div class="tile-title">Advance Payment</div>
            </div>
            
            <!-- Return Collection Tile -->
            <div class="tile" id="return-collection-tile" data-target="return-collection-section" data-file="return_collection">
                <i class="fas fa-undo-alt"></i>
                <div class="tile-title">Return Collection</div>
            </div>
        </div>
        
        <!-- Dynamic Content Container -->
        <div id="dynamic-content">
            <!-- Content will be loaded here -->
        </div>
    </div>
    
    <!-- Bottom Navigation Bar -->
    <div class="bottom-nav">
        <div class="nav-item active" id="dashboard-nav">
            <i class="fas fa-home"></i>
            <div>Dashboard</div>
            <span class="nav-indicator"></span>
        </div>
        <div class="nav-item" id="stock-nav">
            <i class="fas fa-truck"></i>
            <div>Stock</div>
            <span class="nav-indicator"></span>
        </div>
        <div class="nav-item" id="pos-nav">
            <div class="nav-icon-circle">
                <i class="fas fa-cash-register"></i>
            </div>
            <div>POS</div>
            <span class="nav-indicator"></span>
        </div>
        <div class="nav-item" id="history-nav">
            <i class="fas fa-history"></i>
            <div>History</div>
            <span class="nav-indicator"></span>
        </div>
        <div class="nav-item" id="profile-nav">
            <i class="fas fa-user"></i>
            <div>Profile</div>
            <span class="nav-indicator"></span>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/rep-dashboard.js"></script>
</body>
</html>
