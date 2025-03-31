<?php
    session_start();
    $user_name = $_SESSION['username'];
    $user_role = $_SESSION['job_role'];
    $user_branch = $_SESSION['store'];
    
    if(!$user_name && !$user_branch){
        header("Location: ../unauthorized/unauthorized_access.php");
        exit();
    }

    // Handle branch switch request
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_branch'])) {
        if ($user_role === 'admin') {
            $_SESSION['store'] = $_POST['new_branch'];
            $user_branch = $_POST['new_branch'];
        }
    }
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>

     <!-- JsBarcode Library -->
     <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
     <link rel="stylesheet" href="./create-invoice.styles.css">


    <link rel="stylesheet" href="../../assets/css/inventory_style.css">
</head>
<body>
    
    <div class="container">
        <input type="text" class="eland-input" id="current_branch" value="<?php echo $user_branch?>" disabled hidden>
        <!-- Header -->
        <!-- <div class="header">
            <h1>GRN / Purchasing / Suppliers / Item Registration</h1>
        </div> -->

        <!-- Header Section -->
        <div class="header-container">
            <!-- Left Section: Inventory Image -->
            <div class="inventory-image">
                <img src="../../assets/images/spices.png" alt="Inventory" />
            </div>
            
            <!-- Middle Section: Company Name -->
            <div class="company-name">
                <!-- <span class="heading-sinhala">එග්ලන්ඩ් සුපර්</span> -->
                <span class="heading-english">Ameena Chilies</span>
                <span class="company-motto">The Best Spicy</span>
            </div>
            
            <!-- Right Section: Date, Time, User, and Store Switcher -->
            <div class="date-time-container">
                <div class="date">
                    <p><strong id="current-date">November 21, 2024</strong></p>
                </div>
                <div class="time">
                    <h2 id="current-time">7:28:50 AM</h2>
                </div>
                <div class="user-info">
                    <label style="font-weight:500; ">User: </label>
                    <span id="user" style="color:  #facf00 ; font-weight:500; " ><?php echo htmlspecialchars($user_name); ?></span>
                    <label style="font-weight:500; ">Branch: </label>
                    <span id="user" style="color:  #facf00 ; font-weight:500; "><?php echo htmlspecialchars($user_branch); ?></span>
                </div>

                <?php if ($user_role === 'admin') : ?>
                <div class="store-switcher">
                    <form method="POST" style="display:flex;">
                        <select name="new_branch" style="width:fit-content; padding:8px 15px">
                            <option value="Main Store" <?php echo ($user_branch === "Main Store") ? "selected" : ""; ?>>Main Store</option>
                            <option value="Branch Store" <?php echo ($user_branch === "Branch Store") ? "selected" : ""; ?>>Branch Store</option>
                            <!-- <option value="Kurunegala Store" <?php echo ($user_branch === "Kurunegala Store") ? "selected" : ""; ?>>Kurunegala Store</option> -->
                        </select>
                        <button type="submit" style="width:fit-content; padding:8px 15px">Switch</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Product Registration (Left Column) -->
            <div class="product-registration">
                <h2 class="form-topic">Product Registration</h2>
                <form id="product-form">
                    <div class="form-group">
                        <div class="radio-buttons">
                            <!-- <label for="auto-manual">Mode:</label> -->
                            <input type="radio" id="auto" name="mode" value="auto" checked> Auto
                            <input type="radio" id="manual" name="mode" value="manual"> Manual
                        </div>
                    </div>

                    <div class="form-group" id="category-group">
                        <label for="category-select" id="category-label" >Category:</label>
                        <select id="category-select" name="category" required>
                            
                        </select>
                        <!-- Plus Button -->
                        <button type="button" id="add-category-btn">+</button>
                    </div>

                    <div class="form-group" id="auto-item-code-group">
                        <label for="item-code">Barcode:</label>
                        <input type="text" required id="item-code" name="item-code" readonly>
                    </div>

                    <div class="form-group">
                        <label for="product-name">Product Name:</label>
                        <input type="text" id="product-name" name="product-name" required>
                    </div>

                    <div class="form-group">
                        <label for="product-name">නිෂ්පාදනයේ නම:</label>
                        <input type="text" id="sinhala-product-name" name="sinhala-product-name" required>
                    </div>

                    <!-- <div class="form-group">
                        <label for="product-mrprice">Maximum Retail Price:</label>
                        <input type="text" id="product-mrprice" name="product-mrprice" required>
                    </div>

                    <div class="form-group">
                        <label for="product-price">Our Price:</label>
                        <input type="text" id="product-price" name="product-price" required>
                    </div>

                    <div class="form-group">
                        <label for="product-wprice">Wholesale Price:</label>
                        <input type="text" id="product-wprice" name="product-wprice" required>
                    </div> -->

                    <div class="form-group">
                        <label for="product-image">Product Image:</label>
                        <input type="file" id="product-image" name="product-image" accept="image/*">
                    </div>
                    <div class="success-error-msg" id="error-message" style="display:none; background-color:#ffcccb; color:#d8000c; padding:10px; border-radius:5px; margin-top:10px;"></div>
                    <div class="success-error-msg" id="success-message" style="display:none; background-color:#d4edda; color:#155724; padding:10px; border-radius:5px; margin-top:10px;"></div>
                    <div class="form-group">
                        <button type="button" id="save-product"><i class="fa fa-save save-icon"></i>Save & Update</button>
                    </div>

                    <div id="manual-item-list">
                        <h3>Manual Item Code List</h2>
                        <div class="scrollable-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Product Name</th>
                                    </tr>
                                </thead>
                                <tbody id="product-list">
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                </form>
      
                <!-- Category Management Modal -->
                <div id="category-modal" class="category-modal">
                    <div id="category-container">
                        <div class="modal-header">
                            <h3>Category</h3>
                            <span id="close-modal" class="close">&times;</span>
                        </div>

                        <div id="category-sections">
                            <!-- Left Section: Scrollable Table for Categories -->
                            <div id="category-left-section">
                                <div class="category-table-container">
                                    <table id="category-table">
                                        <thead>
                                            <tr>
                                                <th>Brand</th>
                                                <th>Key</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Categories will be dynamically populated here -->
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Buttons below table -->
                                <div id="category-table-buttons">
                                    <button id="update-category" class="cat-button">Update Category</button>
                                    <button id="hide-category" class="cat-button">Hide Category</button>
                                </div>
                            </div>

                            <!-- Right Section: New Category Form -->
                            <div id="category-right-section">
                                <div id="new-category-section">
                                    
                                    <label for="new-category-radio"><b>New Category</b></label>

                                    <div id="category-form">
                                        
                                        <input type="text" id="category-name" class="cat-input-field" placeholder="Category Name">
                                        
                                        <input type="text" id="category-key" class="cat-input-field" placeholder="Key Ex: CA">
                                    </div>
                                </div>

                                <!-- Save Button under the form -->
                                <div id="category-save-button">
                                    <button id="save-category" class="cat-button">Save</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Update Category Modal -->
                <div id="update-category-modal" class="update-category-modal" style="display:none;">
                    <div id="update-category-container">
                        <div class="modal-header">
                            <h3>Update Category</h3>
                            <span id="close-update-modal" class="close">&times;</span>
                        </div>

                        <div id="update-category-content">
                            <!-- Existing Name and Key -->
                            <div id="existing-category-fields">
                                <div class="form-group">
                                    <label for="existing-category-name">Existing Name:</label>
                                    <input type="text" id="existing-category-name" class="cat-input-field" disabled>
                                </div>
                                <div class="form-group">
                                    <label for="existing-category-key">Existing Key:</label>
                                    <input type="text" id="existing-category-key" class="cat-input-field" disabled>
                                </div>
                            </div>

                            <!-- New Name and Key Input Fields -->
                            <div id="new-category-form">
                                <div class="form-group">
                                    <label for="update-category-name">New Name:</label>
                                    <input type="text" id="update-category-name" class="cat-input-field" placeholder="Enter New Name">
                                </div>
                                <div class="form-group">
                                    <label for="update-category-key">New Key:</label>
                                    <input type="text" id="update-category-key" class="cat-input-field" placeholder="Enter New Key">
                                </div>
                            </div>
                        </div>

                        <!-- Update Button -->
                        <div id="update-category-buttons">
                            <button id="confirm-update-category" class="cat-button">Update</button>
                        </div>
                    </div>
                </div>




                <button type="button" class="delete-button">All Products</button>
            </div>

            <!-- Create Stock / GRN (Right Column) -->
            <div class="create-stock">
                <h2 class="form-topic">Create Stock / GRN</h2>
                <form id="stock-form">
                    
                    <!-- Supplier Information -->
                    <div class="supplier-info">
                        <label for="supplier-id">Supplier ID:</label>
                        <input type="text" id="supplier-id" name="supplier-id" class="supplier-input-field" value="" readonly />
                        
                        <label for="supplier">Supplier Information:</label>
                        <select id="supplier-select" name="supplier" class="supplier-input-field" onchange="setSupplierId()">
  
                        </select>
                        
                        <!-- Add Supplier Button -->
                        <button type="button" id="add-supplier-btn">+ Add Supplier</button>
                    </div>

                    <!-- Checkbox Options -->
                    <div class="checkbox-options" >
                        <div class="checkbox-container"><input type="checkbox" name="free-item"><label>Free Item</label></div>
                        <div class="checkbox-container"><input type="checkbox" name="combine-with-stock" id="combineStockCheckbox"><label>Combine With Stock</label></div>
                        <div class="checkbox-container"><input type="checkbox" name="gift"> <label>Gift</label></div>
                        <div class="checkbox-container"><input type="checkbox" name="voucher"> <label>Voucher</label></div>
                        <div class="checkbox-container"><input type="checkbox" name="search-with-product" id="searchWithProductCheckbox"> <label>Search With Product #</label></div>   
                    </div>

                    <div class="form-row">
                        <div class="form-group2">
                            <label for="itemcode">Barcode/Item Code:</label>
                            <input type="text" id="stock-itemcode" name="itemcode">
                        </div>

                        <div class="form-group2">
                            <label for="product-name">Product Name:</label>
                            <input type="text" id="stock-productname" name="product-name">
                        </div>

                        <div class="form-group2">
                            <label for="bulk-count">Bulk Count:</label>
                            <input type="number" id="stock-bulkcount" name="bulk-count" value=1>
                        </div>

                        <div class="form-group2" >
                            <label for="unit-count">Unit Count:</label>
                            <input type="number" id="stock-unitcount" name="unit-count" value=1>
                        </div>

                        <div class="form-group2">
                            <label for="purchase-qty">Total Quantity:</label>
                            <div id="qty-unit-group">
                                <input style="margin-top: -15px;" type="number" id="purchase-qty" name="purchase-qty" value=1 style="margin-top: 0px;">
                                <div class="form-group" id="unit-group" >
                                    <select id="unit-select" name="unit">
                                        
                                    </select>
                                    <!-- Plus Button -->
                                    <button type="button" id="add-unit-btn">+</button>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="form-row" style="margin-top:-20px;">
                        <div class="form-group2">
                            <label for="cost-price">Cost Price:</label>
                            <input type="text" id="cost-price" name="cost-price">
                        </div>

                        <div class="form-group2">
                            <label for="wholesale-price">Wholesale Price:</label>
                            <input type="text" id="wholesale-price" name="wholesale-price">
                        </div>

                        <div class="form-group2">
                            <label for="max-retail-price">Maximum Retail Price:</label>
                            <input type="text" id="max-retail-price" name="max-retail-price">
                        </div>

                        <div class="form-group2">
                            <label for="super-customer-genuine-price">Super Customer Price:</label>
                            <input type="text" id="super-customer-genuine-price" name="super-customer-genuine-price">
                        </div>
                        
                        <div class="form-group2">
                            <label for="our-price">Our Price:</label>
                            <input type="text" id="our-price" name="our-price">
                        </div>


                    </div>

                    <!-- Discount and Profit -->
                        <div class="form-row"  style="margin-top:-8px;">

                            <div class="form-group2">
                                <label for="discount-percent">Discount %:</label>
                                <input type="text" id="discount-percent" name="discount-percent">
                                <span id="discount-txt">Value : <span id="discount-value">0.00</span></span>    
                            </div>
                            <div class="form-group2">
                                <label for="expire-date">Expire Date:</label>
                                <input type="date" id="expire-date" name="expire-date">
                            </div>
                            <div class="form-group2">
                                <label for="low-stock">Low Stock:</label>
                                <input type="text" id="low-stock" name="low-stock">
                            </div>


                        </div>

                        <div class="form-group-row">
                            <span id="net-amount-text">Net Amount : <span id="net-amount">0.00</span></span>
                            
                            <div id="profit">
                                <span id="profit-percent-txt">Profit(%) : <span class="profit-numbers" id="profit-percentage">0.00%</span></span>
                                <span id="unit-profit-value-txt">Unit Profit Value : <span class="profit-numbers" id="unit-profit-value">0.00</span></span>
                                <span id="profit-value-txt">Stock Profit Value : <span class="profit-numbers" id="profit-value">0.00</span></span>
                            </div>

                            <span id="unit-cost-text">Unit Cost : <span id="unit-cost">0.00</span></span>
                        </div>

                        <div class="success-error-msg" id="error-message-stock" style="display:none; background-color:#ffcccb; color:#d8000c; padding:10px; border-radius:5px; margin-top:10px;"></div>
                        <div class="success-error-msg" id="success-message-stock" style="display:none; background-color:#d4edda; color:#155724; padding:10px; border-radius:5px; margin-top:10px;"></div>
                    <div id="stock-buttons">
                        <button type="button" id="save-stock"><i class="fa fa-save save-icon"></i>Save</button>
                        <button type="button" id="print-barcode-show-stock"><i class="fa fa-print print-icon"></i>Print Barcode</button>
                    </div>
                    <input type="text" class="eland-input" id="current_branch_stock" name="current_branch_stock" value="<?php echo $user_branch?>" hidden>
                </form>

                <table class="search-stock-table">
                    <thead>
                        <tr>
                            <th>Stock ID</th>
                            <th>Barcode</th>
                            <th>Product</th>
                            <th>Cost Price</th>
                            <th>Wholesale Price</th>
                            <th>Maximum Retail Price</th>
                            <th>Super Customer Price</th>
                            <th>Our Price</th>
                            <th>Qty</th>
                            <th>Available Stock</th>
                            <th>Discount (%)</th>
                            <th>Discount value</th>
                            <!-- <th>Free</th> -->
                            <th>Total Amount</th>
                        </tr>
                        
                    </thead>
                    <tbody id="searchStockTableBody">
                        
                    </tbody>
                </table>
                

                <!-- <h3 id="all-stocks-text">All Stocks</h3>
                <table class="search-stock-table">
                
                    <thead>
                        <tr>
                            <th>Stock ID</th>
                            <th>Item Code</th>
                            <th>Product</th> -->
                            <!-- <th>Cost Price</th> -->
                            <!-- <th>Maximum Retail Price</th>
                            <th>Wholesale Price</th>
                            <th>Super Customer Price</th>
                            <th>Our Price</th>
                            <th>Quantity</th>
                            <th>Available Stock</th> -->
                            <!-- <th>Discount (%)</th>
                            <th>Discount value</th> -->
                            <!-- <th>Free</th> -->
                            <!-- <th>Total Amount</th> -->
                        <!-- </tr>
                        
                    </thead>
                    <tbody id="allStockTableBody">
                        
                    </tbody>
                </table> -->
            </div>
        </div>
        <div class="footer">
            <div class="function-btn-cont">
                <!-- <img src="../../assets/images/excel_icon.png" alt="Export to Excel">
                <img src="../../assets/images/calculator.png" alt="Calculator">
                <img src="../../assets/images/icon-printer-1.jpg" alt="Print"> -->
                <button onclick="location.href='../../controllers/logout.php';" class="button-style">
                    <i class="fa-solid fa-file-excel"></i>
                    <span>Export</span>
                </button>
                <button onclick="location.href='../../controllers/logout.php';" class="button-style">
                    <i class="fa-solid fa-calculator"></i>
                    <span>Calculator</span>
                </button>
                <button onclick="location.href='../print-dashboard/print_dashboard.php';" class="button-style">
                    <i class="fa-solid fa-print"></i>
                    <span>Print</span>
                </button>
            </div>
            <div class="footer-balances">
                <div style=" padding:5px;">
                    <!-- <div style=" padding:5px;">Outstandings</div>
                    <div style=" padding:5px;">Credit Balance</div>
                    <div style=" padding:5px;">Total Balance</div> -->
                    <div style=" padding:5px;">Outstanding Credit Balance</div>
                </div>

                <div style="margin-left:10px; padding:5px;">
                    <div style=" padding:5px;" class="footer-balance-value">:  0.00</div>
                    <!-- <div style=" padding:5px;" class="footer-balance-value">:  0.00</div>
                    <div style=" padding:5px;" class="footer-total-balance">:  0.00</div> -->
                </div>
            </div>

            <div class="footer-amounts">
                <div style="padding:5px;">
                    <div style=" padding:5px;">New Invoice Total</div>
                    <div style=" padding:5px;">Supplier Return Amount</div>
                    <div style=" padding:5px;">Supplier Payment Amount</div>
                </div>

                <div style="margin-left:10px; padding:5px;"> 
                    <div style=" padding:5px;" class="footer-amount-value" id="footer-total-amount-value">:  0.00</div>
                    <div style=" padding:5px;" class="footer-amount-value" id="footer-return-amount-value">:  0.00</div>
                    <div style=" padding:5px;" class="footer-amount-value" id="footer-supplier-payment-amount-value">:  0.00</div>
                </div>
            </div>

            <div class="footer-actions">
                <button class="footer-pay-button"><i class="fas fa-wallet payment-icon"></i>PAY NOW</button>
            </div>
        </div>
    </div>
    

 

    <!-- Modal for All Products -->
    <div id="all-products-modal" class="modal">
        <div class="all-products-modal-content">
            <span class="all-products-modal-close">&times;</span>
            <h3>All Products</h3>
            <div class="all-products-scrollable-table">
                <table>
                    <thead>
                        <tr>
                            <th>BarCode</th>
                            <th>Product Name</th>
                            <th>Product Sinhala Name</th>
                            <th>Category</th>
                            <th>Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="modal-product-list">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Unit Management Modal -->
    <div id="unit-modal" class="unit-modal">
        <div id="unit-container">
            <div class="modal-header">
                <h3>Unit</h3>
                <span id="close-unit-modal" class="close">&times;</span>
            </div>

            <div id="unit-sections">
                <!-- Left Section: Scrollable Table for Units -->
                <div id="unit-left-section">
                    <div class="unit-table-container">
                        <table id="unit-table">
                            <thead>
                                <tr>
                                    <th>Unit Type</th>    
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Units will be dynamically populated here -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Buttons below table -->
                    <div id="unit-table-buttons">
                        <button id="update-unit" class="unit-button">Update Unit</button>
                        <button id="hide-unit" class="unit-button">Hide Unit</button>
                    </div>
                </div>

                <!-- Right Section: New Unit Form -->
                <div id="unit-right-section">
                    <div id="new-unit-section">
                        <label for="new-unit-radio"><b>New Unit</b></label>

                        <div id="unit-form">
                            <input type="text" id="unit-name" class="unit-input-field" placeholder="Unit Name">
                        </div>
                    </div>

                    <!-- Save Button under the form -->
                    <div id="unit-save-button">
                        <button id="save-unit" class="unit-button">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Unit Modal -->
    <div id="update-unit-modal" class="update-unit-modal" style="display:none;">
        <div id="update-unit-container">
            <div class="modal-header">
                <h3>Update Unit</h3>
                <span id="close-unit-update-modal" class="close">&times;</span>
            </div>

            <div id="update-unit-content">
                <div id="existing-unit-fields">
                    <!-- Existing Name and Key -->
                    <div class="form-group">
                        <label for="existing-unit-name">Existing Name:</label>
                        <input type="text" id="existing-unit-name" class="unit-input-field" disabled>
                    </div>
                </div>
                
                <!-- New Name and Key Input Fields -->
                <div class="form-group">
                    <label for="update-unit-name">New Name:</label>
                    <input type="text" id="update-unit-name" class="unit-input-field" placeholder="Enter New Name">
                </div>

            </div>

            <!-- Update Button -->
            <div id="update-unit-buttons">
                <button id="confirm-update-unit" class="unit-button">Update</button>
            </div>
        </div>
    </div>

    
    <!-- Barcode Display -->
    <div id="barcode-container">
        <h4>Generated Barcode</h4>
        <svg id="barcode"></svg>
        <button class="btn btn-secondary mt-3" onclick="window.print()">Print Barcode</button>
    </div>


    <!-- Add Supplier Modal -->
    <div id="supplier-modal" class="modal">
        <div class="supplier-modal-content">
            <!-- Close Button -->
            <span class="close" id="close-supplier-modal">&times;</span>
            
            <!-- Supplier Form Section -->
            <div id="supplier-form-section">
                <h3>Register Suppliers</h3>
                <form id="supplier-form">
                    <div>
                        <label for="supplier-name" class="supplier-form-lable">Supplier Name:</label>
                        <input type="text" id="supplier-name" required>
                    </div>
                    <div>
                        <label for="telephone-no" class="supplier-form-lable">Telephone No#:</label>
                        <input type="text" id="telephone-no">
                    </div>
                    <div>
                        <label for="company" class="supplier-form-lable">Company:</label>
                        <input type="text" id="company" required>
                    </div>
                    <div>
                        <button type="button" id="save-supplier">Save</button>
                    </div>
                </form>
                
                <!-- Additional Buttons -->
                <!-- <div id="extra-buttons">
                    <button id="area-manager-details" class="extra-btn">Area Manager Details</button>
                    <button id="agent-details" class="extra-btn">Agent Details</button>
                    <button id="ref-details" class="extra-btn">Ref Details</button>
                </div> -->
            </div>

            <!-- Supplier Table Section -->
            <div id="supplier-table-section">
                <div id="supplier-table-container">
                    <table id="supplier-table">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Supplier Name</th>
                                <th>Telephone No</th>
                                <th>Company</th>
                                <th>Credit Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be dynamically populated -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Table Control Buttons -->
                <div id="supplier-table-buttons">
                    <button id="hide-details">Hide Details</button>
                    <button id="update-supplier">Update Name</button>
                    <button id="select-supplier">Select</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Supplier Modal -->
    <div id="update-supplier-modal" class="update-supplier-modal" style="display:none;">
        <div id="update-supplier-container">
            <div class="modal-header">
                <h3>Update Supplier</h3>
                <span id="close-supplier-update-modal" class="close">&times;</span>
            </div>

            <div id="update-supplier-content">
                <div id="existing-supplier-column">
                    <!-- Existing Name and Key -->
                    <div class="form-group">
                        <label for="existing-supplier-id">Supplier ID:</label>
                        <input type="text" id="existing-supplier-id" class="sup-input-field" disabled>
                    </div>
                    <div class="form-group">
                        <label for="existing-supplier-name">Existing Supplier Name:</label>
                        <input type="text" id="existing-supplier-name" class="sup-input-field" disabled>
                    </div>
                    <div class="form-group">
                        <label for="existing-supplier-phone">Existing Phone No:</label>
                        <input type="text" id="existing-supplier-phone" class="sup-input-field" disabled>
                    </div>
                    <div class="form-group">
                        <label for="existing-supplier-company">Existing Company Name:</label>
                        <input type="text" id="existing-supplier-company" class="sup-input-field" disabled>
                    </div>         
                </div>
                
                <div id="new-supplier-column">
                    <!-- New Name and Key Input Fields -->
                    <div class="form-group">
                        <label for="update-supplier-name">New Supplier Name:</label>
                        <input type="text" id="update-supplier-name" class="sup-input-field" placeholder="Enter New Name">
                    </div>
                    <div class="form-group">
                        <label for="update-supplier-phone">New Phone No:</label>
                        <input type="text" id="update-supplier-phone" class="sup-input-field" placeholder="Enter New Phone No">
                    </div>
                    <div class="form-group">
                        <label for="update-supplier-company">New Company Name:</label>
                        <input type="text" id="update-supplier-company" class="sup-input-field" placeholder="Enter New Company">
                    </div>
                    
                </div>
            </div>

            <!-- Update Button -->
            <div id="update-supplier-buttons">
                <button id="confirm-update-supplier" class="sup-button">Update</button>
            </div>
        </div>
    </div>


    <!-- Modal for displaying stock details -->
    <div id="stock-modal" class="modal">
        <div class="stock-modal-content">
            <div>
                <span class="close" id="stock-close-modal">&times;</span>
                <h2>Barcode Print</h2>
            </div>

            <div id="stock-header">
                <div id="stock-search-bar">
                    <label for="stock-search">Search By:</label>
                    <select id="stock-search-by">
                        <option value="all">All</option>
                        <option value="stock_id">Stock No</option>
                        <option value="product_id">Product No</option>
                        <option value="product_name">Product Name</option>
                    </select>
                    <input type="text" id="stock-search-input" placeholder="Search..."> 
                </div>
                <div class="stock-print-barcode-btn-container">
                    <button id="stock-print-barcode-btn">Print Barcode</button>
                </div>
            </div>    

            <div class="print-barcode-stock-table-container">
                <table id="print-barcode-stock-table">
                    <thead>
                        <tr>
                            <th>Product No</th>
                            <th>Stock No</th>
                            <th>Barcode</th>
                            <th>Name</th>
                            <th>Buy Price</th>
                            <th>Buy Qty</th>
                            <th>Min Price</th>
                            <th>Max Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Table rows will be dynamically injected here -->
                    </tbody>
                </table>
            </div>

            
        </div>
    </div>


    <!-- Barcode Modal Overlay -->
    <div class="barcode-modal-overlay" id="barcode-modal">
        <div class="barcode-modal-content">
            
            <div>
                <span class="close" id="barcode-close-modal">&times;</span>
                <div class="barcode-modal-header">Barcode Print</div> 
            </div>
            <div class="barcode-modal-body">
                <!-- Barcode Preview -->
                <div class="barcode-preview">
                    <canvas id="barcodeCanvas">
                        
                    </canvas>
                
                </div>

                <!-- Form Inputs -->
                <form id="barcodeForm">
                    <div class="barcode-form">
                        <div>
                            <label for="barcodeID">Barcode</label>
                            <input type="text" id="barcodeID" placeholder="Enter Barcode" readonly>
                        </div>
                        <div>
                            <label for="stockID">Stock ID</label>
                            <input type="text" id="stockID" placeholder="Enter Stock ID" readonly>
                        </div>
                        <div>
                            <label for="productID">Product ID</label>
                            <input type="text" id="productID" placeholder="Enter Product ID" readonly>
                        </div>
                        <div>
                            <label for="productName">Product Name</label>
                            <input type="text" id="productName" placeholder="Enter Product Name">
                        </div>
                        <div>
                            <label for="mfDate">MF Date</label>
                            <input type="date" id="mfDate">
                        </div>
                        <div>
                            <label for="expDate">Expire Date</label>
                            <input type="date" id="expDate">
                        </div>
                        <div>
                            <label for="printPrice">Print Price</label>
                            <input type="number" id="printPrice" placeholder="Enter Price">
                        </div>
                        <div>
                            <label for="printQty">Print Qty</label>
                            <input type="number" id="printQty" placeholder="Enter Quantity">
                        </div> 
                        <div>
                            <button type="button" id="generateStockBarcode" class="print-form-button">Generate</button>
                        </div> 
                        <div>
                            <button type="button" id="printBarcode" class="print-form-button">Print</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Modal for Combine With Stock -->
    <div class="modal" id="mergeItemsModal" style="display: none;">
        <div class="merge-items-modal-content">
            <div class="merge-items-modal-header">
                <h2>Item to Item Discount</h2>
                <button class="close" id="mergeItemsCloseModalButton">&times;</button>
            </div>
            <div class="merge-items-modal-body">             
                <div class="merge-items-form-group">
                    <label for="autoItemCode">Item Code</label>
                    <input type="text" id="autoItemCode" placeholder="Auto No" />
                </div>

                <div id="merge-items-flex">
                    <div>
                        <div class="merge-items-section-header">Mother Item</div>
                        <div class="merge-items-form-group">
                            <label for="motherItem">Mother Item</label>
                            <input type="text" id="motherItem" placeholder="Enter mother item" />
                        </div>
                        <div class="merge-items-form-group">
                            <label for="motherDescription">Description</label>
                            <input type="text" id="motherDescription" placeholder="Enter description" />
                        </div>
                        <div class="merge-items-form-group">
                            <label for="motherQty">Qty</label>
                            <input type="number" id="motherQty" placeholder="Enter quantity" />
                        </div>
                    </div>
                    <div>
                        <div class="merge-items-section-header">Child Item</div>
                        <div class="merge-items-form-group">
                            <label for="childItem">Child Item</label>
                            <input type="text" id="childItem" placeholder="Enter child item" />
                        </div>
                        <div class="merge-items-form-group">
                            <label for="childDescription">Description</label>
                            <input type="text" id="childDescription" placeholder="Enter description" />
                        </div>
                        <div class="merge-items-form-group">
                            <label for="childQty">Qty</label>
                            <input type="number" id="childQty" placeholder="Enter quantity" />
                        </div>
                    </div>
                </div>

                <div class="merge-items-button-group">
                    <button id="mergeItemsSaveButton">Save</button>
                    <button id="mergeItemsCloseButton">Exit</button>
                </div>
                
            </div>
        </div>
    </div>



    <!-- pay supplier modal -->
    <div class="modal" id="payNowModal">
        <div class="pay-now-modal-content">
            <div class="pay-now-modal-header">
                <h2>Pay to Supplier</h2>
                <button id="payNowCloseModal" class="close-button" >&times;</button>
            </div>
            <div class="pay-now-modal-body">
                <div class="pay-now-sidebar">
                    <img style="width:120px; margin-top:-20px; margin-left:20px; margin-right:40px;" src="../../assets/images/supplier.png"></img>
                    <div class="outstanding">
                        <h3>Outstanding : </h3>
                        <p id="outstandingAmount">0.00</p>
                    </div>

                    <div id="selectedSupplierDetails" style="display:none;">
                        <div class="selected-supplier">
                            <h3>Selected Supplier : </h3>
                            <p id="selectedSupplierName"></p>
                            <p id="selectedSupplierId" hidden></p>
                        </div>
                        <div class="credit-details">
                            <h3>Credit Balance : </h3>
                            <p id="selectedSupplierCredit"></p>
                        </div>
                    </div>
                </div>
                <div style="margin-top:-20px;">
                    <div class="pay-now-supplier-details">
                        <table class="pay-now-supplier-table" id="pay-now-supplier-table">
                            <thead>
                            <tr>
                                <th>Supplier No</th>
                                <th>Supplier Name</th>
                                <th>Telephone</th>
                                <th>Company</th>
                                <th>Credit Balance</th>
                            </tr>
                            </thead>
                            <tbody id="paySupplierTableBody">
                            
                            </tbody>
                        </table>
                    </div>

                    <div class="pay-supplier-details-section">
                        <div class="pay-supplier-stocks">
                            <h3>Stocks</h3>
                            <div class="stock-payment-table-container" id="stock-table-container">
                                <table class="pay-supplier-stocks-table">
                                    <thead>
                                        <tr>
                                            <th>Stock</th>
                                            <th>Date</th>
                                            <th>Item Code</th>
                                            <th>Product Name</th> 
                                            <th>Qty</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody id="paySupplierStocksTableBody">
                                        <!-- Table rows will go here -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="total-amount">
                                <strong>Total Amount:</strong> <span id="stocksTotalAmount">0</span>
                            </div>
                        </div>

                        <div class="supplier-payments">
                            <h3>Payments</h3>
                            <div class="stock-payment-table-container" id="payment-table-container">
                                <table class="supplier-payments-table">
                                    <thead>
                                        <tr>
                                            <th>Payment No</th>
                                            <th>Type</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="supplierPaymentsTableBody">
                                        <!-- Table rows will go here -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="total-amount">
                                <strong>Total Amount:</strong> <span id="paymentsTotalAmount">0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <!-- <button class="delete-payment-button">Delete Payment</button> -->
                <button class="confirm-pay-now-button" id="confirm-pay-now-button">Pay Now</button>
            </div>
        </div>
    </div>



    <!-- payment methods -->
    <div class="modal" id="payment-modal">
        <div id="payment-modal-content">
            <div class="payment-modal-header">
                <h3>Create Payment to Supplier </h3>
                <button id="paymentCloseModal" class="close-button" style="margin-top:-15px;">&times;</button>                
            </div>

            <div style="display: flex;">
                <div class="selected-supplier-form-group">
                    <label for="payment-supplier-id">Supplier ID :</label>
                    <input type="text" id="payment-supplier-id" value="N/A" disabled />
                </div>
                <div class="selected-supplier-form-group">
                    <label for="payment-supplier-name">Supplier Name :</label>
                    <input type="text" id="payment-supplier-name" value="N/A" disabled />
                </div>
                <div class="selected-supplier-form-group">
                    <label for="payment-credit-balance">Credit Balance :</label>
                    <input type="text" id="payment-credit-balance" value="0.00" disabled />
                </div>
                <button type="button" class="add-bank-btn" onclick="addBank()">
                    <i class="fas fa-university" style="margin-right: 8px;"></i>Set Banks
                </button>
            </div>

            <form id="payment-form">
                    <div class="payment-radio-buttons">
                        <label>
                            <input type="radio" name="payment-type" value="single" id="single-payment" checked> Single Payment
                        </label>
                        <label>
                            <input type="radio" name="payment-type" value="multiple" id="multiple-payment"> Multiple Payment
                        </label>
                    </div>
       
                    <div class="blue-box" id="blue-box" style="display: flex; flex-direction: column; align-items: center; justify-content: center; background-color: rgb(236, 236, 236); padding: 20px; width: fit-content; border-radius: 5px; margin: 0 auto;">
                        <div class="payment-form-group" id="payment-method-group">
                            <label for="payment-method">Select Payment Method</label>
                            <select id="payment-method">
                                <option value="">-- Select Method --</option>
                                <option value="cash">Cash</option>
                                <option value="cheque">Cheque</option>
                                <option value="online">Online</option>
                            </select>
                        </div>
                        <div id="payment-details"></div>
                    </div>

                    <form id="multi-payment-form" style="margin-top:20px;">
                        <div id="payment-container" style="justify-content: center;">
                            <div class="multi-payment-box">
                                <div class="multi-payment-form-group" id="multi-payment-method-group" >
                                    <label for="multi-payment-method-0">Select Payment Method</label>
                                    <select id="multi-payment-method-0" class="multi-payment-method" data-index="0">
                                        <option value="">-- Select Method--</option>
                                        <option value="cash">Cash</option>
                                        <option value="cheque">Cheque</option>
                                        <option value="online">Online</option>
                                    </select>
                                </div>
                                <div id="multi-payment-details-0" class="multi-payment-details"></div>
                                <button type="button" class="delete-payment-row" data-index="0" style="margin-top: 10px;">
                                    <i class="fa fa-trash delete-icon delete-payment-row-icon"></i>
                                </button>
                            </div>
                        </div>                    


                        <div style="display:flex; justify-content: center; align-items: center; gap: 10px; margin-top:20px;">
                            <button type="submit" id="pay-print-button" class="pay-print-button" style="display:none;">
                                <i class="fas fa-print"></i>Pay & Print Receipt
                            </button>
                        
                            <button type="button" id="add-payment" style="display:none;">
                                <b><i class="fas fa-plus"></i></b>
                            </button>
                        <div>
                    </form>
            </form>
            
        </div>
    </div>

    <!-- Modal Structure -->
    <div id="bank-popup" class="modal" style="display: none;">
        <div class="bank-popup-content">
            <div class="payment-modal-header">
                <h2>Banks Setup</h2>
                <button id="bankCloseModal" class="close-button" style="margin-top:-15px;" onclick="closeBankPopup()">&times;</button>                
            </div>
            
            <div class="bank-tabs">
                <button id="tab-bank-details" class="active">Bank Details</button>
                <!-- <button id="tab-branches-details" onclick="showTab('branches-details')">Bank Branches Details</button> -->
            </div>
            <div id="bank-details" class="bank-tab-content">
                <label for="bank-code">Bank Code:</label>
                <input type="text" id="bank-code" placeholder="Enter bank code">
                <!-- <button id="refresh-bank-code" title="Refresh Bank Code">🔄</button> -->
                <br>
                <label for="bank-name">Name:</label>
                <input type="text" id="bank-name" placeholder="Enter bank name">
                <br>
                <!-- <label><input type="checkbox" id="inactive-bank"> Inactive</label> -->

                <button onclick="saveBank()">Save</button>
                <button onclick="closeBankPopup()">Close</button>

            </div>
            <!-- <div id="branches-details" class="tab-content" style="display: none;">
                <p>Branch details content will go here...</p>
            </div> -->

            <div id="all-bank-details">
                <table id="bank-details-table">
                    <thead>
                        <tr>
                            <th>BANK CODE</th>
                            <th>BANK NAME</th>
                        </tr>
                    </thead>
                    <tbody id="bank-list">

                    </tbody>
                </table>
            </div>
        </div>
    </div>


   

    <script src="../../assets/js/inventory_script.js"></script>
</body>
</html>

<script>
    document.addEventListener("keydown", function (event) {
        if (event.code === "Home") {
            window.location.href = "../dashboard/index.php";
        }
    });
</script>
