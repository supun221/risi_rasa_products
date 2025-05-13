<!-- Customer Modal -->
<div class="modal fade" id="add-customer-modal" tabindex="-1" aria-labelledby="customer-form-title" aria-hidden="true">
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
                    <!-- Hidden field for customer ID (used for updates) -->
                    <input type="hidden" id="customer_id" name="id" value="">
                    
                    <div class="form-group">
                        <label for="customer_name">Name</label>
                        <input type="text" class="form-control" id="customer_name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_telephone">Telephone</label>
                        <input type="tel" class="form-control" id="customer_telephone" name="telephone" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_nic">NIC</label>
                        <input type="text" class="form-control" id="customer_nic" name="nic" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_address">Address</label>
                        <textarea class="form-control" id="customer_address" name="address" rows="2" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_whatsapp">WhatsApp</label>
                        <input type="tel" class="form-control" id="customer_whatsapp" name="whatsapp" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_route_id">Route</label>
                        <select class="form-control" id="customer_route_id" name="route_id">
                            <option value="">-- Select Route --</option>
                            <!-- Routes will be loaded dynamically -->
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_credit_limit">Credit Limit</label>
                        <input type="number" class="form-control" id="customer_credit_limit" name="credit_limit" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-customer-btn">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- No JavaScript here - moved to customers.php -->
