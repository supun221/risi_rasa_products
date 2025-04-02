<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Representative Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f7f9fc;
            padding-bottom: 70px;
            font-family: 'Inter', sans-serif;
            color: #3a3a3a;
            line-height: 1.6;
        }
        .container {
            max-width: 1140px;
            padding: 0 20px;
        }
        .section-card {
            border-radius: 6px;
            box-shadow: 0 1px 5px rgba(0,0,0,0.06);
            margin-bottom: 24px;
            background-color: #fff;
            border: 1px solid #e8e9eb;
            overflow: hidden;
        }
        .section-header {
            background-color: #2c3e50;
            color: #fff;
            padding: 16px 20px;
            font-weight: 600;
            font-size: 15px;
            letter-spacing: 0.3px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            align-items: center;
        }
        .section-header i {
            margin-right: 10px;
            font-size: 14px;
            opacity: 0.85;
        }
        .section-body {
            padding: 22px;
        }
        .stock-item {
            border-bottom: 1px solid #f0f0f0;
            padding: 12px 0;
        }
        .stock-item:last-child {
            border-bottom: none;
        }
        .bottom-nav {
            position: fixed;
            bottom: 0;
            width: 100%;
            background-color: #fff;
            box-shadow: 0 -1px 6px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-around;
            padding: 10px 0;
            z-index: 1000;
            border-top: 1px solid #f0f0f0;
        }
        .nav-item {
            text-align: center;
            color: #6c757d;
            transition: all 0.2s ease;
            padding: 5px 0;
            cursor: pointer;
            width: 25%;
            font-size: 13px;
        }
        .nav-item.active {
            color: #2c3e50;
            font-weight: 600;
        }
        .nav-item:hover {
            color: #2c3e50;
        }
        .nav-item i {
            font-size: 16px;
            margin-bottom: 4px;
        }
        .form-group label {
            font-weight: 600;
            color: #4a4a4a;
            font-size: 13px;
            margin-bottom: 8px;
            display: block;
        }
        .form-control {
            border: 1px solid #e2e2e2;
            border-radius: 4px;
            padding: 10px 12px;
            height: auto;
            transition: all 0.2s ease;
            font-size: 14px;
            background-color: #fcfcfc;
        }
        .form-control:focus {
            border-color: #a0a0a0;
            box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.03);
            background-color: #fff;
        }
        select.form-control {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml;charset=utf8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 4 5'%3E%3Cpath fill='%23343a40' d='M2 0L0 2h4zm0 5L0 3h4z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 8px 10px;
            padding-right: 25px;
        }
        .btn {
            border-radius: 4px;
            padding: 10px 18px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s ease;
            letter-spacing: 0.3px;
        }
        .btn-primary {
            background-color: #2c3e50;
            border-color: #2c3e50;
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: #1e2b37;
            border-color: #1e2b37;
            box-shadow: 0 0 0 0.2rem rgba(44, 62, 80, 0.25);
        }
        .btn-success {
            background-color: #34495e;
            border-color: #34495e;
        }
        .btn-success:hover, .btn-success:focus {
            background-color: #2c3e50;
            border-color: #2c3e50;
            box-shadow: 0 0 0 0.2rem rgba(52, 73, 94, 0.25);
        }
        .table {
            margin-bottom: 0;
            color: #333;
            font-size: 14px;
        }
        .table thead th {
            border-top: 0;
            background-color: #f7f7f7;
            color: #444;
            font-weight: 600;
            border-bottom: 1px solid #eaeaea;
            padding: 12px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .table td {
            padding: 12px;
            vertical-align: middle;
            border-top: 1px solid #f0f0f0;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #fdfdfd;
        }
        .table tfoot th {
            background-color: #f7f7f7;
            font-weight: 600;
            border-top: 1px solid #eaeaea;
            color: #444;
            padding: 12px;
        }
        h2.dashboard-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 25px;
            text-align: center;
            font-size: 20px;
            letter-spacing: 0.5px;
        }
        .dashboard-header {
            margin-bottom: 25px;
            padding: 20px 0;
            border-bottom: 1px solid #eaeaea;
        }
        .required-indicator {
            color: #e74c3c;
            margin-left: 3px;
        }
        .helper-text {
            font-size: 12px;
            color: #777;
            margin-top: 5px;
        }
        /* New tile-based layout styles */
        .tiles-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 24px;
        }
        .tile {
            border-radius: 6px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.08);
            background-color: #fff;
            border: 1px solid #e8e9eb;
            overflow: hidden;
            height: 140px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: all 0.25s ease;
            position: relative;
        }
        .tile:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.12);
            border-color: #d5d8da;
        }
        .tile:active {
            transform: translateY(0);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .tile i {
            font-size: 36px;
            color: #2c3e50;
            margin-bottom: 12px;
            transition: all 0.25s ease;
        }
        .tile:hover i {
            transform: scale(1.1);
            color: #34495e;
        }
        .tile-title {
            font-weight: 600;
            font-size: 16px;
            color: #333;
            transition: color 0.25s ease;
        }
        .tile:hover .tile-title {
            color: #2c3e50;
        }
        
        /* Active section indicator */
        .section-active {
            border-left: 4px solid #2c3e50;
        }
        
        /* Fade transition for sections */
        .fade-transition {
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        
        .return-link {
            display: inline-flex;
            align-items: center;
            color: #2c3e50;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 15px;
            padding: 6px 10px;
            border-radius: 4px;
            transition: background 0.2s;
        }
        
        .return-link:hover {
            background-color: #f5f5f5;
            text-decoration: none;
            color: #1e2b37;
        }
        
        .return-link i {
            margin-right: 6px;
            font-size: 12px;
        }
        
        /* Enhanced mobile responsiveness */
        @media (max-width: 576px) {
            .container {
                padding: 0 10px;
            }
            .section-header {
                padding: 12px 14px;
                font-size: 14px;
            }
            .section-body {
                padding: 14px;
            }
            .dashboard-header {
                padding: 12px 0;
                margin-bottom: 15px;
            }
            h2.dashboard-title {
                font-size: 18px;
                margin-bottom: 15px;
            }
            .tiles-container {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            .tile {
                height: 110px; /* Slightly taller than before */
                min-height: 110px;
                border-radius: 8px;
                box-shadow: 0 3px 6px rgba(0,0,0,0.1);
            }
            
            .tile:after {
                content: "";
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                height: 3px;
                background: linear-gradient(to right, #2c3e50, #34495e);
                opacity: 0;
                transition: opacity 0.2s;
            }
            
            .tile:hover:after {
                opacity: 1;
            }
            
            /* Mobile optimization for forms */
            .form-group {
                margin-bottom: 16px;
            }
            
            /* Remove excess white space */
            .section-body {
                padding: 16px;
            }
            
            /* Better transitions for mobile */
            .section-card {
                transition: all 0.3s ease;
            }
            .tile i {
                font-size: 24px;
                margin-bottom: 6px;
            }
            .tile-title {
                font-size: 14px;
            }
            .bottom-nav {
                padding: 8px 0;
            }
            .nav-item i {
                font-size: 14px;
            }
            .nav-item {
                font-size: 11px;
            }
            .form-group label {
                font-size: 12px;
            }
            .form-control {
                font-size: 13px;
                padding: 12px; /* Larger tap targets for mobile */
                height: 46px;
            }
            select.form-control {
                height: 46px;
            }
            .btn {
                padding: 12px 16px; /* Larger buttons for touch */
                font-size: 14px;
            }
            .table {
                font-size: 12px;
            }
            .table td, .table th {
                padding: 8px;
            }
            .table-responsive {
                margin: 0 -14px; /* Extend table to full width on mobile */
                width: calc(100% + 28px);
            }
            .helper-text {
                font-size: 11px;
            }
            body {
                padding-bottom: 60px; /* Account for smaller bottom nav */
            }
        }

        /* Additional media query for medium-small devices */
        @media (max-width: 767px) and (min-width: 577px) {
            .tiles-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            .tile {
                height: 120px;
            }
        }

        /* Enhancements for landscape mode on mobile */
        @media (max-height: 500px) and (orientation: landscape) {
            .tiles-container {
                grid-template-columns: repeat(4, 1fr);
            }
            .tile {
                height: 90px;
            }
            .tile i {
                font-size: 20px;
                margin-bottom: 4px;
            }
            .dashboard-header {
                padding: 8px 0;
                margin-bottom: 10px;
            }
            .section-card {
                margin-bottom: 15px;
            }
            body {
                padding-bottom: 50px;
            }
            .bottom-nav {
                padding: 5px 0;
            }
        }

        /* Touch-friendly form inputs */
        @media (max-width: 992px) {
            input[type="radio"], input[type="checkbox"] {
                transform: scale(1.2);
                margin-right: 8px;
            }
            select.form-control option {
                font-size: 14px;
                padding: 10px;
            }
            .btn {
                min-height: 44px; /* Apple HIG recommendation */
            }
            .modal-footer .btn {
                margin-top: 5px;
                margin-bottom: 5px;
            }
        }

        /* Sri Lanka Clock Styles */
        .sl-clock-container {
            text-align: center;
            background-color: #f8f9fa;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.08);
            border: 1px solid #e8e9eb;
        }
        
        .sl-clock-label {
            font-size: 13px;
            color: #666;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .sl-clock-label i {
            margin-right: 5px;
            color: #d44949;
        }
        
        .sl-clock-time {
            font-size: 24px;
            font-weight: 700;
            color: #2c3e50;
            letter-spacing: 0.5px;
        }
        
        .sl-clock-date {
            font-size: 13px;
            color: #777;
            margin-top: 3px;
        }
        
        @media (max-width: 576px) {
            .sl-clock-container {
                padding: 8px;
                margin-bottom: 10px;
            }
            
            .sl-clock-time {
                font-size: 20px;
            }
            
            .sl-clock-label, .sl-clock-date {
                font-size: 12px;
            }
        }
    </style>
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
            <div class="tile" id="add-to-lorry-tile" data-target="add-section">
                <i class="fas fa-plus-circle"></i>
                <div class="tile-title">Add to Lorry</div>
            </div>
            
            <!-- POS Tile -->
            <div class="tile" id="pos-tile" data-target="pos-section">
                <i class="fas fa-cash-register"></i>
                <div class="tile-title">POS System</div>
            </div>
            
            <!-- Customer Tile -->
            <div class="tile" id="customer-tile" data-target="customer-section">
                <i class="fas fa-users"></i>
                <div class="tile-title">Customers</div>
            </div>
            
            <!-- Retrieve from Lorry Tile -->
            <div class="tile" id="retrieve-from-lorry-tile" data-target="retrieve-section">
                <i class="fas fa-dolly"></i>
                <div class="tile-title">Retrieve Stock</div>
            </div>
        </div>
        
        <!-- Add to Lorry Stock Section (hidden by default) -->
        <div class="section-card fade-transition" id="add-section" style="display: none;">
            <div class="section-header">
                <i class="fas fa-plus-circle"></i> Add to Lorry Stock
            </div>
            <div class="section-body">
                <a href="#" class="return-link" id="return-from-add">
                    <i class="fas fa-chevron-left"></i> Return to Dashboard
                </a>
                <form>
                    <div class="form-group">
                        <label for="product">Product <span class="required-indicator">*</span></label>
                        <select class="form-control" id="product" required>
                            <option value="">Select Product</option>
                            <option value="1">Product 1</option>
                            <option value="2">Product 2</option>
                            <option value="3">Product 3</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity <span class="required-indicator">*</span></label>
                        <input type="number" class="form-control" id="quantity" min="1" required>
                        <div class="helper-text">Enter the number of units to add to the lorry</div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Add to Lorry</button>
                </form>
            </div>
        </div>
        
        <!-- POS System Section (hidden by default) -->
        <div class="section-card fade-transition" id="pos-section" style="display: none;">
            <div class="section-header">
                <i class="fas fa-cash-register"></i> POS System
            </div>
            <div class="section-body">
                <a href="#" class="return-link" id="return-from-pos">
                    <i class="fas fa-chevron-left"></i> Return to Dashboard
                </a>
                <form>
                    <div class="form-group">
                        <label for="pos-product">Product <span class="required-indicator">*</span></label>
                        <select class="form-control" id="pos-product" required>
                            <option value="">Select Product</option>
                            <option value="1">Product 1</option>
                            <option value="2">Product 2</option>
                            <option value="3">Product 3</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="pos-quantity">Quantity <span class="required-indicator">*</span></label>
                        <input type="number" class="form-control" id="pos-quantity" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="customer-name">Customer <span class="required-indicator">*</span></label>
                        <input type="text" class="form-control" id="customer-name" required placeholder="Enter customer name">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Complete Sale</button>
                </form>
            </div>
        </div>
        
        <!-- Customer Management Section (hidden by default) -->
        <div class="section-card fade-transition" id="customer-section" style="display: none;">
            <div class="section-header">
                <i class="fas fa-users"></i> Customer Management
            </div>
            <div class="section-body">
                <a href="#" class="return-link" id="return-from-customer">
                    <i class="fas fa-chevron-left"></i> Return to Dashboard
                </a>
                <form>
                    <div class="form-group">
                        <label for="customer-search">Search Customers</label>
                        <input type="text" class="form-control" id="customer-search" placeholder="Search by name or phone">
                    </div>
                    <button type="button" class="btn btn-primary mb-3">Search</button>
                </form>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Total Purchases</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>John Doe</td>
                                <td>123-456-7890</td>
                                <td>$450.00</td>
                                <td><button class="btn btn-sm btn-outline-primary">View</button></td>
                            </tr>
                            <tr>
                                <td>Jane Smith</td>
                                <td>987-654-3210</td>
                                <td>$325.00</td>
                                <td><button class="btn btn-sm btn-outline-primary">View</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Retrieve from Lorry Section (hidden by default) -->
        <div class="section-card fade-transition" id="retrieve-section" style="display: none;">
            <div class="section-header">
                <i class="fas fa-dolly"></i> Retrieve from Lorry
            </div>
            <div class="section-body">
                <a href="#" class="return-link" id="return-from-retrieve">
                    <i class="fas fa-chevron-left"></i> Return to Dashboard
                </a>
                <form>
                    <div class="form-group">
                        <label for="retrieve-product">Product <span class="required-indicator">*</span></label>
                        <select class="form-control" id="retrieve-product" required>
                            <option value="">Select Product</option>
                            <option value="1">Product 1</option>
                            <option value="2">Product 2</option>
                            <option value="3">Product 3</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="retrieve-quantity">Quantity <span class="required-indicator">*</span></label>
                        <input type="number" class="form-control" id="retrieve-quantity" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="retrieve-reason">Reason for Retrieval <span class="required-indicator">*</span></label>
                        <select class="form-control" id="retrieve-reason" required>
                            <option value="">Select Reason</option>
                            <option value="sale">Sale to Customer</option>
                            <option value="damage">Damaged Goods</option>
                            <option value="return">Return to Warehouse</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group" id="customer-details" style="display: none;">
                        <label for="customer">Customer Name</label>
                        <input type="text" class="form-control" id="customer" placeholder="Enter customer name">
                    </div>
                    <button type="submit" class="btn btn-success btn-block">Confirm Retrieval</button>
                </form>
            </div>
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
    <script>
        $(document).ready(function() {
            // Initialize Sri Lanka Clock
            updateSriLankaTime();
            setInterval(updateSriLankaTime, 1000);
            
            // Function to update Sri Lanka time (Asia/Colombo)
            function updateSriLankaTime() {
                const now = new Date();
                
                // Sri Lanka is UTC+5:30
                const sriLankaTime = new Date(now.getTime() + (5.5 * 60 * 60 * 1000));
                
                // Format time: HH:MM:SS AM/PM
                let hours = sriLankaTime.getUTCHours();
                const minutes = sriLankaTime.getUTCMinutes().toString().padStart(2, '0');
                const seconds = sriLankaTime.getUTCSeconds().toString().padStart(2, '0');
                const ampm = hours >= 12 ? 'PM' : 'AM';
                
                hours = hours % 12;
                hours = hours ? hours : 12; // the hour '0' should be '12'
                const hoursStr = hours.toString().padStart(2, '0');
                
                const timeStr = `${hoursStr}:${minutes}:${seconds} ${ampm}`;
                $('#sl-time').text(timeStr);
                
                // Format date: Day, Month Date, Year
                const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                
                const day = days[sriLankaTime.getUTCDay()];
                const date = sriLankaTime.getUTCDate();
                const month = months[sriLankaTime.getUTCMonth()];
                const year = sriLankaTime.getUTCFullYear();
                
                const dateStr = `${day}, ${month} ${date}, ${year}`;
                $('#sl-date').text(dateStr);
            }
            
            // Show customer details when sale is selected
            $('#retrieve-reason').change(function() {
                if($(this).val() === 'sale') {
                    $('#customer-details').slideDown(200);
                } else {
                    $('#customer-details').slideUp(200);
                }
            });
            
            // Tab navigation with animation
            $('.nav-item').click(function() {
                $('.nav-item').removeClass('active');
                $(this).addClass('active');
                // You would add page transition logic here
            });
            
            // Unified return function
            function returnToDashboard() {
                $('.section-card').not('#stock-section').fadeOut(200);
                $('#stock-section').fadeIn(300);
                $('#main-tiles').fadeIn(300);
                
                // Smooth scroll to top if needed
                if ($(window).scrollTop() > 0) {
                    $('html, body').animate({
                        scrollTop: 0
                    }, 200);
                }
                
                return false;
            }
            
            // Return link handlers
            $('.return-link').click(function(e) {
                e.preventDefault();
                returnToDashboard();
            });
            
            // Unified tile click handler with transitions
            $('.tile').click(function() {
                const targetId = $(this).data('target');
                
                // Hide all sections with animation
                $('.section-card').not('#' + targetId).fadeOut(200);
                $('#main-tiles').fadeOut(200, function() {
                    // Show target section with animation
                    $('#' + targetId).fadeIn(300).addClass('section-active');
                    
                    // Scroll to the section
                    $('html, body').animate({
                        scrollTop: $('#' + targetId).offset().top - 15
                    }, 300);
                });
            });
            
            // Show stock section by default
            $('#stock-section').show();
            
            // Handle browser back button
            $(window).on('popstate', function() {
                returnToDashboard();
            });
            
            // Add hover effects for better touch feedback
            $('.tile').on('touchstart', function() {
                $(this).css('transform', 'scale(0.98)');
            }).on('touchend', function() {
                $(this).css('transform', '');
            });
        });
    </script>
</body>
</html>
