<?php
// Return Collection Section

// Check for AJAX request to load invoice data
if (isset($_POST['invoice_number']) && !empty($_POST['invoice_number']) && empty($_POST['submit_return'])) {
    // This is an AJAX request, so we should only return JSON
    header('Content-Type: application/json');
    
    try {
        require_once '../../../../config/databade.php';
        
        if (!isset($conn) || $conn->connect_error) {
            throw new Exception("Database connection failed: " . $conn->connect_error);
        }
        
        $invoice_number = $conn->real_escape_string($_POST['invoice_number']);
        
        // Get sale details from pos_sales table
        $sale_query = "SELECT id as sale_id, customer_id, 
                      COALESCE(customer_name, 'Walk-in Customer') as customer_name, 
                      sale_date, total_amount
                      FROM pos_sales 
                      WHERE invoice_number = ?";
        
        $stmt = $conn->prepare($sale_query);
        if (!$stmt) {
            throw new Exception("Query preparation failed: " . $conn->error);
        }
        
        $stmt->bind_param("s", $invoice_number);
        $stmt->execute();
        $sale_result = $stmt->get_result();
        
        if ($sale_result->num_rows === 0) {
            throw new Exception("Invoice not found");
        }
        
        $sale_data = $sale_result->fetch_assoc();
        
        // Get sale items from pos_sale_items table
        $items_query = "SELECT id as sale_item_id, product_name, unit_price, quantity, 
                       subtotal as total_price
                       FROM pos_sale_items 
                       WHERE sale_id = ?";
        
        $stmt = $conn->prepare($items_query);
        if (!$stmt) {
            throw new Exception("Item query preparation failed: " . $conn->error);
        }
        
        $stmt->bind_param("i", $sale_data['sale_id']);
        $stmt->execute();
        $items_result = $stmt->get_result();
        
        $items = [];
        while ($item = $items_result->fetch_assoc()) {
            $items[] = $item;
        }
        
        echo json_encode([
            'sale' => $sale_data,
            'items' => $items
        ]);
    } 
    catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    
    // Important: exit after sending JSON response to prevent any other output
    exit;
}

// Check for success message from process file
$success_message = '';
$return_bill_number = '';
if (isset($_GET['success']) && $_GET['success'] == 1 && isset($_GET['return_bill'])) {
    $return_bill_number = htmlspecialchars($_GET['return_bill']);
    $success_message = "Return processed successfully. Return Bill Number: " . $return_bill_number;
}
?>

