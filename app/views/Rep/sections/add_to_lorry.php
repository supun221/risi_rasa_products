<?php
// Add to Lorry logic can be placed here
// Connect to database, fetch products, etc.
require_once '../../../../config/databade.php';
?>

<div class="section-card fade-transition" id="add-section">
    <div class="section-header">
        <i class="fas fa-plus-circle"></i> Add to Lorry Stock
    </div>
    <div class="section-body">
        <a href="#" class="return-link" id="return-from-add">
            <i class="fas fa-chevron-left"></i> Return to Dashboard
        </a>
        
        <!-- Barcode Scanner Section -->
        <div class="form-group mb-4">
            <label for="barcode-input"><i class="fas fa-barcode mr-1"></i> Scan Barcode</label>
            <div class="input-group">
                <input type="text" class="form-control" id="barcode-input" placeholder="Scan or enter barcode" autofocus>
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button" id="search-barcode">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <small class="form-text text-muted">Scan product barcode or enter it manually and press Enter</small>
        </div>

        <form id="add-to-lorry-form">
            <div class="form-group">
                <label for="product-search">Product Search <span class="required-indicator">*</span></label>
                <input type="text" class="form-control" id="product-search" placeholder="Type product name to search" autocomplete="off">
                <div id="product-suggestions" class="product-suggestions"></div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="product">Selected Product</label>
                    <select class="form-control" id="product" name="stock_entry_id" required>
                        <option value="">Select Product</option>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label for="item-code">Item Code</label>
                    <input type="text" class="form-control" id="item-code" readonly>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="available-stock">Available Stock</label>
                        <input type="text" class="form-control" id="available-stock" readonly>
                        <small class="form-text text-muted">This is the quantity available in main stock</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="price">Unit Price</label>
                        <input type="text" class="form-control" id="price" readonly>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="quantity">Quantity <span class="required-indicator">*</span></label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                        <div class="helper-text">Enter the number of units to add to the lorry</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="total-amount">Total Amount</label>
                        <input type="text" class="form-control" id="total-amount" readonly>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">
                <i class="fas fa-plus-circle mr-2"></i> Add to Lorry Stock
            </button>
        </form>
        
        <!-- Recent Additions Section -->
        <div class="mt-4">
            <h5><i class="fas fa-history mr-1"></i> Recent Additions</h5>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Total</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody id="recent-additions">
                        <!-- Will be populated via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Duplicate Barcode Modal -->
<div class="modal fade" id="duplicate-barcode-modal" tabindex="-1" aria-labelledby="duplicate-barcode-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="duplicate-barcode-title">Multiple Products Found</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Multiple products were found with this barcode. Please select the correct one:</p>
                <div class="list-group" id="duplicate-products-list">
                    <!-- Products will be added here dynamically -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.product-suggestions {
    position: absolute;
    z-index: 1000;
    width: 100%;
    max-height: 200px;
    overflow-y: auto;
    background-color: #fff;
    border: 1px solid #ddd;
    border-radius: 0 0 4px 4px;
    box-shadow: 0 5px 10px rgba(0,0,0,0.1);
    display: none;
}
.product-suggestion-item {
    padding: 10px 15px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
}
.product-suggestion-item:hover, .product-suggestion-item:focus {
    background-color: #f8f9fa;
}
.product-suggestion-item:last-child {
    border-bottom: none;
}
.product-code {
    color: #6c757d;
    font-size: 0.85em;
    display: block;
}
.barcode-group {
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 15px;
    margin-bottom: 15px;
}
.list-group-item {
    cursor: pointer;
}
.list-group-item:hover {
    background-color: #f8f9fa;
}
</style>

