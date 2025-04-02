<?php
// Retrieve stock logic can be placed here
// Connect to database, fetch products, etc.
?>

<div class="section-card fade-transition" id="retrieve-section">
    <div class="section-header">
        <i class="fas fa-dolly"></i> Retrieve from Lorry
    </div>
    <div class="section-body">
        <a href="#" class="return-link" id="return-from-retrieve">
            <i class="fas fa-chevron-left"></i> Return to Dashboard
        </a>
        <form id="retrieve-form">
            <div class="form-group">
                <label for="retrieve-product">Product <span class="required-indicator">*</span></label>
                <select class="form-control" id="retrieve-product" name="product" required>
                    <option value="">Select Product</option>
                    <?php 
                    // In a real implementation, you'd fetch products from database 
                    $products = [['id' => 1, 'name' => 'Product 1'], ['id' => 2, 'name' => 'Product 2'], ['id' => 3, 'name' => 'Product 3']];
                    foreach ($products as $product) {
                        echo "<option value='{$product['id']}'>{$product['name']}</option>";
                    }
                    ?>
                </select>
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
            <button type="submit" class="btn btn-success btn-block">Confirm Retrieval</button>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Show/hide customer details based on reason
        $('#retrieve-reason').change(function() {
            if($(this).val() === 'sale') {
                $('#customer-details').slideDown(200);
            } else {
                $('#customer-details').slideUp(200);
            }
        });
        
        // Retrieve form submission
        $('#retrieve-form').submit(function(e) {
            e.preventDefault();
            
            // Get form data
            const product = $('#retrieve-product').val();
            const quantity = $('#retrieve-quantity').val();
            const reason = $('#retrieve-reason').val();
            const customer = $('#customer').val();
            
            // Validate form
            if (!product || !quantity || !reason) {
                alert('Please fill in all required fields');
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
                    product: product,
                    quantity: quantity,
                    reason: reason,
                    customer: customer
                },
                success: function(response) {
                    // In a real implementation, you'd parse the JSON response
                    alert('Stock retrieved successfully!');
                    $('#retrieve-form')[0].reset();
                    $('#customer-details').hide();
                },
                error: function() {
                    alert('Error retrieving stock. Please try again.');
                }
            });
        });
    });
</script>
