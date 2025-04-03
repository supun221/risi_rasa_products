<!-- Add/Edit Customer Modal -->
<div class="modal fade" id="add-customer-modal" tabindex="-1" aria-labelledby="add-customer-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customer-form-title">Add New Customer</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="customer-form">
                    <input type="hidden" id="customer_id" name="id" value="">
                    
                    <div class="form-group">
                        <label for="customer_name">Name <span class="required-indicator">*</span></label>
                        <input type="text" class="form-control" id="customer_name" name="name" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="customer_telephone">Telephone <span class="required-indicator">*</span></label>
                                <input type="text" class="form-control" id="customer_telephone" name="telephone" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="customer_whatsapp">WhatsApp <span class="required-indicator">*</span></label>
                                <input type="text" class="form-control" id="customer_whatsapp" name="whatsapp" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_nic">NIC <span class="required-indicator">*</span></label>
                        <input type="text" class="form-control" id="customer_nic" name="nic" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_address">Address <span class="required-indicator">*</span></label>
                        <textarea class="form-control" id="customer_address" name="address" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_credit_limit">Credit Limit <span class="required-indicator">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rs.</span>
                            </div>
                            <input type="text" class="form-control" id="customer_credit_limit" name="credit_limit" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-customer">Save Customer</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Reset form when modal is closed
    $('#add-customer-modal').on('hidden.bs.modal', function() {
        $('#customer-form')[0].reset();
        $('#customer_id').val('');
        $('#customer-form-title').text('Add New Customer');
    });
    
    // Save customer button click
    $('#save-customer').click(function() {
        // Check if form is valid
        if (!$('#customer-form')[0].checkValidity()) {
            $('#customer-form')[0].reportValidity();
            return;
        }
        
        // Get form data
        const formData = {
            id: $('#customer_id').val(),
            name: $('#customer_name').val(),
            telephone: $('#customer_telephone').val(),
            nic: $('#customer_nic').val(),
            address: $('#customer_address').val(),
            whatsapp: $('#customer_whatsapp').val(),
            credit_limit: $('#customer_credit_limit').val()
        };
        
        // Determine if this is an add or update operation
        const isUpdate = formData.id !== '';
        const url = isUpdate ? 'process/update_customer.php' : 'process/add_customer.php';
        
        // Send AJAX request
        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show success message
                    alert(response.message);
                    
                    // Close modal
                    $('#add-customer-modal').modal('hide');
                    
                    // Refresh customer list
                    refreshCustomerList();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error saving customer. Please try again.');
            }
        });
    });
    
    // Function to refresh the customer list
    function refreshCustomerList() {
        const searchTerm = $('#customer-search').val();
        
        $.ajax({
            url: 'process/search_customers.php',
            type: 'GET',
            data: { term: searchTerm || '' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    updateCustomerTable(response.customers);
                }
            }
        });
    }
    
    // Function to update the customer table (defined in parent scope)
    function updateCustomerTable(customers) {
        // This function is defined in the parent scope (customers.php)
        // We're just declaring it here to avoid linting errors
        if (typeof window.updateCustomerTable === 'function') {
            window.updateCustomerTable(customers);
        }
    }
    
    // Phone number input validation
    $('#customer_telephone, #customer_whatsapp').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
});
</script>