<!-- Add SweetAlert2 library for toast notifications -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $(document).ready(function() {
        // Load recent additions on page load
        loadRecentAdditions();
        
        // Product search functionality
        $('#product-search').on('input', function() {
            let searchTerm = $(this).val();
            
            if(searchTerm.length < 2) {
                $('#product-suggestions').hide();
                return;
            }
            
            // AJAX call to search products
            $.ajax({
                url: 'process/search_products.php',
                type: 'GET',
                data: { term: searchTerm },
                dataType: 'json',
                success: function(response) {
                    if(response.success && response.products.length > 0) {
                        displayProductSuggestions(response.products);
                    } else {
                        $('#product-suggestions').hide();
                    }
                },
                error: function() {
                    console.error('Error searching products');
                }
            });
        });
        
        // Display product suggestions
        function displayProductSuggestions(products) {
            let suggestions = '';
            products.forEach(function(product) {
                suggestions += `<div class="product-suggestion-item" 
                    data-id="${product.id}" 
                    data-available="${product.available_stock}" 
                    data-price="${product.wholesale_price}"
                    data-itemcode="${product.itemcode}">
                    ${product.product_name}
                    <span class="product-code">Code: ${product.itemcode} | Stock: ${product.available_stock} ${product.unit}</span>
                </div>`;
            });
            
            $('#product-suggestions').html(suggestions).show();
            
            // Handle suggestion click
            $('.product-suggestion-item').click(function() {
                const productId = $(this).data('id');
                const productName = $(this).text().trim();
                const availableStock = $(this).data('available');
                const price = $(this).data('price');
                const itemCode = $(this).data('itemcode');
                
                selectProduct(productId, productName, availableStock, price, itemCode);
            });
        }
        
        // Handle barcode search
        $('#search-barcode, #barcode-input').on('click keypress', function(e) {
            if (e.type === 'click' || e.which === 13) { // Button click or Enter key
                e.preventDefault();
                
                const barcode = $('#barcode-input').val().trim();
                if (!barcode) return;
                
                $.ajax({
                    url: 'process/search_by_barcode.php',
                    type: 'GET',
                    data: { barcode: barcode },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            if (response.products.length === 1) {
                                // Single product found
                                const product = response.products[0];
                                selectProduct(product.id, product.product_name, product.available_stock, product.wholesale_price, product.itemcode);
                            } else if (response.products.length > 1) {
                                // Multiple products found - show modal
                                showDuplicateBarcodeModal(response.products);
                            } else {
                                alert('No products found with this barcode.');
                            }
                        } else {
                            alert('Error searching barcode: ' + response.message);
                        }
                    },
                    error: function() {
                        alert('Error searching barcode. Please try again.');
                    }
                });
                
                // Clear the barcode input for next scan
                $('#barcode-input').val('');
            }
        });
        
        // Show duplicate barcode modal
        function showDuplicateBarcodeModal(products) {
            let productList = '';
            products.forEach(function(product) {
                productList += `<a href="#" class="list-group-item list-group-item-action duplicate-product-item" 
                                data-id="${product.id}" 
                                data-name="${product.product_name}" 
                                data-available="${product.available_stock}"
                                data-price="${product.wholesale_price}"
                                data-itemcode="${product.itemcode}">
                    <strong>${product.product_name}</strong>
                    <small class="d-block text-muted">Code: ${product.itemcode} | Stock: ${product.available_stock} ${product.unit}</small>
                </a>`;
            });
            
            $('#duplicate-products-list').html(productList);
            $('#duplicate-barcode-modal').modal('show');
            
            // Handle product selection from modal
            $('.duplicate-product-item').click(function(e) {
                e.preventDefault();
                
                const productId = $(this).data('id');
                const productName = $(this).data('name');
                const availableStock = $(this).data('available');
                const price = $(this).data('price');
                const itemCode = $(this).data('itemcode');
                
                selectProduct(productId, productName, availableStock, price, itemCode);
                $('#duplicate-barcode-modal').modal('hide');
            });
        }
        
        // Select a product and update the form
        function selectProduct(id, name, availableStock, price, itemCode) {
            // Update product dropdown
            $('#product').html(`<option value="${id}" selected>${name}</option>`);
            
            // Update item code, stock and price fields
            $('#item-code').val(itemCode || '');
            $('#available-stock').val(availableStock);
            $('#price').val(price.toFixed(2));
            
            // Clear search input and hide suggestions
            $('#product-search').val('');
            $('#product-suggestions').hide();
            
            // Focus on quantity
            $('#quantity').focus();
            
            // Calculate total amount if quantity is already entered
            calculateTotal();
        }
        
        // Calculate total amount when quantity changes
        $('#quantity').on('input', function() {
            calculateTotal();
        });
        
        function calculateTotal() {
            const quantity = parseInt($('#quantity').val()) || 0;
            const price = parseFloat($('#price').val()) || 0;
            
            const total = quantity * price;
            $('#total-amount').val(total.toFixed(2));
        }
        
        // Function to load recent additions
        function loadRecentAdditions() {
            $.ajax({
                url: 'process/get_recent_additions.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        let html = '';
                        if (response.additions.length > 0) {
                            response.additions.forEach(function(addition) {
                                let addedTime = new Date(addition.date_added);
                                let timeStr = addedTime.toLocaleTimeString('en-US', {
                                    hour: '2-digit',
                                    minute: '2-digit'
                                });
                                
                                html += `<tr>
                                    <td>${addition.product_name}</td>
                                    <td>${addition.quantity}</td>
                                    <td>${parseFloat(addition.total_amount).toFixed(2)}</td>
                                    <td>${timeStr}</td>
                                </tr>`;
                            });
                        } else {
                            html = '<tr><td colspan="4" class="text-center">No recent additions</td></tr>';
                        }
                        $('#recent-additions').html(html);
                    }
                },
                error: function() {
                    $('#recent-additions').html('<tr><td colspan="4" class="text-center text-danger">Error loading data</td></tr>');
                }
            });
        }
        
        // Hide product suggestions when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#product-search, #product-suggestions').length) {
                $('#product-suggestions').hide();
            }
        });
        
        // Add to lorry form submission
        $('#add-to-lorry-form').submit(function(e) {
            e.preventDefault();
            
            // Get form data
            const stockEntryId = $('#product').val();
            const quantity = $('#quantity').val();
            const availableStock = parseInt($('#available-stock').val());
            
            // Validate form
            if (!stockEntryId || !quantity) {
                alert('Please select a product and enter quantity');
                return false;
            }
            
            // Check if quantity is valid
            if (parseInt(quantity) <= 0) {
                alert('Quantity must be greater than 0');
                return false;
            }
            
            // Check if enough stock is available
            if (parseInt(quantity) > availableStock) {
                alert(`Not enough stock available. Maximum available: ${availableStock}`);
                return false;
            }
            
            // Get rep_id from any source available
            const repId = <?php echo isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : '0'; ?>;
            
            // Debug the values that will be sent
            console.log('Submitting form with values:', {
                stock_entry_id: stockEntryId,
                quantity: quantity,
                rep_id: repId
            });
            
            // Send AJAX request
            $.ajax({
                url: 'process/add_to_lorry_process.php',
                type: 'POST',
                data: {
                    stock_entry_id: stockEntryId,
                    quantity: quantity,
                    rep_id: repId
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Server response:', response);
                    
                    if (response.success) {
                        // Load recent additions to refresh the table
                        loadRecentAdditions();
                        
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 2000
                        }).then(function() {
                            // Reset form
                            resetForm();
                        });
                    } else {
                        // Show error message
                        let errorMsg = response.message;
                        
                        // If there's debug info, show it only during development
                        if (response.debug_info) {
                            console.error('Debug info:', response.debug_info);
                            // Comment out the next line in production
                            errorMsg += '\n\nDebug info: ' + JSON.stringify(response.debug_info, null, 2);
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMsg
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', status, error);
                    console.log('Response text:', xhr.responseText);
                    
                    // Show error message
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred. Please try again.'
                    });
                }
            });
        });
        
        // Function to reset the form
        function resetForm() {
            $('#product').html('<option value="">Select Product</option>');
            $('#item-code').val('');
            $('#available-stock').val('');
            $('#price').val('');
            $('#total-amount').val('');
            $('#quantity').val('1');
        }
        
        // Sweet Alert Toast
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
        
        // Focus on barcode input when page loads
        $('#barcode-input').focus();
        
        // Update the JavaScript to include available_stock in the display
        $('#product-search-results').on('click', '.product-item', function() {
            const stockId = $(this).data('id');
            const productName = $(this).data('name');
            const availableStock = $(this).data('stock');
            const price = $(this).data('price');
            
            $('#stock-entry-id').val(stockId);
            $('#selected-product').val(productName);
            $('#available-stock').val(availableStock);
            $('#product-price').text('Rs. ' + parseFloat(price).toFixed(2));
            
            // Set max quantity to available stock
            $('#quantity').attr('max', availableStock);
            
            $('#product-search-results').hide();
        });
        
        // Ensure quantity doesn't exceed available stock
        $('#quantity').on('input', function() {
            const max = parseInt($(this).attr('max'));
            const val = parseInt($(this).val());
            
            if (val > max) {
                $(this).val(max);
                alert('You cannot add more than the available stock (' + max + ')');
            }
        });
    });
</script>
