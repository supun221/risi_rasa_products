<?php
// Return Collection History Section
require_once '../../../../config/databade.php';

// Get session rep_id
session_start();
$rep_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

// Pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Search parameters
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

try {
    // Base query for counting total records
    $count_query = "SELECT COUNT(*) as total FROM return_collections WHERE rep_id = ?";
    $count_params = [$rep_id];
    
    // Base query for fetching return collections
    $query = "SELECT r.*, 
              DATE_FORMAT(r.created_at, '%Y-%m-%d %h:%i %p') as formatted_date,
              COALESCE(c.name, r.customer_name) as display_customer_name
              FROM return_collections r
              LEFT JOIN customers c ON r.customer_id = c.id
              WHERE r.rep_id = ?";
    $params = [$rep_id];
    
    // Add search conditions if provided
    if (!empty($search_term)) {
        $search_param = '%' . $search_term . '%';
        $count_query .= " AND (return_bill_number LIKE ? OR original_invoice_number LIKE ? OR customer_name LIKE ?)";
        $count_params[] = $search_param;
        $count_params[] = $search_param;
        $count_params[] = $search_param;
        
        $query .= " AND (r.return_bill_number LIKE ? OR r.original_invoice_number LIKE ? OR r.customer_name LIKE ?)";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    // Add date range conditions if provided
    if (!empty($date_from)) {
        $count_query .= " AND DATE(created_at) >= ?";
        $count_params[] = $date_from;
        
        $query .= " AND DATE(r.created_at) >= ?";
        $params[] = $date_from;
    }
    
    if (!empty($date_to)) {
        $count_query .= " AND DATE(created_at) <= ?";
        $count_params[] = $date_to;
        
        $query .= " AND DATE(r.created_at) <= ?";
        $params[] = $date_to;
    }
    
    // Order and limit for the main query
    $query .= " ORDER BY r.created_at DESC LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $limit;
    
    // Execute count query
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->bind_param(str_repeat('s', count($count_params)), ...$count_params);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count_row = $count_result->fetch_assoc();
    $total_records = $count_row['total'];
    
    $total_pages = ceil($total_records / $limit);
    
    // Execute main query
    $stmt = $conn->prepare($query);
    $param_types = str_repeat('s', count($params) - 2) . 'ii'; // All strings except the last two which are integers
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Fetch return collections
    $return_collections = [];
    while ($row = $result->fetch_assoc()) {
        $return_collections[] = $row;
    }
    
} catch (Exception $e) {
    $error_message = "Error fetching return collections: " . $e->getMessage();
}
?>

<div class="section-container">
    <div class="section-header">
        <h4><i class="fas fa-history"></i> Return Collection History</h4>
    </div>
    
    <div class="section-body">
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <!-- Filter Form -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Search & Filter</h5>
            </div>
            <div class="card-body">
                <form id="return-filter-form" method="GET">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="search">Search:</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="Return #, Invoice # or Customer" 
                                       value="<?php echo htmlspecialchars($search_term); ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="date_from">Date From:</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" 
                                       value="<?php echo htmlspecialchars($date_from); ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="date_to">Date To:</label>
                                <input type="date" class="form-control" id="date_to" name="date_to"
                                       value="<?php echo htmlspecialchars($date_to); ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group d-flex align-items-end h-100">
                                <button type="submit" class="btn btn-primary mr-2">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <a href="?section=return_history" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Results Table -->
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Return Collections</h5>
                <div>
                    <a href="?section=return_collection" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> New Return
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Return #</th>
                                <th>Date</th>
                                <th>Original Invoice</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Reason</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($return_collections)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">No return collections found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($return_collections as $return): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($return['return_bill_number']); ?></td>
                                        <td><?php echo htmlspecialchars($return['formatted_date']); ?></td>
                                        <td><?php echo htmlspecialchars($return['original_invoice_number']); ?></td>
                                        <td><?php echo htmlspecialchars($return['display_customer_name']); ?></td>
                                        <td>Rs. <?php echo number_format($return['total_amount'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($return['reason']); ?></td>
                                        <td>
                                            <a href="../invoice/pos_return_receipt.php?return_bill=<?php echo urlencode($return['return_bill_number']); ?>" 
                                               class="btn btn-sm btn-secondary" target="_blank">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center mt-4">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?section=return_history&page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search_term); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>">
                                        Previous
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?section=return_history&page=<?php echo $i; ?>&search=<?php echo urlencode($search_term); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?section=return_history&page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search_term); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>">
                                        Next
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Modal for viewing return details -->
        <div class="modal fade" id="returnDetailsModal" tabindex="-1" role="dialog" aria-labelledby="returnDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="returnDetailsModalLabel">Return Details</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="returnDetailsContent">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <p>Loading return details...</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <a href="#" class="btn btn-primary" id="printReturnBtn" target="_blank">
                            <i class="fas fa-print"></i> Print Return
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle view return button clicks
    $('.view-return-btn').click(function() {
        const returnId = $(this).data('return-id');
        const returnBill = $(this).data('return-bill');
        
        // Set the print button URL
        $('#printReturnBtn').attr('href', '/internship project/risi_rasa_products/app/views/invoice/pos_return_receipt.php?return_bill=' + returnBill);
        
        // Show the modal
        $('#returnDetailsModal').modal('show');
        
        // Load return details via AJAX
        $.ajax({
            url: '../process/get_return_details.php',
            method: 'GET',
            data: { return_id: returnId },
            dataType: 'html',
            success: function(response) {
                $('#returnDetailsContent').html(response);
            },
            error: function(xhr, status, error) {
                $('#returnDetailsContent').html(`
                    <div class="alert alert-danger">
                        Error loading return details: ${error}
                    </div>
                `);
            }
        });
    });
    
    // Date range validation
    $('#return-filter-form').submit(function() {
        const dateFrom = $('#date_from').val();
        const dateTo = $('#date_to').val();
        
        if (dateFrom && dateTo && new Date(dateFrom) > new Date(dateTo)) {
            alert('Date From cannot be later than Date To');
            return false;
        }
        
        return true;
    });
});
</script>