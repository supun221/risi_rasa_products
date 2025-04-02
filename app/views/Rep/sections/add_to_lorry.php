<?php
// Add to Lorry logic can be placed here
// Connect to database, fetch products, etc.
?>

<div class="section-card fade-transition" id="add-section">
    <div class="section-header">
        <i class="fas fa-plus-circle"></i> Add to Lorry Stock
    </div>
    <div class="section-body">
        <a href="#" class="return-link" id="return-from-add">
            <i class="fas fa-chevron-left"></i> Return to Dashboard
        </a>
        <form id="add-to-lorry-form">
            <div class="form-group">
                <label for="product">Product <span class="required-indicator">*</span></label>
                <select class="form-control" id="product" name="product" required>
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
                <label for="quantity">Quantity <span class="required-indicator">*</span></label>
                <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                <div class="helper-text">Enter the number of units to add to the lorry</div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Add to Lorry</button>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Add to lorry form submission
        $('#add-to-lorry-form').submit(function(e) {
            e.preventDefault();
            
            // Get form data
            const product = $('#product').val();
            const quantity = $('#quantity').val();
            
            // Validate form
            if (!product || !quantity) {
                alert('Please fill in all required fields');
                return false;
            }
            
            // Send AJAX request
            $.ajax({
                url: 'process/add_to_lorry_process.php',
                type: 'POST',
                data: {
                    product: product,
                    quantity: quantity
                },
                success: function(response) {
                    // In a real implementation, you'd parse the JSON response
                    alert('Product added to lorry successfully!');
                    $('#add-to-lorry-form')[0].reset();
                },
                error: function() {
                    alert('Error adding product to lorry. Please try again.');
                }
            });
        });
    });
</script>
