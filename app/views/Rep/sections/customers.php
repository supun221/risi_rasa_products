<?php
// Customers management logic can be placed here
// Connect to database, fetch customers, etc.
?>

<div class="section-card fade-transition" id="customer-section">
    <div class="section-header">
        <i class="fas fa-users"></i> Customer Management
    </div>
    <div class="section-body">
        <a href="#" class="return-link" id="return-from-customer">
            <i class="fas fa-chevron-left"></i> Return to Dashboard
        </a>
        <form id="customer-search-form">
            <div class="form-group">
                <label for="customer-search">Search Customers</label>
                <input type="text" class="form-control" id="customer-search" name="search" placeholder="Search by name or phone">
            </div>
            <button type="submit" class="btn btn-primary mb-3">Search</button>
        </form>
        <div class="table-responsive">
            <table class="table table-striped" id="customers-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Total Purchases</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // In a real implementation, you'd fetch customers from database
                    $customers = [
                        ['id' => 1, 'name' => 'John Doe', 'phone' => '123-456-7890', 'purchases' => '$450.00'],
                        ['id' => 2, 'name' => 'Jane Smith', 'phone' => '987-654-3210', 'purchases' => '$325.00']
                    ];
                    
                    foreach ($customers as $customer) {
                        echo "<tr>
                            <td>{$customer['name']}</td>
                            <td>{$customer['phone']}</td>
                            <td>{$customer['purchases']}</td>
                            <td><button class='btn btn-sm btn-outline-primary view-customer' data-id='{$customer['id']}'>View</button></td>
                        </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Customer search form submission
        $('#customer-search-form').submit(function(e) {
            e.preventDefault();
            
            // Get search term
            const searchTerm = $('#customer-search').val();
            
            // Send AJAX request
            $.ajax({
                url: 'process/search_customers.php',
                type: 'GET',
                data: { term: searchTerm },
                success: function(response) {
                    // In a real implementation, you'd update the table with results
                    alert('Search complete. Results would be displayed here.');
                },
                error: function() {
                    alert('Error searching customers. Please try again.');
                }
            });
        });
        
        // View customer details
        $('.view-customer').click(function() {
            const customerId = $(this).data('id');
            
            // Send AJAX request to get customer details
            $.ajax({
                url: 'process/get_customer.php',
                type: 'GET',
                data: { id: customerId },
                success: function(response) {
                    // In a real implementation, you'd display the customer details
                    alert('Viewing customer ID: ' + customerId);
                },
                error: function() {
                    alert('Error retrieving customer details. Please try again.');
                }
            });
        });
    });
</script>
