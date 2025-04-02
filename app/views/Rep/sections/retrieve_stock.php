<?php
// Retrieve stock logic can be placed here
require_once '../../../../config/databade.php';
?>

<div class="section-card fade-transition" id="retrieve-section">
    <div class="section-header">
        <i class="fas fa-dolly"></i> Retrieve from Lorry
    </div>
    <div class="section-body">
        <a href="#" class="return-link" id="return-from-retrieve">
            <i class="fas fa-chevron-left"></i> Return to Dashboard
        </a>
        
        <!-- Barcode Scanner Section -->
        <div class="form-group mb-4">
            <label for="retrieve-barcode-input"><i class="fas fa-barcode mr-1"></i> Scan Barcode</label>
            <div class="input-group">
                <input type="text" class="form-control" id="retrieve-barcode-input" placeholder="Scan or enter barcode">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button" id="retrieve-search-barcode">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <small class="form-text text-muted">Scan product barcode or enter it manually and press Enter</small>
        </div>
        
        <form id="retrieve-form">
            <div class="form-group">
                <label for="retrieve-product-search">Product Search <span class="required-indicator">*</span></label>
                <input type="text" class="form-control" id="retrieve-product-search" placeholder="Type product name to search" autocomplete="off">
                <div id="retrieve-product-suggestions" class="product-suggestions"></div>
            </div>
            
            <div class="form-group">
                <label for="retrieve-product">Selected Product</label>
                <select class="form-control" id="retrieve-product" name="stock_entry_id" required>
                    <option value="">Select Product</option>
                </select>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="retrieve-available-stock">Available Stock</label>
                        <input type="text" class="form-control" id="retrieve-available-stock" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="retrieve-price">Wholesale Price</label>
                        <input type="text" class="form-control" id="retrieve-price" readonly>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="retrieve-quantity">Quantity <span class="required-indicator">*</span></label>
                <input type="number" class="form-control" id="retrieve-quantity" name="quantity" min="1" required>
            </div>
            
            <div class="form-group">
                <label for="retrieve-reason">Reason for Retrieval <span class="required-indicator">*</span></label>
                <select class="form-control" id="retrieve-reason" name="reason" required>
                    <option value="">Select Reason</option>
                    <option value="sale">Sale to Customer</option>
                    <option value="damage">Damaged Goods</option>
                    <option value="return">Return to Warehouse</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div class="form-group" id="customer-details" style="display: none;">
                <label for="customer">Customer Name</label>
                <input type="text" class="form-control" id="customer" name="customer" placeholder="Enter customer name">
            </div>
            
            <div class="form-group">
                <label for="retrieve-total-amount">Total Amount</label>
                <input type="text" class="form-control" id="retrieve-total-amount" readonly>
            </div>
            
            <button type="submit" class="btn btn-success btn-block">Confirm Retrieval</button>
        </form>
    </div>
</div>

