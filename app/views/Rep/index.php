<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Representative Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/rep-dashboard.css">
</head>
<body>
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
                            <tr>
                                <td>Product 1</td>
                                <td>15</td>
                                <td>$150.00</td>
                            </tr>
                            <tr>
                                <td>Product 2</td>
                                <td>8</td>
                                <td>$240.00</td>
                            </tr>
                            <tr>
                                <td>Product 3</td>
                                <td>23</td>
                                <td>$345.00</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2">Total Value</th>
                                <th>$735.00</th>
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
            <div class="tile" id="retrieve-from-lorry-tile" data-target="retrieve-section" data-file="retrieve_stock">
                <i class="fas fa-dolly"></i>
                <div class="tile-title">Retrieve Stock</div>
            </div>
        </div>
        
        <!-- Dynamic Content Container -->
        <div id="dynamic-content">
            <!-- Content will be loaded here -->
        </div>
    </div>
    
    <!-- Bottom Navigation -->
    <div class="bottom-nav">
        <div class="nav-item active">
            <i class="fas fa-truck"></i>
            <div>Stock</div>
        </div>
        <div class="nav-item">
            <i class="fas fa-history"></i>
            <div>History</div>
        </div>
        <div class="nav-item">
            <i class="fas fa-chart-bar"></i>
            <div>Reports</div>
        </div>
        <div class="nav-item">
            <i class="fas fa-user-circle"></i>
            <div>Profile</div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/rep-dashboard.js"></script>
</body>
</html>
