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

    if ($_GET['action'] === 'saveStringConversion') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            exit;
        }
        
        try {
            // Check if record exists
            $check_stmt = $conn->prepare("SELECT id FROM measurement_conversions_strings WHERE item_code = ?");
            $check_stmt->bind_param("s", $data['item_code']);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Update existing record
                $stmt = $conn->prepare("UPDATE measurement_conversions_strings 
                    SET product_name = ?, selling_meter = ?, selling_yard = ? 
                    WHERE item_code = ?");
                $stmt->bind_param("sdds", 
                    $data['product_name'],
                    $data['kilo_to_meter'],
                    $data['kilo_to_yard'],
                    $data['item_code']
                );
            } else {
                // Insert new record
                $stmt = $conn->prepare("INSERT INTO measurement_conversions_strings 
                    (item_code, product_name, selling_meter, selling_yard) 
                    VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssdd", 
                    $data['item_code'],
                    $data['product_name'],
                    $data['kilo_to_meter'],
                    $data['kilo_to_yard']
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

    if ($_GET['action'] === 'getStringConversions') {
        $query = "SELECT * FROM measurement_conversions_strings ORDER BY id DESC";
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
    <style>
        .highlighted-history-row{
            background-color:  #a0c1df;
            /* color: white; */
        }
    </style>
</head>
<body>


<?php require_once '../header1.php'; ?>

    <div style="position: relative; text-align: center; margin-bottom: 10px; margin-top: 10px;">
        <button id="stockCreationHistoryBtn" class="btn btn-primary" style="position: absolute; left: 5%; top: 50%; transform: translateY(-50%); background-color: #e05b5b;"
        onclick="location.href='../inventory/stock_view.php';"><i class="fas fa-arrow-left"></i>
            Back
        </button>
        <h1 style="display: inline-block; margin: 0;">All Stock Creations</h1>

    </div>

    <div id="searchWrapper">
        <label for="searchInput">🔍 Search:</label>
        <input type="text" id="searchInput" placeholder="Type product name or barcode">
    </div>
    <br>

    <div id="stockTableWrapper">
        <table id="stockTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Created Date</th>
                    <th>Stock ID</th>
                    <th>Supplier</th>
                    <th>Product</th>
                    <th>Barcode</th>
                    <th>Purchase Quantity</th>
                    <th>Unit</th>
                    <th>Cost Price</th>
                    <th>Wholesale Price</th>
                    <th>Max Retail Price</th>
                    <th>Super Customer Price</th>
                    <th>Our Price</th>
                    <th>Low Stock</th>
                    <th>Expire Date</th>
                    <th>Discount Percent</th>                   
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

    <!-- <div id="pagination">
        <button id="prevPage" disabled>Previous</button>
        <span id="currentPage">1</span> / <span id="totalPages">1</span>
        <button id="nextPage">Next</button>
    </div> -->

    <!-- Edit Modal -->
    <div id="editModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h2>Edit Stock Entry</h2>
            <form id="editForm">
                <div id="editFormBox">
                    <div>
                        <input type="hidden" id="stockId">

                        <input type="hidden" id="editId">
                        <input type="hidden" id="editSupplierId">

                        <label for="editSupplierName">Supplier Name:</label>
                        <input type="text" id="editSupplierName" disabled>

                        <label for="editProductName">Product Name:</label>
                        <input type="text" id="editProductName" required disabled>

                        <label for="editBarcode">Barcode:</label>
                        <input type="text" id="editBarcode" disabled>

                        <label for="editAvailableStock">Purchase Quantity:</label>
                        <input type="number" step="0.01" id="editPurchaseQty" required>
                        <input type="hidden" id="previousPurchaseQty">

                        <label for="editUnit">Unit:</label>
                        <select id="editUnit" name="editUnit">
                            <option value="">Select Unit</option>
                            <?php
                            // Database connection
                            include 'connection_db.php'; // Ensure you have a valid DB connection file

                            $query = "SELECT unit_name FROM unit";
                            $result = mysqli_query($conn, $query);

                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<option value='" . $row['unit_name'] . "'>" . $row['unit_name'] . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div>
                        <label for="editCostPrice">Cost Price:</label>
                        <input type="text" id="editCostPrice" required>
                        <input type="hidden" id="previousCostPrice">

                        <label for="editWholesalePrice">Wholesale Price:</label>
                        <input type="number" step="0.01" id="editWholesalePrice" required>                       

                        <label for="editMaxRetailPrice">Max Retail Price:</label>
                        <input type="number" step="0.01" id="editMaxRetailPrice" required>
                        
                        <label for="editSuperCustomerPrice">Super Customer Price:</label>
                        <input type="number" step="0.01" id="editSuperCustomerPrice" required>
                    </div>
                    

                    <div>
                        <label for="editOurPrice">Our Price:</label>
                        <input type="number" step="0.01" id="editOurPrice" required>

                        <label for="editLowStock">Low Stock:</label>
                        <input type="number" step="0.01" id="editLowStock">

                        <label for="editExpireDate">Expiration Date:</label>
                        <input type="date" id="editExpireDate">

                        <label for="editDiscountPercent">Discount Percent:</label>
                        <input type="number" step="0.01" id="editDiscountPercent">


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
            const itemsPerPage = 15;
            let isLoading = false;
            let hasMoreData = true;

            // Add CSS for the table and loading spinner
            $('head').append(`
                <style>
                    #stockTableWrapper {
                        max-height: 600px;
                        overflow-y: auto;
                        width: 98%;
                        margin: 0 auto;
                        margin-bottom:45px;
                    }
                
                    
                    .spinner {
                        width: 40px;
                        height: 40px;
                        margin: 0 auto;
                        border: 4px solid #f3f3f3;
                        border-top: 4px solid #3498db;
                        border-radius: 50%;
                        animation: spin 1s linear infinite;
                    }
                    
                    @keyframes spin {
                        0% { transform: rotate(0deg); }
                        100% { transform: rotate(360deg); }
                    }
                    
                    #loading-indicator {
                        text-align: center;
                        padding: 10px;
                    }
                </style>
            `);

            // Add loading indicator to DOM
            $('#stockTableWrapper').append('<div id="loading-indicator" style="display:none;"><div class="spinner"></div><p>Loading more items...</p></div>');

            // Detect when user scrolls to bottom of table
            $('#stockTableWrapper').on('scroll', function() {
                const wrapper = $(this);
                const scrollPosition = wrapper.scrollTop() + wrapper.innerHeight();
                const scrollHeight = wrapper[0].scrollHeight;
                
                // If we're within 50px of the bottom and not currently loading and have more data
                if (scrollHeight - scrollPosition < 50 && !isLoading && hasMoreData) {
                    loadMoreData();
                }
            });

            function loadMoreData() {
                isLoading = true;
                $('#loading-indicator').show();
                
                // Increment page and fetch next batch
                currentPage++;
                
                $.ajax({
                    url: '../../controllers/stockController.php',
                    method: 'GET',
                    data: {
                        action: 'getStockCreations',
                        page: currentPage,
                        limit: itemsPerPage,
                        search: $('#searchInput').val().trim() || ''
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data.entries && data.entries.length > 0) {
                            const tableBody = $('#stockTable tbody');
                            
                            // Append new rows instead of replacing
                            data.entries.forEach(entry => {
                                const row = `
                                    <tr data-id="${entry.id}" data-supplier-id="${entry.supplier_id}">
                                        <td>${entry.id}</td>
                                        <td>${entry.created_at}</td>
                                        <td>${entry.stock_id}</td>
                                        <td>${entry.supplier_name}</td>
                                        <td>${entry.product_name}</td>
                                        <td>${entry.barcode}</td>
                                        <td>${entry.purchase_qty}</td>
                                        <td>${entry.unit}</td>
                                        <td>${entry.cost_price}</td>
                                        <td>${entry.wholesale_price}</td>
                                        <td>${entry.max_retail_price}</td>
                                        <td>${entry.super_customer_price}</td>
                                        <td>${entry.our_price}</td>
                                        <td>${entry.low_stock}</td>
                                        <td>${entry.expire_date}</td>
                                        <td>${entry.discount_percent}</td>
                                        <td>${entry.free === '1' ? 'Yes' : 'No'}</td>
                                        <td>${entry.gift === '1' ? 'Yes' : 'No'}</td>
                                        <td>${entry.voucher === '1' ? 'Yes' : 'No'}</td>
                                        <td>
                                            <div style="display:flex;">
                                                <button class="editBtn"><i class="fas fa-edit"></i></button>
                                                <button class="deleteBtn"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                `;
                                tableBody.append(row);
                            });
                            
                            // Initialize any event handlers for the new rows (if needed)
                            initializeRowEventHandlers();
                            
                            // Check if this is the last page
                            if (currentPage >= data.totalPages) {
                                hasMoreData = false;
                            }
                        } else {
                            hasMoreData = false;
                        }
                        
                        isLoading = false;
                        $('#loading-indicator').hide();
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to load more stock entries', 'error');
                        isLoading = false;
                        $('#loading-indicator').hide();
                    }
                });
            }

            function fetchStockEntries(page, searchQuery = '') {
                isLoading = true;
                $('#loading-indicator').show();
                
                $.ajax({
                    url: '../../controllers/stockController.php',
                    method: 'GET',
                    data: {
                        action: 'getStockCreations',
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
                                        <td>${entry.created_at}</td>
                                        <td>${entry.stock_id}</td>
                                        <td>${entry.supplier_name}</td>
                                        <td>${entry.product_name}</td>
                                        <td>${entry.barcode}</td>
                                        <td>${entry.purchase_qty}</td>
                                        <td>${entry.unit}</td>
                                        <td>${entry.cost_price}</td>
                                        <td>${entry.wholesale_price}</td>
                                        <td>${entry.max_retail_price}</td>
                                        <td>${entry.super_customer_price}</td>
                                        <td>${entry.our_price}</td>
                                        <td>${entry.low_stock}</td>
                                        <td>${entry.expire_date}</td>
                                        <td>${entry.discount_percent}</td>
                                        <td>${entry.free === '1' ? 'Yes' : 'No'}</td>
                                        <td>${entry.gift === '1' ? 'Yes' : 'No'}</td>
                                        <td>${entry.voucher === '1' ? 'Yes' : 'No'}</td>
                                        <td>
                                            <div style="display:flex;">
                                                <button class="editBtn"><i class="fas fa-edit"></i></button>
                                                <button class="deleteBtn"><i class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                `;
                                tableBody.append(row);
                            });
                            
                            // Initialize event handlers for edit and delete buttons
                            initializeRowEventHandlers();
                            
                            // Set hasMoreData flag based on pagination info
                            hasMoreData = (currentPage < data.totalPages);
                            currentPage = data.currentPage;
                        } else {
                            tableBody.append('<tr><td colspan="21">No data available</td></tr>');
                            hasMoreData = false;
                        }
                        
                        isLoading = false;
                        $('#loading-indicator').hide();
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to load stock entries', 'error');
                        isLoading = false;
                        $('#loading-indicator').hide();
                    }
                });
            }

            // Function to initialize event handlers for edit and delete buttons
            function initializeRowEventHandlers() {
                // You would add your edit and delete button event handlers here
                // For example:
                // $('.editBtn').off('click').on('click', function() {
                //     const row = $(this).closest('tr');
                //     const id = row.data('id');
                //     // Your edit functionality here
                //     console.log('Edit item with ID:', id);
                // });

                if (!$('#highlight-style').length) {
                    $('head').append(`
                        <style id="highlight-style">
                            tr.highlighted {
                                background-color:rgb(208, 229, 247); /* Light blue background */
                                transition: background-color 0.3s ease;
                            }
                        </style>
                    `);
                }
                
                // Edit button handler
                $('.editBtn').off('click').on('click', function() {
                    const row = $(this).closest('tr');
                    const id = row.data('id');
                    
                    // Remove highlighting from any previously highlighted row
                    $('#stockTable tbody tr').removeClass('highlighted');
                    
                    // Add highlighting to the current row
                    row.addClass('highlighted');
                    
                    // Your existing edit functionality here
                    console.log('Edit item with ID:', id);
                });
                
                // $('.deleteBtn').off('click').on('click', function() {
                //     const row = $(this).closest('tr');
                //     const id = row.data('id');
                //     // Your delete functionality here
                //     console.log('Delete item with ID:', id);
                // });

                $('.deleteBtn').off('click').on('click', function() {
                    const row = $(this).closest('tr');
                    const id = row.data('id');
                    
                    // Remove highlighting from any previously highlighted row
                    $('#stockTable tbody tr').removeClass('highlighted');
                    
                    // Add highlighting to the current row
                    row.addClass('highlighted');
                    
                    // Your delete functionality here
                    console.log('Delete item with ID:', id);
                });
            }

            // Real-time search with reset
            $('#searchInput').on('input', function() {
                const searchQuery = $(this).val().trim();
                currentPage = 1;
                hasMoreData = true;
                fetchStockEntries(1, searchQuery); // Reset to page 1 when searching
            });

            // Remove pagination buttons functionality (not needed with infinite scroll)
            // But keep them in the DOM if you want to repurpose them later

            // Fetch initial stock entries
            fetchStockEntries(currentPage);
        


            $(document).on('click', '.editBtn', function() {
                const row = $(this).closest('tr');

                // Populate modal with the selected row data
                $('#editId').val(row.data('id'));
                $('#editSupplierId').val(row.data('supplier-id')); // Add this line
                $('#editSupplierName').val(row.find('td:nth-child(4)').text());
                $('#editProductName').val(row.find('td:nth-child(5)').text());
                $('#editBarcode').val(row.find('td:nth-child(6)').text());
                $('#editPurchaseQty').val(row.find('td:nth-child(7)').text());
                $('#previousPurchaseQty').val(row.find('td:nth-child(7)').text());
                $('#editUnit').val(row.find('td:nth-child(8)').text());
                $('#editCostPrice').val(row.find('td:nth-child(9)').text());
                $('#previousCostPrice').val(row.find('td:nth-child(9)').text());
                $('#editWholesalePrice').val(row.find('td:nth-child(10)').text());
                $('#editMaxRetailPrice').val(row.find('td:nth-child(11)').text());
                $('#editSuperCustomerPrice').val(row.find('td:nth-child(12)').text());
                $('#editOurPrice').val(row.find('td:nth-child(13)').text());
                $('#editLowStock').val(row.find('td:nth-child(14)').text());
                //$('#editExpireDate').val(row.find('td:nth-child(12)').text());
                $('#editDiscountPercent').val(row.find('td:nth-child(16)').text());
                $('#stockId').val(row.find('td:nth-child(3)').text());
                // $('#editFree').prop('checked', row.find('td:nth-child(14)').text() === 'Yes');
                // $('#editGift').prop('checked', row.find('td:nth-child(15)').text() === 'Yes');
                // $('#editVoucher').prop('checked', row.find('td:nth-child(16)').text() === 'Yes');
                
                const expireDate = row.find('td:nth-child(15)').text();
                if (expireDate) {
                    // For date inputs, the value must be in YYYY-MM-DD format
                    // If your date is already in YYYY-MM-DD format but still not working,
                    // we need to ensure it's properly formatted without extra spaces or characters
                    
                    // Try to parse the date
                    const dateObj = new Date(expireDate);
                    if (!isNaN(dateObj.getTime())) {
                        // Valid date - format it as YYYY-MM-DD
                        const year = dateObj.getFullYear();
                        // Month is 0-indexed in JavaScript Date, so add 1 and pad with leading zero if needed
                        const month = String(dateObj.getMonth() + 1).padStart(2, '0');
                        const day = String(dateObj.getDate()).padStart(2, '0');
                        
                        const formattedDate = `${year}-${month}-${day}`;
                        console.log("Formatted date for input:", formattedDate); // Debug log
                        $('#editExpireDate').val(formattedDate);
                    } else {
                        // If date parsing failed, try direct assignment
                        $('#editExpireDate').val(expireDate);
                        console.log("Date parsing failed, using original value");
                    }
                }

                // Show the modal
                $('#editModal').show();
            });


            // Submit form handler
            // Submit form handler
            // $('#editForm').submit(function(e) {
            //     e.preventDefault();

            //     // Collect form data
            //     const formData = {
            //         action: 'updateStockCreation',
            //         id: $('#editId').val(),
            //         supplier_id: $('#editSupplierId').val(),
            //         product_name: $('#editProductName').val(),
            //         purchase_qty: $('#editPurchaseQty').val(),
            //         previous_purchase_qty: $('#previousPurchaseQty').val(),
            //         unit: $('#editUnit').val(),
            //         cost_price: $('#editCostPrice').val(),
            //         previous_cost_price: $('#previousCostPrice').val(),
            //         wholesale_price: $('#editWholesalePrice').val(),
            //         max_retail_price: $('#editMaxRetailPrice').val(),
            //         super_customer_price: $('#editSuperCustomerPrice').val(),
            //         our_price: $('#editOurPrice').val(),
            //         low_stock: $('#editLowStock').val(),
            //         expire_date: $('#editExpireDate').val(),
            //         discount_percent: $('#editDiscountPercent').val(),
            //         barcode: $('#editBarcode').val(),
            //         stock_id: $('#stockId').val(),
            //         category: $('#editCategory').val(),
            //     };

            //     // Perform AJAX request to update entry
            //     $.ajax({
            //         url: '../../controllers/stockController.php',
            //         method: 'POST',
            //         data: formData,
            //         dataType: 'json',
            //         success: function(response) {
            //             if (response.success) {
            //                 // Fetch the updated entry from the server
            //                 $.ajax({
            //                     url: '../../controllers/stockController.php',
            //                     method: 'GET',
            //                     data: {
            //                         action: 'getStockCreations',
            //                         page: currentPage,
            //                         limit: itemsPerPage,
            //                         search: $('#searchInput').val().trim()
            //                     },
            //                     dataType: 'json',
            //                     success: function(data) {
            //                         if (data.entries) {
            //                             const updatedEntry = data.entries.find(entry => entry.id === parseInt(formData.id));
            //                             if (updatedEntry) {
            //                                 // Find the row to update
            //                                 const row = $(`#stockTable tbody tr[data-id="${updatedEntry.id}"]`);

            //                                 // Update row content
            //                                 row.html(`
            //                                     <td>${updatedEntry.id}</td>
            //                                     <td>${updatedEntry.created_at}</td>
            //                                     <td>${updatedEntry.stock_id}</td>
            //                                     <td>${updatedEntry.supplier_name}</td>
            //                                     <td>${updatedEntry.product_name}</td>
            //                                     <td>${updatedEntry.barcode}</td>
            //                                     <td>${updatedEntry.purchase_qty}</td>
            //                                     <td>${updatedEntry.unit}</td>
            //                                     <td>${updatedEntry.cost_price}</td>
            //                                     <td>${updatedEntry.wholesale_price}</td>
            //                                     <td>${updatedEntry.max_retail_price}</td>
            //                                     <td>${updatedEntry.super_customer_price}</td>
            //                                     <td>${updatedEntry.our_price}</td>
            //                                     <td>${updatedEntry.low_stock}</td>
            //                                     <td>${updatedEntry.expire_date}</td>
            //                                     <td>${updatedEntry.discount_percent}</td>                                      
            //                                     <td>${updatedEntry.free === '1' ? 'Yes' : 'No'}</td>
            //                                     <td>${updatedEntry.gift === '1' ? 'Yes' : 'No'}</td>
            //                                     <td>${updatedEntry.voucher === '1' ? 'Yes' : 'No'}</td>
            //                                     <td>${updatedEntry.category}</td>
            //                                     <td>
            //                                         <div style="display:flex;">
            //                                             <button class="editBtn"><i class="fas fa-edit"></i></button>
            //                                             <button class="deleteBtn"><i class="fas fa-trash"></i></button>
            //                                         </div>
            //                                     </td>
            //                                 `);

            //                                 Swal.fire('Success', 'Stock entry updated successfully', 'success');
            //                                 $('#editModal').hide(); // Hide modal
            //                             }
            //                         }
            //                     },
            //                     error: function() {
            //                         Swal.fire('Error', 'Failed to fetch updated data', 'error');
            //                     }
            //                 });
            //             } else {
            //                 Swal.fire('Error', response.error || 'Failed to update stock entry', 'error');
            //             }
            //         },
            //         error: function() {
            //             Swal.fire('Error', 'Unable to communicate with the server', 'error');
            //         }
            //     });
            // });


            $('#editForm').submit(function(e) {
                e.preventDefault();

                // Show loading indicator
                Swal.fire({
                    title: 'Processing...',
                    text: 'Updating stock information',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Collect form data
                const formData = {
                    action: 'updateStockCreation',
                    id: $('#editId').val(),
                    supplier_id: $('#editSupplierId').val(),
                    product_name: $('#editProductName').val(),
                    purchase_qty: $('#editPurchaseQty').val(),
                    previous_purchase_qty: $('#previousPurchaseQty').val(),
                    unit: $('#editUnit').val(),
                    cost_price: $('#editCostPrice').val(),
                    previous_cost_price: $('#previousCostPrice').val(),
                    wholesale_price: $('#editWholesalePrice').val(),
                    max_retail_price: $('#editMaxRetailPrice').val(),
                    super_customer_price: $('#editSuperCustomerPrice').val(),
                    our_price: $('#editOurPrice').val(),
                    low_stock: $('#editLowStock').val(),
                    expire_date: $('#editExpireDate').val(),
                    discount_percent: $('#editDiscountPercent').val(),
                    barcode: $('#editBarcode').val(),
                    stock_id: $('#stockId').val(),

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
                                    action: 'getStockCreations',
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
                                                <td>${updatedEntry.created_at}</td>
                                                <td>${updatedEntry.stock_id}</td>
                                                <td>${updatedEntry.supplier_name}</td>
                                                <td>${updatedEntry.product_name}</td>
                                                <td>${updatedEntry.barcode}</td>
                                                <td>${updatedEntry.purchase_qty}</td>
                                                <td>${updatedEntry.unit}</td>
                                                <td>${updatedEntry.cost_price}</td>
                                                <td>${updatedEntry.wholesale_price}</td>
                                                <td>${updatedEntry.max_retail_price}</td>
                                                <td>${updatedEntry.super_customer_price}</td>
                                                <td>${updatedEntry.our_price}</td>
                                                <td>${updatedEntry.low_stock}</td>
                                                <td>${updatedEntry.expire_date}</td>
                                                <td>${updatedEntry.discount_percent}</td>                                      
                                                <td>${updatedEntry.free === '1' ? 'Yes' : 'No'}</td>
                                                <td>${updatedEntry.gift === '1' ? 'Yes' : 'No'}</td>
                                                <td>${updatedEntry.voucher === '1' ? 'Yes' : 'No'}</td>
                                                <td>
                                                    <div style="display:flex;">
                                                        <button class="editBtn"><i class="fas fa-edit"></i></button>
                                                        <button class="deleteBtn"><i class="fas fa-trash"></i></button>
                                                    </div>
                                                </td>
                                            `);

                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Success',
                                                text: 'Stock entry updated successfully',
                                                confirmButtonColor: '#3085d6'
                                            });
                                            $('#editModal').hide(); // Hide modal
                                        } else {
                                            // Entry found but not in current page, refresh the table
                                            refreshTable();
                                            Swal.fire({
                                                icon: 'success',
                                                title: 'Success',
                                                text: 'Stock entry updated successfully. Table has been refreshed.',
                                                confirmButtonColor: '#3085d6'
                                            });
                                            $('#editModal').hide();
                                        }
                                    } else {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Success',
                                            text: 'Stock entry updated successfully. Refreshing page...',
                                            confirmButtonColor: '#3085d6'
                                        });
                                        setTimeout(() => {
                                            location.reload();
                                        }, 1500);
                                        $('#editModal').hide();
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error("Error fetching updated data:", error);
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Success with warning',
                                        text: 'Stock entry was updated successfully, but failed to refresh the table. Please reload the page.',
                                        confirmButtonColor: '#3085d6'
                                    });
                                    $('#editModal').hide();
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.error || 'Failed to update stock entry',
                                confirmButtonColor: '#d33'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX error:", error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: 'Unable to communicate with the server. Please try again later.',
                            confirmButtonColor: '#d33'
                        });
                    }
                });
            });

            // Add this function to refresh the table
            function refreshTable() {
                $.ajax({
                    url: '../../controllers/stockController.php',
                    method: 'GET',
                    data: {
                        action: 'getStockCreations',
                        page: currentPage,
                        limit: itemsPerPage,
                        search: $('#searchInput').val().trim()
                    },
                    dataType: 'json',
                    success: function(data) {
                        renderTable(data.entries);
                        updatePagination(data.totalPages);
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to refresh table data', 'error');
                    }
                });
            }

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
            //fetchStockEntries(currentPage);

            // Event delegation for delete buttons
            // $(document).on('click', '.deleteBtn', function() {
            //     const row = $(this).closest('tr');
            //     const id = row.data('id');
                
            //     // Confirm before deleting
            //     Swal.fire({
            //         title: 'Are you sure?',
            //         text: "You won't be able to revert this!",
            //         icon: 'warning',
            //         showCancelButton: true,
            //         confirmButtonColor: '#d33',
            //         cancelButtonColor: '#3085d6',
            //         confirmButtonText: 'Yes, delete it!'
            //     }).then((result) => {
            //         if (result.isConfirmed) {
            //             // Send delete request
            //             $.ajax({
            //                 url: '../../controllers/stockController.php',
            //                 method: 'POST',
            //                 data: {
            //                     action: 'deleteStockCreation',
            //                     id: id
            //                 },
            //                 dataType: 'json',
            //                 success: function(response) {
            //                     if (response.success) {
            //                         // Remove the row from the table
            //                         row.remove();
                                    
            //                         // Show success message
            //                         Swal.fire(
            //                             'Deleted!',
            //                             'Stock creation has been deleted.',
            //                             'success'
            //                         );
                                    
            //                         // If the table is now empty and this was the last item on the page, go to previous page
            //                         if ($('#stockTable tbody tr').length === 0 && currentPage > 1) {
            //                             currentPage--;
            //                             loadData();
            //                         } else {
            //                             // Otherwise just reload the current page to update counts
            //                             loadData();
            //                         }
            //                     } else {
            //                         Swal.fire(
            //                             'Error!',
            //                             response.error || 'Failed to delete the record.',
            //                             'error'
            //                         );
            //                     }
            //                 },
            //                 error: function() {
            //                     Swal.fire(
            //                         'Error!',
            //                         'Unable to communicate with the server.',
            //                         'error'
            //                     );
            //                 }
            //             });
            //         }
            //     });
            // });


            $(document).on('click', '.deleteBtn', function() {
                const row = $(this).closest('tr');
                const id = row.data('id');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '../../controllers/stockController.php',
                            method: 'POST',
                            data: {
                                action: 'deleteStockCreation',
                                id: id
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    row.remove();
                                    
                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: 'Stock creation has been deleted successfully.',
                                        icon: 'success',
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                    
                                    // Check if table is empty and handle pagination
                                    if ($('#stockTable tbody tr').length === 0 && currentPage > 1) {
                                        currentPage--;
                                    }
                                    loadData();
                                } else {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: response.error || 'Failed to delete the record.',
                                        icon: 'error'
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                Swal.fire({
                                    title: 'Server Error!',
                                    text: 'Unable to communicate with the server: ' + error,
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            });
        });

        let products = [];

        // document.addEventListener("DOMContentLoaded", async () => {
        //     try {
        //         const response = await fetch("getAllProducts.php");
        //         const data = await response.json();
        //         if (data.error) {
        //             console.error(data.error);
        //             return;
        //         }
        //         products = data;
        //         populateDropdown(products);
        //     } catch (error) {
        //         console.error("Failed to fetch products:", error);
        //     }
        // });


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


        document.addEventListener("keydown", function (event) {
            if (event.code === "Home") {
                window.location.href = "../dashboard/index.php";
            }
        });

        let stockCreationHistIndex = -1;

        document.addEventListener('keydown' , (e) => {
            const historyRows = document.querySelectorAll('#stockTable tbody tr')
            if (historyRows.length - 1 == 0) return;
            if(e.key == "ArrowDown"){
                if(stockCreationHistIndex < historyRows.length - 1){
                    stockCreationHistIndex++
                }else{
                    stockCreationHistIndex = 0;
                }
            }

            if(e.key == "ArrowUp"){
                if(stockCreationHistIndex > 0){
                    stockCreationHistIndex--
                }else{
                    stockCreationHistIndex = historyRows.length - 1
                }
            }

            historyRows.forEach((row) => row.classList.remove("highlighted-history-row"))
            historyRows[stockCreationHistIndex].classList.add("highlighted-history-row")
            historyRows[stockCreationHistIndex].scrollIntoView({behavior: "smooth" , block: "center"})
        })

    </script>
</body>

</html>