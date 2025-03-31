<?php
require_once 'connection_db.php';

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    if ($_GET['action'] === 'getProduct') {
        $item_code = $_GET['item_code'] ?? '';
        
        if (empty($item_code)) {
            echo json_encode(['success' => false, 'message' => 'Item code is required']);
            exit;
        }
        
        $stmt = $conn->prepare("SELECT product_name FROM products WHERE item_code = ?");
        $stmt->bind_param("s", $item_code);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        
        echo json_encode($product ? 
            ['success' => true, 'product_name' => $product['product_name']] : 
            ['success' => false, 'message' => 'Product not found']
        );
        exit;
    }
    
    if ($_GET['action'] === 'saveConversion') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            exit;
        }
        
        try {
            // Check if record exists
            $check_stmt = $conn->prepare("SELECT id FROM measurement_conversions WHERE item_code = ?");
            $check_stmt->bind_param("s", $data['item_code']);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Update existing record
                $stmt = $conn->prepare("UPDATE measurement_conversions 
                    SET product_name = ?, selling_bottle = ?, selling_litre = ? 
                    WHERE item_code = ?");
                $stmt->bind_param("sdds", 
                    $data['product_name'],
                    $data['kilo_to_bottle'],
                    $data['kilo_to_litter'],
                    $data['item_code']
                );
            } else {
                // Insert new record
                $stmt = $conn->prepare("INSERT INTO measurement_conversions 
                    (item_code, product_name, selling_bottle, selling_litre) 
                    VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssdd", 
                    $data['item_code'],
                    $data['product_name'],
                    $data['kilo_to_bottle'],
                    $data['kilo_to_litter']
                );
            }
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error executing query']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($_GET['action'] === 'getConversions') {
        $query = "SELECT * FROM measurement_conversions ORDER BY id DESC";
        $result = $conn->query($query);
        $conversions = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $conversions[] = $row;
            }
            echo json_encode(['success' => true, 'data' => $conversions]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error fetching conversions']);
        }
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Entries Management</title>
    <link rel="stylesheet" href="../../assets/css/stock.css">
    <link rel="stylesheet" href="create-invoice.styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>


    <div class="header-container-rmaster">
        <div class="text-partition-cont">
            <!-- first content row -->
            <div class="content-row">

                <div class="timer-container">
                    <span id="current-date">November 21, 2024</span>
                    <span id="current-time">7:28:50 AM</span>
                </div>

                <div class="company-info">
                    <!-- <span class="heading-sinhala">එග්ලන්ඩ් සුපර්</span> -->
                    <span class="heading-english">Ameena Chilies</span>
                    <span class="company-motto">The Best Spicy</span>
                </div>
            </div>
        </div>
    </div>

    <h1>Manage Stock Entries</h1>
    <div id="searchWrapper">
        <label for="searchInput">🔍 Search:</label>
        <input type="text" id="searchInput" placeholder="Type product name or barcode">
    </div>
    <br>
    <div id="promotionSection">
        <div id="promoButtonContainer">
            <button id="prodMgr_openBtn" class="prodMgr_btn">
                <i class="fas fa-cog"></i> Manage Conversions
            </button>
        </div>
        <a href="../stocks/stock_transfer.php" >
            <button class="prodMgr_btn">
                <i class="fas fa-exchange-alt"></i> Transfer stocks
            </button>
        </a>
        <div id="buttonContainer">
            <button id="addDealBtn">
                <i class="fas fa-plus-circle"></i> Add Deal
            </button>
        </div>
        <div id="promoButtonContainer">
            <button id="addPromoBtn">
                <i class="fas fa-plus-circle"></i> Add Free Item Promotions
            </button>
        </div>
        <div id="promoButtonContainer">
            <button id="addDiscountPromoBtn">
                <i class="fas fa-plus-circle"></i> Add Item Discount Promotions
            </button>
        </div>
        <div id="promoButtonContainer">
            <button id="addSupplierPromoBtn">
                <i class="fas fa-plus-circle"></i> Add Supplier Promotions
            </button>
        </div>
        <!-- <a href="../stocks/stock_transfer.php">
            <button id="addSupplierPromoBtn" style="background-color:#313132;">
                <i class="fas fa-exchange-alt"></i> Transfer stocks
            </button>
        </a> -->

        <div id="dealModal" class="modal" style="display: none;">
            <div class="modal-content">
                <h3>Add a Promotion Deal</h3>
                <form id="dealForm">
                    <label for="productDropdown">Select Product:</label>
                    <select id="productDropdown">
                        <!-- Options will be populated dynamically -->
                    </select>

                    <label for="barcodeField">Barcode:</label>
                    <input type="text" id="barcodeField" >

                    <label for="dealPrice">Deal Price:</label>
                    <input type="number" id="dealPrice" step="0.01" required>

                    <label for="startDate">Start Date:</label>
                    <input type="date" id="startDate" required>

                    <label for="endDate">End Date:</label>
                    <input type="date" id="endDate" required>

                    <button type="submit">Save Deal</button>
                    <button type="button" onclick="closeDealModal()">Cancel</button>
                </form>
            </div>
        </div>

        <div id="promotionModal" style="display: none;">
            <button id="promotionModalCloseBtn" class="close-button" onclick="closePromotionModal()">&times;</button>
            <div id="promotion-modal-content">

                <div id="add-promotion-form-container">
                    <h3>Add Free Item Promotion</h3>
                    <form id="promotionForm">
                        <label for="promoBarcode">Product Barcode:</label>
                        <input type="text" id="promoBarcode" required>

                        <label for="buyAmount">Buy Quantity:</label>
                        <input type="number" id="buyAmount" required>

                        <label for="freeAmount">Free Quantity:</label>
                        <input type="number" id="freeAmount" required>

                        <div id="add-promotion-buttons">
                            <button type="submit" style="background-color:#2980b9;">Save</button>
                            <button type="button" onclick="closePromotionModal()" style="background-color:#c61c10;">Cancel</button>
                        </div>
                    </form>
                </div>

                <div style="background-color: white; display:flex; padding:5px 10px; border: 1px solid #ccc; border-radius:5px; padding-right:30px; gap:10px;">
                    <div id="distinctProducts">
                        <h3>Promotion Products</h3>
                        <div id="distinctProductsList">
                            <table id="productTable">
                                <thead>
                                    <tr>
                                        <th>Barcode</th>
                                        <th>Product Name</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be populated dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="promotionRecords">
                        <h3>Promotion Records</h3>
                        <div id="promotionRecordsList">
                            <table id="promotionTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Buy Quantity</th>
                                        <th>Free Quantity</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be populated dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="promotionDiscountModal" style="display: none;">
            <button id="promotionDiscountModalCloseBtn" class="close-button" onclick="closeDiscountPromotionModal()">&times;</button>
            <div id="promotion-discount-modal-content">

                <div id="add-discount-promotion-form-container">
                    <h3>Add Item Discount Promotion</h3>
                    <form id="promotionDiscountForm">
                        <label for="promoBarcode">Product Barcode:</label>
                        <input type="text" id="promoDiscountBarcode" required>

                        <label for="buyAmount">Buy Quantity:</label>
                        <input type="number" id="buyAmount2" required>

                        <div id="promotion-discount-radio-buttons">
                            <div class="discount-radio-box2">
                                <input type="radio" name="discountType2" value="percentage" onclick="toggleDiscountFields2()"><span>Discount Percentage</span>
                            </div>
                            <div class="discount-radio-box2">
                                <input type="radio" name="discountType2" value="value" onclick="toggleDiscountFields2()"><span style="margin-left: -4px;">Discount Value</span>
                            </div>
                        </div>

                        <!-- Discount Fields -->
                        <input type="number" id="discountPercentage2" placeholder="Enter Discount Percentage" name="discount_percentage" style="display: none;">
                        <input type="number" id="discountValue2" placeholder="Enter Discount Value" name="discount_value" style="display: none;">

                        <div id="add-discount-promotion-buttons">
                            <button type="submit" style="background-color:#2980b9;">Save</button>
                            <button type="button" onclick="closeDiscountPromotionModal()" style="background-color:#c61c10;">Cancel</button>
                        </div>
                    </form>
                </div>

                <div style="background-color: white; display:flex; padding:5px 10px; border: 1px solid #ccc; border-radius:5px; padding-right:30px; gap:10px;">
                    <div id="distinctDiscountProducts">
                        <h3>Promotion Products</h3>
                        <div id="distinctDiscountProductsList">
                            <table id="productDiscountTable">
                                <thead>
                                    <tr>
                                        <th>Barcode</th>
                                        <th>Product Name</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be populated dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="promotionDiscountRecords">
                        <h3>Promotion Records</h3>
                        <div id="promotionDiscountRecordsList">
                            <table id="promotionDiscountTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Buy Quantity</th>
                                        <th>Discount Percentage</th>
                                        <th>Discount Value</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be populated dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div id="promotionSupplierModal" style="display: none;">
            <button id="promotionSupplierModalCloseBtn" class="close-button" onclick="closeSupplierPromotionModal()">&times;</button>
            <div id="promotion-supplier-modal-content">
                <div id="add-promotion-supplier-form-container">
                    <h3>Add Supplier Promotion</h3>
                    <form id="promotionSupplierForm">
                        <!-- Supplier Selection -->
                        <label for="supplierDropdown">Select Supplier:</label>
                        <select id="supplierDropdown" size="5" required></select>
                        <input type="hidden" id="supplierName" name="supplier_name">

                        <label for="supplierId">Supplier ID:</label>
                        <input type="text" id="supplierId" name="supplier_id" readonly>

                        <!-- Promotion Dates -->
                        <label for="promoStartDate">Start Date:</label>
                        <input type="date" id="promoStartDate" name="start_date" required>

                        <label for="promoEndDate">End Date:</label>
                        <input type="date" id="promoEndDate" name="end_date" required>

                        <!-- Discount Type Selection -->
                        <div id="promotion-supplier-radio-buttons">
                            <div class="discount-radio-box">
                                <input type="radio" name="discountType" value="percentage" onclick="toggleDiscountFields()"><span>Discount Percentage</span>
                            </div>
                            <div class="discount-radio-box">
                                <input type="radio" name="discountType" value="value" onclick="toggleDiscountFields()"><span style="margin-left: -4px;">Discount Value</span>
                            </div>
                        </div>

                        <!-- Discount Fields -->
                        <input type="number" id="discountPercentage" placeholder="Enter Discount Percentage" name="discount_percentage" style="display: none;">
                        <input type="number" id="discountValue" placeholder="Enter Discount Value" name="discount_value" style="display: none;">

                        <div id="add-promotion-supplier-buttons">
                            <button type="submit" style="background-color:#2980b9;">Save</button>
                            <button type="button" onclick="closeSupplierPromotionModal()" style="background-color:#c61c10;">Cancel</button>
                        </div>
                    </form>
                </div>

                <!-- Supplier Table -->
                <div style="background-color: white; display: flex; padding: 15px 20px; border: 1px solid #ccc; border-radius: 5px; padding-right: 30px; gap: 10px;">
                    <div id="distinctSuppliers">
                        <h3>Supplier Promotion Records</h3>
                        <div id="distinctSuppliersList">
                            <table id="suppliersTable">
                                <thead>
                                    <tr>
                                        <th>Supplier ID</th>
                                        <th>Supplier Name</th>
                                        <th>Discount Percentage</th>
                                        <th>Discount Value</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be populated dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Manager Modal -->
        <div id="prodMgr_modal" class="prodMgr_modal">
            <div class="prodMgr_modalContent">
                <span class="prodMgr_close">&times;</span>
                <div class="prodMgr_layout">
                    <!-- Left side - Form -->
                    <div class="prodMgr_formSection">
                        <h2>Manage Conversions</h2>
                        
                        <div class="prodMgr_formGroup">
                            <label for="prodMgr_itemCode">Item Code</label>
                            <input type="text" id="prodMgr_itemCode" name="itemCode">
                        </div>
                        
                        <div class="prodMgr_formGroup">
                            <label for="prodMgr_productName">Product Name</label>
                            <input type="text" id="prodMgr_productName" name="productName" readonly>
                        </div>
                        
                        <div class="prodMgr_formGroup">
                            <label>Selling Type</label>
                            <div class="prodMgr_sellingTypeRow">
                                <input type="text" value="Kilo To Bottle" readonly>
                                <input type="number" id="prodMgr_kiloToBottle" step="0.01">
                            </div>
                            <div class="prodMgr_sellingTypeRow">
                                <input type="text" value="Kilo To Litter" readonly>
                                <input type="number" id="prodMgr_kiloToLitter" step="0.01">
                            </div>
                        </div>
                        
                        <div class="prodMgr_modalFooter">
                            <button id="prodMgr_btnSave" class="prodMgr_btnSave">Save</button>
                            <button id="prodMgr_btnExit" class="prodMgr_btnExit">Exit</button>
                        </div>
                    </div>

                    <!-- Right side - Data Display -->
                    <div class="prodMgr_dataSection">
                        <h3>Measurement Conversions</h3>
                        <div class="prodMgr_dataTable">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Item Code</th>
                                        <th>Product</th>
                                        <th>Bottle</th>
                                        <th>Litre</th>
                                    </tr>
                                </thead>
                                <tbody id="prodMgr_conversionData">
                                    <!-- Data will be populated here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div id="stockTableWrapper">
        <table id="stockTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Supplier Name</th>
                    <th>Product Name</th>
                    <th>Available Stock</th>
                    <th>Cost Price</th>
                    <th>Wholesale Price</th>
                    <th>Max Retail Price</th>
                    <th>Super Customer Price</th>
                    <th>Our Price</th>
                    <th>Low Stock</th>
                    <th>Expire Date</th>
                    <th>Discount Percent</th>
                    <th>Barcode</th>
                    <th>Free</th>
                    <th>Gift</th>
                    <th>Voucher</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data will be dynamically added here -->
            </tbody>
        </table>
    </div>

    <div id="pagination">
        <button id="prevPage" disabled>Previous</button>
        <span id="currentPage">1</span> / <span id="totalPages">1</span>
        <button id="nextPage">Next</button>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h2>Edit Stock Entry</h2>
            <form id="editForm">
                <div id="editFormBox">
                    <div>
                        <input type="hidden" id="editId">
                        <input type="hidden" id="editSupplierId">

                        <label for="editSupplierName">Supplier Name:</label>
                        <input type="text" id="editSupplierName" disabled>

                        <label for="editProductName">Product Name:</label>
                        <input type="text" id="editProductName" required>

                        <label for="editAvailableStock">Available Stock:</label>
                        <input type="number" id="editAvailableStock" required>

                        <label for="editCostPrice">Cost Price:</label>
                        <input type="text" id="editCostPrice" disabled>

                        <label for="editWholesalePrice">Wholesale Price:</label>
                        <input type="number" step="0.01" id="editWholesalePrice" required>

                        <label for="editMaxRetailPrice">Max Retail Price:</label>
                        <input type="number" step="0.01" id="editMaxRetailPrice" required>
                    </div>

                    <div>
                        <label for="editSuperCustomerPrice">Super Customer Price:</label>
                        <input type="number" step="0.01" id="editSuperCustomerPrice" required>

                        <label for="editOurPrice">Our Price:</label>
                        <input type="number" step="0.01" id="editOurPrice" required>

                        <label for="editLowStock">Low Stock:</label>
                        <input type="number" id="editLowStock" required>

                        <label for="editDiscountPercent">Discount Percent:</label>
                        <input type="number" step="0.01" id="editDiscountPercent" required>

                        <label for="editBarcode">Barcode:</label>
                        <input type="text" id="editBarcode">
                    </div>
                </div>
                <div style="display:flex; justify-content:space-between">
                    <button type="submit" style="background-color:#2980b9;"><i class="fas fa-save"></i>Save</button>
                    <button type="button" onclick="closeModal()" style="background-color:#c61c10;"><i class="fas fa-times"></i>Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            let currentPage = 1;
            const itemsPerPage = 10;

            function fetchStockEntries(page, searchQuery = '') {
                $.ajax({
                    url: '../../controllers/stockController.php',
                    method: 'GET',
                    data: {
                        action: 'getEntries',
                        page: page,
                        limit: itemsPerPage,
                        search: searchQuery
                    },
                    dataType: 'json',
                    success: function(data) {
                        const tableBody = $('#stockTable tbody');
                        tableBody.empty();

                        if (data.entries && data.entries.length > 0) {
                            data.entries.forEach(entry => {
                                const row = `
                                    <tr data-id="${entry.id}" data-supplier-id="${entry.supplier_id}">
                                        <td>${entry.id}</td>
                                        <td>${entry.supplier_name}</td>
                                        <td>${entry.product_name}</td>
                                        <td>${entry.available_stock}</td>
                                        <td>${entry.cost_price}</td>
                                        <td>${entry.wholesale_price}</td>
                                        <td>${entry.max_retail_price}</td>
                                        <td>${entry.super_customer_price}</td>
                                        <td>${entry.our_price}</td>
                                        <td>${entry.low_stock}</td>
                                        <td>${entry.expire_date}</td>
                                        <td>${entry.discount_percent}</td>
                                        <td>${entry.itemcode}</td>
                                        <td>${entry.free === '1' ? 'Yes' : 'No'}</td>
                                        <td>${entry.gift === '1' ? 'Yes' : 'No'}</td>
                                        <td>${entry.voucher === '1' ? 'Yes' : 'No'}</td>
                                        <td><button class="editBtn"><i class="fas fa-edit"></i></button></td>
                                    </tr>
                                `;
                                tableBody.append(row);
                            });
                        } else {
                            tableBody.append('<tr><td colspan="17">No data available</td></tr>');
                        }

                        // Update pagination
                        currentPage = data.currentPage;
                        $('#currentPage').text(currentPage);
                        $('#totalPages').text(data.totalPages);
                        $('#prevPage').prop('disabled', currentPage === 1);
                        $('#nextPage').prop('disabled', currentPage >= data.totalPages);
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to load stock entries', 'error');
                    }
                });
            }

            // Real-time search
            $('#searchInput').on('input', function() {
                const searchQuery = $(this).val().trim();
                fetchStockEntries(1, searchQuery); // Reset to page 1 when searching
            });

            // Pagination button handlers
            $('#prevPage').click(function() {
                if (currentPage > 1) {
                    fetchStockEntries(--currentPage, $('#searchInput').val().trim());
                }
            });

            $('#nextPage').click(function() {
                fetchStockEntries(++currentPage, $('#searchInput').val().trim());
            });

            // Fetch initial stock entries
            fetchStockEntries(currentPage);



            $(document).on('click', '.editBtn', function() {
                const row = $(this).closest('tr');

                // Populate modal with the selected row data
                $('#editId').val(row.data('id'));
                $('#editSupplierId').val(row.data('supplier-id')); // Add this line
                $('#editSupplierName').val(row.find('td:nth-child(2)').text());
                $('#editProductName').val(row.find('td:nth-child(3)').text());
                $('#editAvailableStock').val(row.find('td:nth-child(4)').text());
                $('#editCostPrice').val(row.find('td:nth-child(5)').text());
                $('#editWholesalePrice').val(row.find('td:nth-child(6)').text());
                $('#editMaxRetailPrice').val(row.find('td:nth-child(7)').text());
                $('#editSuperCustomerPrice').val(row.find('td:nth-child(8)').text());
                $('#editOurPrice').val(row.find('td:nth-child(9)').text());
                $('#editLowStock').val(row.find('td:nth-child(10)').text());
                // $('#editExpireDate').val(row.find('td:nth-child(11)').text());
                $('#editDiscountPercent').val(row.find('td:nth-child(12)').text());
                $('#editBarcode').val(row.find('td:nth-child(13)').text());
                // $('#editFree').prop('checked', row.find('td:nth-child(14)').text() === 'Yes');
                // $('#editGift').prop('checked', row.find('td:nth-child(15)').text() === 'Yes');
                // $('#editVoucher').prop('checked', row.find('td:nth-child(16)').text() === 'Yes');

                // Show the modal
                $('#editModal').show();
            });


            // Submit form handler
            // Submit form handler
            $('#editForm').submit(function(e) {
                e.preventDefault();

                // Collect form data
                const formData = {
                    action: 'updateEntry',
                    id: $('#editId').val(),
                    product_name: $('#editProductName').val(),
                    available_stock: $('#editAvailableStock').val(),
                    wholesale_price: $('#editWholesalePrice').val(),
                    max_retail_price: $('#editMaxRetailPrice').val(),
                    super_customer_price: $('#editSuperCustomerPrice').val(),
                    our_price: $('#editOurPrice').val(),
                    low_stock: $('#editLowStock').val(),
                    discount_percent: $('#editDiscountPercent').val(),
                    barcode: $('#editBarcode').val(),
                };

                // Perform AJAX request to update entry
                $.ajax({
                    url: '../../controllers/stockController.php',
                    method: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Fetch the updated entry from the server
                            $.ajax({
                                url: '../../controllers/stockController.php',
                                method: 'GET',
                                data: {
                                    action: 'getEntries',
                                    page: currentPage,
                                    limit: itemsPerPage,
                                    search: $('#searchInput').val().trim()
                                },
                                dataType: 'json',
                                success: function(data) {
                                    if (data.entries) {
                                        const updatedEntry = data.entries.find(entry => entry.id === parseInt(formData.id));
                                        if (updatedEntry) {
                                            // Find the row to update
                                            const row = $(`#stockTable tbody tr[data-id="${updatedEntry.id}"]`);

                                            // Update row content
                                            row.html(`
                                                <td>${updatedEntry.id}</td>
                                                <td>${updatedEntry.supplier_name}</td>
                                                <td>${updatedEntry.product_name}</td>
                                                <td>${updatedEntry.available_stock}</td>
                                                <td>${updatedEntry.cost_price}</td>
                                                <td>${updatedEntry.wholesale_price}</td>
                                                <td>${updatedEntry.max_retail_price}</td>
                                                <td>${updatedEntry.super_customer_price}</td>
                                                <td>${updatedEntry.our_price}</td>
                                                <td>${updatedEntry.low_stock}</td>
                                                <td>${updatedEntry.expire_date}</td>
                                                <td>${updatedEntry.discount_percent}</td>
                                                <td>${updatedEntry.barcode}</td>
                                                <td>${updatedEntry.free === '1' ? 'Yes' : 'No'}</td>
                                                <td>${updatedEntry.gift === '1' ? 'Yes' : 'No'}</td>
                                                <td>${updatedEntry.voucher === '1' ? 'Yes' : 'No'}</td>
                                                <td><button class="editBtn">Edit</button></td>
                                            `);

                                            Swal.fire('Success', 'Stock entry updated successfully', 'success');
                                            $('#editModal').hide(); // Hide modal
                                        }
                                    }
                                },
                                error: function() {
                                    Swal.fire('Error', 'Failed to fetch updated data', 'error');
                                }
                            });
                        } else {
                            Swal.fire('Error', response.error || 'Failed to update stock entry', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Unable to communicate with the server', 'error');
                    }
                });
            });


            // Close modal
            window.closeModal = function() {
                $('#editModal').hide();
            };

            // // Pagination button handlers
            // $('#prevPage').click(function () {
            //     if (currentPage > 1) {
            //         currentPage--;
            //         fetchStockEntries(currentPage);
            //     }
            // });

            // $('#nextPage').click(function () {
            //     currentPage++;
            //     fetchStockEntries(currentPage);
            // });

            // Fetch initial stock entries
            fetchStockEntries(currentPage);
        });



        $(document).ready(function() {
            // Open Modal
            $('#addDealBtn').click(function() {
                $('#dealModal').show();
                fetchProducts();
            });

            // Fetch Products for Dropdown
            function fetchProducts() {
                $.ajax({
                    url: '../../controllers/stockController.php',
                    method: 'GET',
                    data: {
                        action: 'fetchProducts'
                    },
                    dataType: 'json',
                    success: function(products) {
                        const dropdown = $('#productDropdown');
                        dropdown.empty().append('<option value="">Select Product</option>');
                        products.forEach(product => {
                            dropdown.append(
                                $('<option></option>')
                                .val(product.product_name)
                                .text(product.product_name)
                                .data('barcode', product.barcode)
                            );
                        });
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to load products', 'error');
                    }
                });
            }

            // Auto-fill Barcode and Product on Input
            $('#barcodeField').on('input', function() {
                const barcode = $(this).val().trim();
                const $options = $('#productDropdown option');

                // Find matching barcode
                const $matchedOption = $options.filter(function() {
                    return $(this).data('barcode') === barcode;
                });

                if ($matchedOption.length > 0) {
                    $('#productDropdown').val($matchedOption.val());
                }
            });

            // Auto-fill Barcode when Product is Selected
            $('#productDropdown').change(function() {
                const selectedOption = $(this).find(':selected');
                $('#barcodeField').val(selectedOption.data('barcode'));
            });
            // Add Deal Form Submission
            $('#dealForm').submit(function(e) {
                console.log('req sent');

                e.preventDefault();
                // const dealData = {
                //     action: 'addDeal',
                //     product_name: $('#productDropdown').val(), // Send product_name instead of product_id
                //     deal_price: $('#dealPrice').val(),
                //     start_date: $('#startDate').val(),
                //     end_date: $('#endDate').val(),
                // };

                const dealData = new URLSearchParams();
                dealData.append('action', 'addDeal');
                dealData.append('product_name', $('#productDropdown option:selected').text());
                dealData.append('deal_price', $('#dealPrice').val());
                dealData.append('start_date', $('#startDate').val());
                dealData.append('end_date', $('#endDate').val());

                // $.ajax({
                //     url: '../../controllers/stockController.php',
                //     method: 'POST',
                //     data: dealData,
                //     dataType: 'json',
                //     success: function (response) {
                //         if (response.success) {
                //             Swal.fire('Success', 'Deal added successfully!', 'success');
                //             $('#dealModal').hide();
                //         } else {
                //             Swal.fire('Error', response.error, 'error');
                //         }
                //     }
                // });
                fetch('../../controllers/stockController.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: dealData.toString()
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log(data);

                        if (data.success) {
                            Swal.fire('Success', 'Deal added successfully!', 'success');
                            document.getElementById('dealModal').style.display = 'none';
                        } else {
                            Swal.fire('Error', data.error, 'error');
                        }
                    })
                    .catch(error => {
                        console.log('Error:', error);
                        Swal.fire('Error', 'An unexpected error occurred!', 'error');
                    });
            });


            // Close Modal
            window.closeDealModal = function() {
                $('#dealModal').hide();
            };

            // Auto-remove expired deals
            setInterval(function() {
                $.ajax({
                    url: '../../controllers/stockController.php',
                    method: 'POST',
                    data: {
                        action: 'removeExpiredDeals'
                    },
                    success: function() {
                        console.log('Expired deals removed');
                    }
                });
            }, 3600000); // Every 1 hour
        });

        let products = [];

        document.addEventListener("DOMContentLoaded", async () => {
            try {
                const response = await fetch("getAllProducts.php");
                const data = await response.json();
                if (data.error) {
                    console.error(data.error);
                    return;
                }
                products = data;
                populateDropdown(products);
            } catch (error) {
                console.error("Failed to fetch products:", error);
            }
        });


        function populateDropdown(products) {
            const productDropdown = document.getElementById("productDropdown");
            productDropdown.innerHTML = `<option value="">Select a product</option>`;

            products.forEach((product) => {
                const option = document.createElement("option");
                option.value = product.id;
                option.textContent = product.product_name;
                productDropdown.appendChild(option);
            });

            productDropdown.addEventListener("change", (event) => {
                const selectedProductId = event.target.value;
                const selectedProduct = products.find((product) => product.id == selectedProductId);

                if (selectedProduct) {
                    document.getElementById("barcodeField").value = selectedProduct.barcode;
                } else {
                    document.getElementById("barcodeField").value = "";
                }
            });
        }
        document.addEventListener('DOMContentLoaded', () => {
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });

        function updateDateTime() {
            const now = new Date();
            const timeElement = document.getElementById('current-time');
            timeElement.textContent = now.toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });

            const dateElement = document.getElementById('current-date');
            dateElement.textContent = now.toLocaleDateString('en-US', {
                month: 'long',
                day: 'numeric',
                year: 'numeric'
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });

        document.getElementById("addPromoBtn").addEventListener("click", function() {
            document.getElementById("promotionModal").style.display = "block";
            fetchDistinctProducts();
        });

        function closePromotionModal() {
            document.getElementById("promotionModal").style.display = "none";
        }

        document.getElementById("promotionForm").addEventListener("submit", function(event) {
            event.preventDefault(); // Prevent page reload

            let barcode = document.getElementById("promoBarcode").value;
            let buyAmount = document.getElementById("buyAmount").value;
            let freeAmount = document.getElementById("freeAmount").value;

            let formData = new FormData();
            formData.append("barcode", barcode);
            formData.append("buy_amount", buyAmount);
            formData.append("free_amount", freeAmount);

            fetch("save_promotion.php", {
                    method: "POST",
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Promotion saved successfully!");
                        document.getElementById("promotionForm").reset();
                        fetchDistinctProducts();
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(error => console.error("Error:", error));
        });


        function fetchDistinctProducts() {
            fetch("get_distinct_promo_products.php")
                .then(response => response.json())
                .then(data => {
                    let tableBody = document.querySelector("#productTable tbody");
                    tableBody.innerHTML = ""; // Clear existing rows

                    if (data.length === 0) {
                        tableBody.innerHTML = "<tr><td colspan='3'>No products found</td></tr>";
                    } else {
                        data.forEach(product => {
                            let row = document.createElement("tr");

                            row.innerHTML = `
                            <td>${product.barcode}</td>
                            <td>${product.product_name}</td>
                            <td>
                                <button onclick="deletePromoProduct('${product.barcode}')" style="background: none;">
                                    <i class="fas fa-trash-alt promo-delete-icon"></i>
                                </button>
                            </td>
                        `;
                            row.addEventListener("click", () => fetchPromotions(product.barcode));
                            tableBody.appendChild(row);
                        });
                    }
                })
                .catch(error => console.error("Error fetching products:", error));
        }

        function deletePromoProduct(barcode) {
            if (confirm("Are you sure you want to delete this product?")) {
                fetch("delete_promo_product.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: `barcode=${barcode}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert("Product deleted successfully!");
                            fetchDistinctProducts(); // Refresh the table
                        } else {
                            alert("Error: " + data.message);
                        }
                    })
                    .catch(error => console.error("Error deleting product:", error));
            }
        }

        function fetchPromotions(barcode) {
            fetch(`get_promotion_records.php?barcode=${barcode}`)
                .then(response => response.json())
                .then(data => {
                    let tableBody = document.querySelector("#promotionTable tbody");
                    tableBody.innerHTML = ""; // Clear existing rows

                    if (data.length === 0) {
                        tableBody.innerHTML = "<tr><td colspan='4'>No promotions found for this product</td></tr>";
                    } else {
                        data.forEach(promotion => {
                            let row = document.createElement("tr");

                            row.innerHTML = `
                                <td>${promotion.id}</td>
                                <td>${promotion.buy_quantity}</td>
                                <td>${promotion.free_quantity}</td>
                                <td>
                                    <button onclick="deletePromotion(${promotion.id})" style="background: none;">
                                        <i class="fas fa-trash-alt promo-delete-icon"></i>
                                    </button>
                                </td>
                            `;

                            tableBody.appendChild(row);
                        });
                    }
                })
                .catch(error => console.error("Error fetching promotions:", error));
        }

        function deletePromotion(id) {
            if (!confirm("Are you sure you want to delete this promotion?")) {
                return;
            }

            fetch("delete_promotion_record.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        id: id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Promotion deleted successfully!");
                        fetchDistinctProducts(); // Refresh product table
                        fetchPromotions(data.barcode); // Refresh promotions for that barcode
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(error => console.error("Error deleting promotion:", error));
        }

        const productTable = document.getElementById("productTable");
        const promotionTableBody = document.querySelector("#promotionTable tbody"); // Get the promotion table body

        productTable.addEventListener("click", (e) => {
            const rows = productTable.getElementsByTagName("tr");
            let isSelected = false;

            for (const row of rows) {
                row.classList.remove("selected");
            }

            const clickedRow = e.target.closest("tr");
            if (e.target.tagName === "TH" || e.target.closest("thead")) {
                if (clickedRow) clickedRow.classList.remove("selected");
            } else if (clickedRow && clickedRow.tagName === "TR") {
                clickedRow.classList.add("selected");
                isSelected = true;
                const barcode = clickedRow.cells[0].textContent; // Get the barcode from the first cell
                fetchPromotions(barcode); // Fetch promotions for the selected product
            }

            // If no row is selected, clear the promotions table
            if (!isSelected) {
                promotionTableBody.innerHTML = "";
            }
        });



        //Supplier Promotion
        document.getElementById("addSupplierPromoBtn").addEventListener("click", function() {
            document.getElementById("promotionSupplierModal").style.display = "block";
            fetchSupplierPromotions();
        });

        function closeSupplierPromotionModal() {
            document.getElementById("promotionSupplierModal").style.display = "none";
        }


        document.addEventListener("DOMContentLoaded", function() {
            fetchSuppliers();

            // Handle supplier selection
            document.getElementById("supplierDropdown").addEventListener("change", function() {
                document.getElementById("supplierId").value = this.value;

                const selectedOption = this.options[this.selectedIndex];
                document.getElementById("supplierName").value = selectedOption.textContent;
            });
        });

        function fetchSuppliers() {
            fetch("get_suppliers.php")
                .then(response => response.json())
                .then(data => {
                    let dropdown = document.getElementById("supplierDropdown");
                    dropdown.innerHTML = "";

                    data.forEach(supplier => {
                        let option = document.createElement("option");
                        option.value = supplier.supplier_id;
                        option.textContent = supplier.supplier_name;
                        dropdown.appendChild(option);
                    });
                })
                .catch(error => console.error("Error fetching suppliers:", error));
        }

        function toggleDiscountFields() {
            let percentageField = document.getElementById("discountPercentage");
            let valueField = document.getElementById("discountValue");
            let selectedType = document.querySelector('input[name="discountType"]:checked').value;

            if (selectedType === "percentage") {
                percentageField.style.display = "block";
                valueField.style.display = "none";
                valueField.value = ""; // Clear other field
            } else {
                percentageField.style.display = "none";
                valueField.style.display = "block";
                percentageField.value = ""; // Clear other field
            }
        }


        document.getElementById("promotionSupplierForm").addEventListener("submit", function(event) {
            event.preventDefault();

            let formData = new FormData(this);

            fetch('save_supplier_promotions.php', {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        this.reset(); // Reset form on success
                        document.getElementById("discountPercentage").style.display = "none";
                        document.getElementById("discountValue").style.display = "none";
                        fetchSupplierPromotions();
                    }
                })
                .catch(error => console.error("Error saving promotion:", error));
        });

        function fetchSupplierPromotions() {
            fetch("get_supplier_promotions.php")
                .then(response => response.json())
                .then(data => {
                    let tableBody = document.querySelector("#suppliersTable tbody");
                    tableBody.innerHTML = ""; // Clear existing table data

                    data.forEach(promotion => {
                        let row = document.createElement("tr");

                        row.innerHTML = `
                        <td>${promotion.supplier_id}</td>
                        <td>${promotion.supplier_name}</td>
                        <td>${promotion.discount_percentage ? promotion.discount_percentage + "%" : "-"}</td>
                        <td>${promotion.discount_value ? "LKR" + promotion.discount_value : "-"}</td>
                        <td>${promotion.start_date}</td>
                        <td>${promotion.end_date}</td>
                        <td>
                            <button onclick="deleteSupplierPromotion(${promotion.supplier_id})" style="background: none;">
                                <i class="fas fa-trash-alt promo-delete-icon"></i>
                            </button>
                        </td>
                    `;

                        tableBody.appendChild(row);
                    });
                })
                .catch(error => console.error("Error fetching promotions:", error));
        }

        function deleteSupplierPromotion(supplierId) {
            if (confirm("Are you sure you want to delete this promotion?")) {
                fetch("delete_supplier_promotion.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded",
                        },
                        body: `supplier_id=${supplierId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        fetchSupplierPromotions(); // Refresh table
                    })
                    .catch(error => console.error("Error deleting promotion:", error));
            }
        }


        document.getElementById("addDiscountPromoBtn").addEventListener("click", function() {
            document.getElementById("promotionDiscountModal").style.display = "block";
            fetchDiscountProducts();
        });

        function closeDiscountPromotionModal() {
            document.getElementById("promotionDiscountModal").style.display = "none";
        }

        document.addEventListener("DOMContentLoaded", function() {

            // Handle form submission
            document.getElementById("promotionDiscountForm").addEventListener("submit", function(event) {
                event.preventDefault();

                let product_barcode = document.getElementById("promoDiscountBarcode").value;
                let buy_quantity = document.getElementById("buyAmount2").value;
                let discountType = document.querySelector('input[name="discountType2"]:checked').value;
                let discountPercentage = document.getElementById("discountPercentage2").value || null;
                let discountValue = document.getElementById("discountValue2").value || null;

                let formData = new FormData();
                formData.append("product_barcode", product_barcode);
                formData.append("buy_quantity", buy_quantity);
                formData.append("discountType", discountType);
                formData.append("discount_percentage", discountPercentage);
                formData.append("discount_value", discountValue);

                fetch("save_discount_promotion.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) {
                            fetchDiscountProducts();
                        }
                    })
                    .catch(error => console.error("Error saving promotion:", error));
            });
        });

        function toggleDiscountFields2() {
            let percentageField = document.getElementById("discountPercentage2");
            let valueField = document.getElementById("discountValue2");
            let selectedType = document.querySelector('input[name="discountType2"]:checked').value;

            if (selectedType === "percentage") {
                percentageField.style.display = "block";
                valueField.style.display = "none";
                valueField.value = ""; // Clear other field
            } else {
                percentageField.style.display = "none";
                valueField.style.display = "block";
                percentageField.value = ""; // Clear other field
            }
        }

        function fetchDiscountProducts() {
            fetch("get_discount_products.php")
                .then(response => response.json())
                .then(data => {
                    let tableBody = document.querySelector("#productDiscountTable tbody");
                    tableBody.innerHTML = ""; // Clear existing rows

                    if (data.length === 0) {
                        tableBody.innerHTML = "<tr><td colspan='3'>No products found</td></tr>";
                    } else {
                        data.forEach(product => {
                            let row = document.createElement("tr");

                            row.innerHTML = `
                            <td>${product.barcode}</td>
                            <td>${product.product_name}</td>
                            <td>
                                <button onclick="deleteDiscountProduct('${product.barcode}')" style="background: none;">
                                    <i class="fas fa-trash-alt promo-delete-icon"></i>
                                </button>
                            </td>
                        `;
                            row.addEventListener("click", () => fetchDiscountPromotions(product.barcode));
                            tableBody.appendChild(row);
                        });
                    }
                })
                .catch(error => console.error("Error fetching products:", error));
        }

        function deleteDiscountProduct(barcode) {
            if (confirm("Are you sure you want to delete this product?")) {
                fetch("delete_discount_products.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: `barcode=${barcode}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert("Discount Promotions for this product deleted successfully!");
                            fetchDiscountProducts(); // Refresh the table
                        } else {
                            alert("Error: " + data.message);
                        }
                    })
                    .catch(error => console.error("Error deleting product:", error));
            }
        }

        function fetchDiscountPromotions(barcode) {
            fetch(`get_discount_promotion_records.php?barcode=${barcode}`)
                .then(response => response.json())
                .then(data => {
                    let tableBody = document.querySelector("#promotionDiscountTable tbody");
                    tableBody.innerHTML = ""; // Clear existing rows

                    if (data.length === 0) {
                        tableBody.innerHTML = "<tr><td colspan='4'>No promotions found for this product</td></tr>";
                    } else {
                        data.forEach(promotion => {
                            let row = document.createElement("tr");

                            row.innerHTML = `
                                <td>${promotion.id}</td>
                                <td>${promotion.buy_quantity}</td>
                                <td>${promotion.discount_percentage ? promotion.discount_percentage + "%" : "-"}</td>
                                <td>${promotion.discount_amount ? "LKR" + promotion.discount_amount : "-"}</td>   
                                <td>
                                    <button onclick="deleteDiscountPromotion(${promotion.id})" style="background: none;">
                                        <i class="fas fa-trash-alt promo-delete-icon"></i>
                                    </button>
                                </td>
                            `;

                            tableBody.appendChild(row);
                        });
                    }
                })
                .catch(error => console.error("Error fetching promotions:", error));
        }

        function deleteDiscountPromotion(id) {
            if (!confirm("Are you sure you want to delete this promotion?")) {
                return;
            }

            fetch("delete_discount_promotion_record.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        id: id
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Promotion deleted successfully!");
                        fetchDiscountProducts(); // Refresh product table
                        fetchDiscountPromotions(data.barcode); // Refresh promotions for that barcode
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(error => console.error("Error deleting promotion:", error));
        }

        const productDiscountTable = document.getElementById("productDiscountTable");
        const promotionDiscountTableBody = document.querySelector("#promotionDiscountTable tbody"); // Get the promotion table body

        productDiscountTable.addEventListener("click", (e) => {
            const rows = productDiscountTable.getElementsByTagName("tr");
            let isSelected = false;

            for (const row of rows) {
                row.classList.remove("selected");
            }

            const clickedRow = e.target.closest("tr");
            if (e.target.tagName === "TH" || e.target.closest("thead")) {
                if (clickedRow) clickedRow.classList.remove("selected");
            } else if (clickedRow && clickedRow.tagName === "TR") {
                clickedRow.classList.add("selected");
                isSelected = true;
                const barcode = clickedRow.cells[0].textContent; // Get the barcode from the first cell
                fetchDiscountPromotions(barcode); // Fetch promotions for the selected product
            }

            // If no row is selected, clear the promotions table
            if (!isSelected) {
                promotionDiscountTableBody.innerHTML = "";
            }
        });
        document.addEventListener("keydown", function(event) {
        if (event.code === "Home") {
            window.location.href = "../dashboard/index.php";
        }
    });


    document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('prodMgr_modal');
            const openBtn = document.getElementById('prodMgr_openBtn');
            const closeBtn = document.querySelector('.prodMgr_close');
            const exitBtn = document.getElementById('prodMgr_btnExit');
            const itemCodeInput = document.getElementById('prodMgr_itemCode');
            const saveBtn = document.getElementById('prodMgr_btnSave');

            // Open modal
            document.getElementById('prodMgr_openBtn').onclick = function() {
                modal.style.display = "block";
                fetchConversionData(); // Fetch data when modal opens
            };

            // Close modal
            const closeModal = () => modal.style.display = "none";
            closeBtn.onclick = closeModal;
            exitBtn.onclick = closeModal;
        

            function fetchConversionData() {
                fetch('?action=getConversions')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const tbody = document.getElementById('prodMgr_conversionData');
                            tbody.innerHTML = '';
                            
                            data.data.forEach(conversion => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td>${conversion.item_code}</td>
                                    <td>${conversion.product_name}</td>
                                    <td>${conversion.selling_bottle}g</td>
                                    <td>${conversion.selling_litre}g</td>
                                `;
                                tbody.appendChild(row);
                            });
                        }
                    })
                    .catch(error => console.error('Error fetching conversions:', error));
            }

            // Fetch product name when item code is entered
            function fetchProductName() {
                const itemCode = document.getElementById('prodMgr_itemCode').value.trim();
                if (!itemCode) return;

                fetch(`?action=getProduct&item_code=${itemCode}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('prodMgr_productName').value = data.product_name;
                        } else {
                            alert('Product not found!');
                            document.getElementById('prodMgr_productName').value = '';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error fetching product details');
                    });
            }

            
            //itemCodeInput.addEventListener('blur', fetchProductName);
            itemCodeInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault(); // Prevent form submission if inside a form
                    fetchProductName();
                }
            });

            // Save conversion
            saveBtn.addEventListener('click', function() {
                const formData = {
                    item_code: document.getElementById('prodMgr_itemCode').value.trim(),
                    product_name: document.getElementById('prodMgr_productName').value.trim(),
                    kilo_to_bottle: document.getElementById('prodMgr_kiloToBottle').value,
                    kilo_to_litter: document.getElementById('prodMgr_kiloToLitter').value
                };

                if (!formData.item_code || !formData.product_name) {
                    alert('Please fill in all required fields');
                    return;
                }

                fetch('?action=saveConversion', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Conversion saved successfully!');
                        fetchConversionData(); // Refresh data after saving
                        // Clear form
                        document.getElementById('prodMgr_itemCode').value = '';
                        document.getElementById('prodMgr_productName').value = '';
                        document.getElementById('prodMgr_kiloToBottle').value = '';
                        document.getElementById('prodMgr_kiloToLitter').value = '';
                    } else {
                        alert('Error saving conversion: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error saving conversion');
                });
            });
        });


    </script>
</body>

</html>