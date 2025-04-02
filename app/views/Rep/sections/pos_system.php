<?php
// POS system logic can be placed here
// Connect to database, fetch products, etc.
?>

<div class="section-card fade-transition" id="pos-section">
    <div class="section-header">
        <i class="fas fa-cash-register"></i> POS System
    </div>
    <div class="section-body">
        <a href="#" class="return-link" id="return-from-pos">
            <i class="fas fa-chevron-left"></i> Return to Dashboard
        </a>
        <form id="pos-form">
            <div class="form-group">
                <label for="pos-product">Product <span class="required-indicator">*</span></label>
                <select class="form-control" id="pos-product" name="product" required>
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
                <label for="pos-quantity">Quantity <span class="required-indicator">*</span></label>
                <input type="number" class="form-control" id="pos-quantity" name="quantity" min="1" required>
            </div>
            <div class="form-group">
                <label for="customer-name">Customer <span class="required-indicator">*</span></label>
                <input type="text" class="form-control" id="customer-name" name="customer" required placeholder="Enter customer name">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Complete Sale</button>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        // POS form submission
        $('#pos-form').submit(function(e) {
            e.preventDefault();
            
            // Get form data
            const product = $('#pos-product').val();
            const quantity = $('#pos-quantity').val();
            const customer = $('#customer-name').val();
            
            // Validate form
            if (!product || !quantity || !customer) {
                alert('Please fill in all required fields');
                return false;
            }
            
            // Send AJAX request
            $.ajax({
                url: 'process/pos_process.php',
                type: 'POST',
                data: {
                    product: product,
                    quantity: quantity,
                    customer: customer
                },
                success: function(response) {
                    // In a real implementation, you'd parse the JSON response
                    alert('Sale completed successfully!');
                    $('#pos-form')[0].reset();
                },
                error: function() {
                    alert('Error processing sale. Please try again.');
                }
            });
        });
    });
</script>