<!-- Duplicate Barcode Modal for Retrieve -->
<div class="modal fade" id="retrieve-duplicate-modal" tabindex="-1" aria-labelledby="retrieve-duplicate-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="retrieve-duplicate-title">Multiple Products Found</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Multiple products were found with this barcode. Please select the correct one:</p>
                <div class="list-group" id="retrieve-duplicate-list">
                    <!-- Products will be added here dynamically -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Product search functionality
        $('#retrieve-product-search').on('input', function() {
            let searchTerm = $(this).val();
            
            if(searchTerm.length < 2) {
                $('#retrieve-product-suggestions').hide();
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
                        displayRetrieveProductSuggestions(response.products);
                    } else {
                        $('#retrieve-product-suggestions').hide();
                    }
                },
                error: function() {
                    console.error('Error searching products');
                }
            });
        });
        
        // Display product suggestions for retrieval
        function displayRetrieveProductSuggestions(products) {
            let suggestions = '';
            products.forEach(function(product) {
                suggestions += `<div class="product-suggestion-item" data-id="${product.id}" data-available="${product.available_stock}" data-price="${product.wholesale_price}">
                    ${product.product_name}
                    <span class="product-code">Code: ${product.itemcode} | Stock: ${product.available_stock} ${product.unit}</span>
                </div>`;
            });
            
            $('#retrieve-product-suggestions').html(suggestions).show();
            
            // Handle suggestion click
            $('.product-suggestion-item').click(function() {
                const productId = $(this).data('id');
                const productName = $(this).text().trim();
                const availableStock = $(this).data('available');
                const price = $(this).data('price');
                
                selectRetrieveProduct(productId, productName, availableStock, price);
            });
        }
        
        // Handle barcode search for retrieval
        $('#retrieve-search-barcode, #retrieve-barcode-input').on('click keypress', function(e) {
            if (e.type === 'click' || e.which === 13) { // Button click or Enter key
                e.preventDefault();
                
                const barcode = $('#retrieve-barcode-input').val().trim();
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
                                selectRetrieveProduct(product.id, product.product_name, product.available_stock, product.wholesale_price);
                            } else if (response.products.length > 1) {
                                // Multiple products found - show modal
                                showRetrieveDuplicateModal(response.products);
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
                $('#retrieve-barcode-input').val('');
            }
        });
        
        // Show duplicate barcode modal for retrieval
        function showRetrieveDuplicateModal(products) {
            let productList = '';
            products.forEach(function(product) {
                productList += `<a href="#" class="list-group-item list-group-item-action retrieve-duplicate-item" 
                                data-id="${product.id}" 
                                data-name="${product.product_name}" 
                                data-available="${product.available_stock}"
                                data-price="${product.wholesale_price}">
                    <strong>${product.product_name}</strong>
                    <small class="d-block text-muted">Code: ${product.itemcode} | Stock: ${product.available_stock} ${product.unit}</small>
                </a>`;
            });
            
            $('#retrieve-duplicate-list').html(productList);
            $('#retrieve-duplicate-modal').modal('show');
            
            // Handle product selection from modal
            $('.retrieve-duplicate-item').click(function(e) {
                e.preventDefault();
                
                const productId = $(this).data('id');
                const productName = $(this).data('name');
                const availableStock = $(this).data('available');
                const price = $(this).data('price');
                
                selectRetrieveProduct(productId, productName, availableStock, price);
                $('#retrieve-duplicate-modal').modal('hide');
            });
        }
        
        // Select a product for retrieval and update the form
        function selectRetrieveProduct(id, name, availableStock, price) {
            // Update product dropdown
            $('#retrieve-product').html(`<option value="${id}" selected>${name}</option>`);
            
            // Update stock and price fields
            $('#retrieve-available-stock').val(availableStock);
            $('#retrieve-price').val(price.toFixed(2));
            
            // Clear search input and hide suggestions
            $('#retrieve-product-search').val('');
            $('#retrieve-product-suggestions').hide();
            
            // Focus on quantity
            $('#retrieve-quantity').focus();
            
            // Calculate total amount if quantity is already entered
            calculateRetrieveTotal();
        }
        
        // Calculate total amount when quantity changes
        $('#retrieve-quantity').on('input', function() {
            calculateRetrieveTotal();
        });
        
        function calculateRetrieveTotal() {
            const quantity = parseInt($('#retrieve-quantity').val()) || 0;
            const price = parseFloat($('#retrieve-price').val()) || 0;
            
            const total = quantity * price;
            $('#retrieve-total-amount').val(total.toFixed(2));
        }
        
        // Show/hide customer details based on reason
        $('#retrieve-reason').change(function() {
            if($(this).val() === 'sale') {
                $('#customer-details').slideDown(200);
            } else {
                $('#customer-details').slideUp(200);
            }
        });
        
        // Hide product suggestions when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#retrieve-product-search, #retrieve-product-suggestions').length) {
                $('#retrieve-product-suggestions').hide();
            }
        });
        
        // Retrieve form submission
        $('#retrieve-form').submit(function(e) {
            e.preventDefault();
            
            // Get form data
            const stockEntryId = $('#retrieve-product').val();
            const quantity = $('#retrieve-quantity').val();
            const reason = $('#retrieve-reason').val();
            const customer = $('#customer').val();
            const availableStock = parseInt($('#retrieve-available-stock').val());
            
            // Validate form
            if (!stockEntryId || !quantity || !reason) {
                alert('Please fill in all required fields');
                return false;
            }
            
            // Check if quantity is valid
            if (parseInt(quantity) <= 0) {
                alert('Quantity must be greater than 0');
                return false;
            }
            
            // Check if quantity is not more than available stock
            if (parseInt(quantity) > availableStock) {
                alert(`Not enough stock available. Maximum available: ${availableStock}`);
                return false;
            }
            
            // Validate customer name for sales
            if (reason === 'sale' && !customer) {
                alert('Customer name is required for sales');
                return false;
            }
            
            // Send AJAX request
            $.ajax({
                url: 'process/retrieve_stock_process.php',
                type: 'POST',
                data: {
                    stock_entry_id: stockEntryId,
                    quantity: quantity,
                    reason: reason,
                    customer: customer,
                    price: $('#retrieve-price').val(),
                    total_amount: $('#retrieve-total-amount').val()
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Stock retrieved successfully!');
                        
                        // Clear form
                        $('#retrieve-form')[0].reset();
                        $('#retrieve-product').html('<option value="">Select Product</option>');
                        $('#retrieve-available-stock').val('');
                        $('#retrieve-price').val('');
                        $('#retrieve-total-amount').val('');
                        $('#customer-details').hide();
                        
                        // Focus on barcode input for next scan
                        $('#retrieve-barcode-input').focus();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('Error retrieving stock. Please try again.');
                }
            });
        });
        
        // Focus on barcode input when page loads
        $('#retrieve-barcode-input').focus();
    });
</script>