<div class="section-container">
    <div class="section-header">
        <h4><i class="fas fa-undo-alt"></i> Return Collection</h4>
    </div>
    
    <div class="section-body">
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Search Form -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Find Invoice</h5>
            </div>
            <div class="card-body">
                <form id="invoice-search-form">
                    <div class="form-group">
                        <label for="invoice_number">Enter Invoice Number:</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="invoice_number" name="invoice_number" 
                                   placeholder="e.g. INV-20250430-1234" required>
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary" id="search-btn">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Return Form (initially hidden) -->
        <div id="return-form-container" style="display: none;">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Process Return</h5>
                </div>
                <div class="card-body">
                    <form id="return-form" method="POST" action="../process/process_return_collection.php">
                        <input type="hidden" name="original_invoice" id="original_invoice">
                        <input type="hidden" name="sale_id" id="sale_id">
                        <input type="hidden" name="customer_id" id="customer_id">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Invoice Number:</label>
                                    <div class="form-control-plaintext" id="display_invoice_number"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Customer:</label>
                                    <input type="text" class="form-control" name="customer_name" id="customer_name" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Sale Date:</label>
                                    <div class="form-control-plaintext" id="sale_date"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Original Total:</label>
                                    <div class="form-control-plaintext" id="original_total"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="return_reason">Return Reason:</label>
                                    <select class="form-control" name="return_reason" id="return_reason" required>
                                        <option value="">Select a reason</option>
                                        <option value="Damaged Product">Damaged Product</option>
                                        <option value="Customer Dissatisfied">Customer Dissatisfied</option>
                                        <option value="Wrong Product">Wrong Product</option>
                                        <option value="Expired Product">Expired Product</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="return_notes">Notes:</label>
                                    <textarea class="form-control" name="return_notes" id="return_notes" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered" id="return-items-table">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Product</th>
                                        <th>Unit Price</th>
                                        <th>Original Qty</th>
                                        <th>Return Qty</th>
                                        <th>Return Amount</th>
                                    </tr>
                                </thead>
                                <tbody id="return-items-body">
                                    <!-- Items will be added here dynamically -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4" class="text-right">Total Return Amount:</th>
                                        <th id="total-return-amount">Rs. 0.00</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="text-center mt-4">
                            <button type="submit" name="submit_return" class="btn btn-success btn-lg" id="submit-return-btn">
                                <i class="fas fa-save"></i> Process Return
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Search for invoice
    $('#invoice-search-form').submit(function(e) {
        e.preventDefault();
        
        const invoiceNumber = $('#invoice_number').val().trim();
        if (!invoiceNumber) return;
        
        $('#search-btn').html('<i class="fas fa-spinner fa-spin"></i> Searching...');
        
        $.ajax({
            url: 'sections/return_collection.php', // Use the direct path to this file
            method: 'POST',
            data: { invoice_number: invoiceNumber },
            dataType: 'json',
            success: function(response) {
                $('#search-btn').html('<i class="fas fa-search"></i> Search');
                
                if (response.error) {
                    alert(response.error);
                    return;
                }
                
                // Populate form fields with sale data
                $('#original_invoice').val(invoiceNumber);
                $('#sale_id').val(response.sale.sale_id);
                $('#customer_id').val(response.sale.customer_id);
                $('#customer_name').val(response.sale.customer_name);
                
                $('#display_invoice_number').text(invoiceNumber);
                $('#sale_date').text(formatDate(response.sale.sale_date));
                $('#original_total').text('Rs. ' + parseFloat(response.sale.total_amount).toFixed(2));
                
                // Clear previous items
                $('#return-items-body').empty();
                
                // Populate items table
                if (response.items && response.items.length > 0) {
                    response.items.forEach(function(item, index) {
                        const row = $('<tr>');
                        
                        row.append(`<td>
                            ${item.product_name}
                            <input type="hidden" name="item_id[${index}]" value="${item.sale_item_id}">
                            <input type="hidden" name="product_name[${index}]" value="${item.product_name}">
                            <input type="hidden" name="unit_price[${index}]" value="${item.unit_price}">
                            <input type="hidden" name="original_qty[${index}]" value="${item.quantity}">
                        </td>`);
                        
                        row.append(`<td>Rs. ${parseFloat(item.unit_price).toFixed(2)}</td>`);
                        row.append(`<td>${item.quantity}</td>`);
                        
                        // Return quantity input with constraints
                        row.append(`<td>
                            <input type="number" class="form-control return-qty" 
                                   name="return_qty[${index}]" value="0" 
                                   min="0" max="${item.quantity}"
                                   data-price="${item.unit_price}">
                        </td>`);
                        
                        row.append(`<td class="return-amount">Rs. 0.00</td>`);
                        
                        $('#return-items-body').append(row);
                    });
                    
                    // Show the return form
                    $('#return-form-container').show();
                    
                    // Initialize return quantity changes
                    initReturnQtyEvents();
                } else {
                    alert('No items found for this invoice');
                }
            },
            error: function(xhr, status, error) {
                $('#search-btn').html('<i class="fas fa-search"></i> Search');
                console.error("AJAX Error: ", status, error);
                console.log("Response: ", xhr.responseText);
                alert('Error fetching invoice data. Please check console for details.');
            }
        });
    });
    
    // Initialize return quantity events
    function initReturnQtyEvents() {
        $('.return-qty').on('input', function() {
            const qty = parseInt($(this).val()) || 0;
            const unitPrice = parseFloat($(this).data('price'));
            const returnAmount = qty * unitPrice;
            
            // Format and update the amount cell
            $(this).closest('tr').find('.return-amount').text('Rs. ' + returnAmount.toFixed(2));
            
            // Update total
            calculateTotal();
        });
    }
    
    // Calculate total return amount
    function calculateTotal() {
        let total = 0;
        $('.return-qty').each(function() {
            const qty = parseInt($(this).val()) || 0;
            const unitPrice = parseFloat($(this).data('price'));
            total += qty * unitPrice;
        });
        
        $('#total-return-amount').text('Rs. ' + total.toFixed(2));
    }
    
    // Format date helper
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-LK', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    // Form validation before submit
    $('#return-form').submit(function(e) {
        let hasReturnItems = false;
        $('.return-qty').each(function() {
            if (parseInt($(this).val()) > 0) {
                hasReturnItems = true;
                return false; // break the loop
            }
        });
        
        if (!hasReturnItems) {
            e.preventDefault();
            alert('Please enter at least one item to return');
            return false;
        }
        
        if (!$('#return_reason').val()) {
            e.preventDefault();
            alert('Please select a return reason');
            return false;
        }
        
        // Show processing indicator and disable button to prevent multiple submissions
        $('#submit-return-btn').html('<i class="fas fa-spinner fa-spin"></i> Processing...').prop('disabled', true);
        
        // Use AJAX to submit the form to ensure proper handling
        e.preventDefault();
        
        // Get form data and explicitly add the submit_return parameter
        var formData = $(this).serialize();
        formData += '&submit_return=1'; // Add the missing parameter
        
        $.ajax({
            // Use absolute path from web root instead of relative path
            url: '../Rep/process/process_return_collection.php',
            type: 'POST',
            data: formData, // Use our modified formData that includes submit_return
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show success message and redirect if needed
                    window.location.href = response.redirect_url;
                } else {
                    // Show error message
                    alert('Error: ' + response.message);
                    // Re-enable the submit button
                    $('#submit-return-btn').html('<i class="fas fa-save"></i> Process Return').prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error);
                console.log("Response:", xhr.responseText);
                alert('Error processing return. Please check the console for details.');
                // Re-enable the submit button
                $('#submit-return-btn').html('<i class="fas fa-save"></i> Process Return').prop('disabled', false);
            }
        });
    });
    
    // Show a print view of the return receipt after successful submission
    if ($('.alert-success').length > 0) {
        // Extract return bill number from success message
        const successMsg = $('.alert-success').text();
        const returnBillMatch = successMsg.match(/RTN-\d+-\d+/);
        
        if (returnBillMatch) {
            const returnBillNumber = returnBillMatch[0];
            
            // Add print button next to success message
            $('.alert-success').append(
                `<div class="mt-3">
                    <button class="btn btn-info" id="print-return-btn">
                        <i class="fas fa-print"></i> Print Return Receipt
                    </button>
                    <button class="btn btn-secondary ml-2" id="new-return-btn">
                        <i class="fas fa-plus"></i> Process Another Return
                    </button>
                </div>`
            );
            
            // Handle print button click
            $('#print-return-btn').click(function() {
                // You can implement print functionality here
                // For now, just show an alert
                alert('Printing return receipt for ' + returnBillNumber);
                
                // Alternatively, open a new window with the receipt
                // window.open('print_return_receipt.php?return_id=' + returnBillNumber, '_blank');
            });
            
            // Handle new return button click
            $('#new-return-btn').click(function() {
                // Clear the form and hide success message
                $('.alert-success').hide();
                $('#invoice_number').val('');
                $('#return-form-container').hide();
            });
        }
    }
});
</script>