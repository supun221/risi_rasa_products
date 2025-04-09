<?php
// Stock management logic
require_once '../../../../config/databade.php';
session_start();

// Get rep_id from session
$rep_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;

// Fetch current lorry stock
$stmt = $conn->prepare("
    SELECT ls.id, ls.product_name, ls.itemcode, ls.quantity, ls.unit_price, ls.barcode, ls.date_added
    FROM lorry_stock ls
    WHERE ls.rep_id = ? AND ls.status = 'active' AND ls.quantity > 0
    ORDER BY ls.product_name
");
$stmt->bind_param("i", $rep_id);
$stmt->execute();
$result = $stmt->get_result();
$stock_items = [];
while ($row = $result->fetch_assoc()) {
    $stock_items[] = $row;
}

// Get stock statistics
$total_items = count($stock_items);
$total_stock_value = 0;
foreach ($stock_items as $item) {
    $total_stock_value += $item['quantity'] * $item['unit_price'];
}
?>

<div class="section-card fade-transition" id="stock-management-section">
    <div class="section-header">
        <i class="fas fa-truck"></i> Stock Management
    </div>
    <div class="section-body">
        <a href="#" class="return-link" id="return-from-stock-management">
            <i class="fas fa-chevron-left"></i> Return to Dashboard
        </a>
        
        <!-- Stock Overview Cards -->
        <div class="row mb-4">
            <div class="col-md-6 col-sm-6 mb-3">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-boxes fa-3x mr-3"></i>
                            <div>
                                <h6 class="mb-0">Total Products</h6>
                                <h3 class="mb-0 mt-2"><?php echo $total_items; ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-6 mb-3">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-money-bill-wave fa-3x mr-3"></i>
                            <div>
                                <h6 class="mb-0">Stock Value</h6>
                                <h3 class="mb-0 mt-2">Rs. <?php echo number_format($total_stock_value, 2); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Stock Actions -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Stock Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <a href="#" class="btn btn-primary btn-block stock-action-btn" id="add-to-lorry-btn">
                            <i class="fas fa-plus-circle"></i> Add to Lorry Stock
                        </a>
                    </div>
                    <!-- <div class="col-md-6 mb-3">
                        <a href="#" class="btn btn-info btn-block stock-action-btn" id="retrieve-stock-btn">
                            <i class="fas fa-dolly"></i> Retrieve from Lorry
                        </a>
                    </div> -->
                </div>
            </div>
        </div>
        
        <!-- Current Stock Table -->
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Current Lorry Stock</h5>
                <div class="form-inline">
                    <input type="text" class="form-control form-control-sm mr-2" id="stock-search" placeholder="Search products...">
                    <button class="btn btn-sm btn-outline-secondary" id="refresh-stock">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="stock-items-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Code</th>
                                <th>Qty</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($stock_items)): ?>
                                <tr>
                                    <td colspan="6" class="text-center">No items found in your lorry stock</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($stock_items as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                        <td><?php echo htmlspecialchars($item['itemcode'] ?? 'N/A'); ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>Rs. <?php echo number_format($item['unit_price'], 2); ?></td>
                                        <td>Rs. <?php echo number_format($item['quantity'] * $item['unit_price'], 2); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary view-stock-btn" data-id="<?php echo $item['id']; ?>" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-info quick-retrieve-btn" data-id="<?php echo $item['id']; ?>" data-name="<?php echo htmlspecialchars($item['product_name']); ?>" data-qty="<?php echo $item['quantity']; ?>" title="Quick Retrieve">
                                                <i class="fas fa-minus-circle"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Retrieve Modal -->
<div class="modal fade" id="quick-retrieve-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Quick Retrieve</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="quick-retrieve-form">
                    <input type="hidden" id="quick-retrieve-id" name="stock_id">
                    
                    <div class="form-group">
                        <label for="quick-retrieve-product">Product</label>
                        <input type="text" class="form-control" id="quick-retrieve-product" readonly>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="quick-retrieve-available">Available Qty</label>
                            <input type="text" class="form-control" id="quick-retrieve-available" readonly>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="quick-retrieve-qty">Retrieve Qty <span class="required-indicator">*</span></label>
                            <input type="number" class="form-control" id="quick-retrieve-qty" name="quantity" min="1" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="quick-retrieve-reason">Reason <span class="required-indicator">*</span></label>
                        <select class="form-control" id="quick-retrieve-reason" name="reason" required>
                            <option value="">Select Reason</option>
                            <option value="damage">Damaged Goods</option>
                            <option value="return">Return to Warehouse</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="quick-retrieve-note">Note</label>
                        <textarea class="form-control" id="quick-retrieve-note" name="note" rows="2"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirm-quick-retrieve">Confirm Retrieve</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Filter stock items on search
    $('#stock-search').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        $('#stock-items-table tbody tr').each(function() {
            const rowContent = $(this).text().toLowerCase();
            if (rowContent.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Refresh stock data
    $('#refresh-stock').click(function() {
        // Show loading spinner
        $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        
        // Reload the page or fetch updated data via AJAX
        location.reload();
    });
    
    // Add to Lorry button click
    $('#add-to-lorry-btn').click(function() {
        // Load add to lorry section
        $.ajax({
            url: 'sections/add_to_lorry.php',
            type: 'GET',
            success: function(response) {
                // Replace content and add animation
                $('#dynamic-content').html(response).hide().fadeIn(300);
                
                // Update URL
                history.pushState({page: "add_to_lorry"}, "Add to Lorry", "?section=add_to_lorry");
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load Add to Lorry section'
                });
            }
        });
    });
    
    // Retrieve Stock button click
    $('#retrieve-stock-btn').click(function() {
        // Load retrieve stock section
        $.ajax({
            url: 'sections/retrieve_stock.php',
            type: 'GET',
            success: function(response) {
                // Replace content and add animation
                $('#dynamic-content').html(response).hide().fadeIn(300);
                
                // Update URL
                history.pushState({page: "retrieve_stock"}, "Retrieve Stock", "?section=retrieve_stock");
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load Retrieve Stock section'
                });
            }
        });
    });
    
    // View stock item details
    $('.view-stock-btn').click(function() {
        const stockId = $(this).data('id');
        
        // Show loading spinner
        $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        
        // Fetch stock item details via AJAX
        $.ajax({
            url: 'process/get_stock_item_details.php',
            type: 'GET',
            data: { id: stockId },
            dataType: 'json',
            success: function(response) {
                // Reset button
                $('.view-stock-btn[data-id="' + stockId + '"]').html('<i class="fas fa-eye"></i>');
                
                if (response.success) {
                    // Show stock item details in modal or alert
                    Swal.fire({
                        title: response.item.product_name,
                        html: `
                            <table class="table table-sm">
                                <tr>
                                    <th>Code:</th>
                                    <td>${response.item.itemcode || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Barcode:</th>
                                    <td>${response.item.barcode || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Quantity:</th>
                                    <td>${response.item.quantity}</td>
                                </tr>
                                <tr>
                                    <th>Unit Price:</th>
                                    <td>Rs. ${parseFloat(response.item.unit_price).toFixed(2)}</td>
                                </tr>
                                <tr>
                                    <th>Total Value:</th>
                                    <td>Rs. ${(response.item.quantity * response.item.unit_price).toFixed(2)}</td>
                                </tr>
                                <tr>
                                    <th>Added On:</th>
                                    <td>${new Date(response.item.date_added).toLocaleString()}</td>
                                </tr>
                            </table>
                        `,
                        showCloseButton: true,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to load item details'
                    });
                }
            },
            error: function() {
                // Reset button
                $('.view-stock-btn[data-id="' + stockId + '"]').html('<i class="fas fa-eye"></i>');
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load item details'
                });
            }
        });
    });
    
    // Quick Retrieve button click
    $('.quick-retrieve-btn').click(function() {
        const stockId = $(this).data('id');
        const productName = $(this).data('name');
        const availableQty = $(this).data('qty');
        
        // Populate modal
        $('#quick-retrieve-id').val(stockId);
        $('#quick-retrieve-product').val(productName);
        $('#quick-retrieve-available').val(availableQty);
        $('#quick-retrieve-qty').val(1);
        $('#quick-retrieve-qty').attr('max', availableQty);
        
        // Show modal
        $('#quick-retrieve-modal').modal('show');
    });
    
    // Confirm Quick Retrieve button click
    $('#confirm-quick-retrieve').click(function() {
        // Validate form
        if (!$('#quick-retrieve-form')[0].checkValidity()) {
            $('#quick-retrieve-form')[0].reportValidity();
            return;
        }
        
        const stockId = $('#quick-retrieve-id').val();
        const quantity = $('#quick-retrieve-qty').val();
        const reason = $('#quick-retrieve-reason').val();
        const note = $('#quick-retrieve-note').val();
        const availableQty = parseInt($('#quick-retrieve-available').val());
        
        // Validate quantity
        if (parseInt(quantity) > availableQty) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Quantity',
                text: `You can't retrieve more than ${availableQty} units.`
            });
            return;
        }
        
        // Show loading on button
        $('#confirm-quick-retrieve').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...')
            .prop('disabled', true);
        
        // Send AJAX request to process quick retrieve
        $.ajax({
            url: 'process/quick_retrieve_stock.php',
            type: 'POST',
            data: {
                stock_id: stockId,
                quantity: quantity,
                reason: reason,
                note: note
            },
            dataType: 'json',
            success: function(response) {
                // Reset button
                $('#confirm-quick-retrieve').html('Confirm Retrieve')
                    .prop('disabled', false);
                
                // Close modal
                $('#quick-retrieve-modal').modal('hide');
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Stock retrieved successfully.'
                    }).then(() => {
                        // Reload the page to reflect changes
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to retrieve stock.'
                    });
                }
            },
            error: function() {
                // Reset button
                $('#confirm-quick-retrieve').html('Confirm Retrieve')
                    .prop('disabled', false);
                
                // Close modal
                $('#quick-retrieve-modal').modal('hide');
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to process request. Please try again.'
                });
            }
        });
    });
});
</script>
