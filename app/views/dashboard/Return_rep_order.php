<?php
// Start output buffering to prevent "headers already sent" error
ob_start();
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Handle accept/discard actions early
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['return_id'])) {
        $action = $_POST['action'];
        $return_id = (int)$_POST['return_id'];
        
        require_once '../../../config/databade.php';
        
        try {
            if ($action === 'accept') {
                // Simply update the status to "approved" without adjusting inventory
                $update_query = "UPDATE return_collections SET approval_status = 'approved', processed_by = ?, processed_at = NOW() WHERE id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param("ii", $_SESSION['user_id'], $return_id);
                $stmt->execute();
                
                $success_message = "Return #" . $return_id . " has been approved. Use 'Handle Stock' to manage inventory.";
            } elseif ($action === 'discard') {
                // Update the status to "rejected"
                $update_query = "UPDATE return_collections SET approval_status = 'rejected', processed_by = ?, processed_at = NOW() WHERE id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param("ii", $_SESSION['user_id'], $return_id);
                $stmt->execute();
                
                $success_message = "Return #" . $return_id . " has been rejected.";
            } elseif ($action === 'add_stock') {
                $item_id = (int)$_POST['item_id'];
                $qty_to_add = (int)$_POST['qty_to_add'];
                
                // Get the return item details
                $item_query = "SELECT * FROM return_collection_items WHERE id = ?";
                $stmt = $conn->prepare($item_query);
                $stmt->bind_param("i", $item_id);
                $stmt->execute();
                $item_result = $stmt->get_result();
                $item = $item_result->fetch_assoc();
                
                if ($item && $qty_to_add > 0 && $qty_to_add <= $item['return_qty']) {
                    // Get product ID
                    $product_query = "SELECT id FROM products WHERE product_name = ? LIMIT 1";
                    $stmt = $conn->prepare($product_query);
                    $stmt->bind_param("s", $item['product_name']);
                    $stmt->execute();
                    $product_result = $stmt->get_result();
                    $product = $product_result->fetch_assoc();
                    
                    if ($product) {
                        // Add stock to inventory
                        $update_inventory = "UPDATE stock_entries SET available_stock = available_stock + ? WHERE product_name = ? AND branch = ?";
                        $stmt = $conn->prepare($update_inventory);
                        $branch = $_SESSION['store'];
                        $stmt->bind_param("iss", $qty_to_add, $item['product_name'], $branch);
                        $stmt->execute();
                        
                        // Update return item with stock added info
                        $update_item = "UPDATE return_collection_items SET stock_added = ?, resolved = 1, resolved_by = ?, resolved_at = NOW() WHERE id = ?";
                        $stmt = $conn->prepare($update_item);
                        $stmt->bind_param("iii", $qty_to_add, $_SESSION['user_id'], $item_id);
                        $stmt->execute();
                        
                        $success_message = "Added {$qty_to_add} items to stock for {$item['product_name']}";
                    } else {
                        $error_message = "Could not find product in the database.";
                    }
                } else {
                    $error_message = "Invalid quantity or item not found.";
                }
            } elseif ($action === 'discard_item') {
                $item_id = (int)$_POST['item_id'];
                
                // Mark the item as resolved but don't add stock
                $update_item = "UPDATE return_collection_items SET stock_added = 0, resolved = 1, resolved_by = ?, resolved_at = NOW() WHERE id = ?";
                $stmt = $conn->prepare($update_item);
                $stmt->bind_param("ii", $_SESSION['user_id'], $item_id);
                $stmt->execute();
                
                $success_message = "Item marked as resolved (discarded).";
            }
            
            // Redirect to refresh the page using output buffering for safe redirects
            header("Location: Return_rep_order.php?message=" . urlencode($success_message));
            ob_end_flush(); // Flush the buffer before exiting
            exit();
            
        } catch (Exception $e) {
            $error_message = "Error processing action: " . $e->getMessage();
        }
    }
}

// Now include any other required files
require_once '../header1.php';
require_once '../../../config/databade.php';

// Get current page for pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Search parameters
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

try {
    // Base query for counting total records
    $count_query = "SELECT COUNT(*) as total FROM return_collections WHERE 1=1";
    $count_params = [];
    
    // Base query for fetching return collections
    $query = "SELECT r.*, 
              DATE_FORMAT(r.created_at, '%Y-%m-%d %h:%i %p') as formatted_date,
              COALESCE(c.name, r.customer_name) as display_customer_name,
              s.username as rep_name,
              COALESCE(r.approval_status, 'pending') as status
              FROM return_collections r
              LEFT JOIN customers c ON r.customer_id = c.id
              LEFT JOIN signup s ON r.rep_id = s.id
              WHERE 1=1";
    $params = [];
    
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
    
    // Add status filter if provided
    if (!empty($status_filter)) {
        $count_query .= " AND approval_status = ?";
        $count_params[] = $status_filter;
        
        $query .= " AND r.approval_status = ?";
        $params[] = $status_filter;
    }
    
    // Order and limit for the main query
    $query .= " ORDER BY r.created_at DESC LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $limit;
    
    // Execute count query
    $count_stmt = $conn->prepare($count_query);
    if (!empty($count_params)) {
        $count_stmt->bind_param(str_repeat('s', count($count_params)), ...$count_params);
    }
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count_row = $count_result->fetch_assoc();
    $total_records = $count_row['total'];
    
    $total_pages = ceil($total_records / $limit);
    
    // Execute main query
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $param_types = str_repeat('s', count($params) - 2) . 'ii'; // All strings except the last two which are integers
        $stmt->bind_param($param_types, ...$params);
    } else {
        $stmt->bind_param("ii", $offset, $limit);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Fetch return collections
    $return_collections = [];
    while ($row = $result->fetch_assoc()) {
        // Get total return quantity for this return
        $qty_query = "SELECT SUM(return_qty) as total_qty FROM return_collection_items WHERE return_id = ?";
        $qty_stmt = $conn->prepare($qty_query);
        $qty_stmt->bind_param("i", $row['id']);
        $qty_stmt->execute();
        $qty_result = $qty_stmt->get_result();
        $qty_row = $qty_result->fetch_assoc();
        $row['total_qty'] = $qty_row['total_qty'] ?: 0;
        
        // Get stock added info
        $stock_query = "SELECT SUM(stock_added) as total_stock_added, 
                        COUNT(*) as total_items, 
                        SUM(CASE WHEN resolved = 1 THEN 1 ELSE 0 END) as resolved_items 
                        FROM return_collection_items WHERE return_id = ?";
        $stock_stmt = $conn->prepare($stock_query);
        $stock_stmt->bind_param("i", $row['id']);
        $stock_stmt->execute();
        $stock_result = $stock_stmt->get_result();
        $stock_row = $stock_result->fetch_assoc();
        
        $row['total_stock_added'] = $stock_row['total_stock_added'] ?: 0;
        $row['is_resolved'] = ($stock_row['total_items'] > 0 && $stock_row['total_items'] == $stock_row['resolved_items']);
        
        $return_collections[] = $row;
    }
    
} catch (Exception $e) {
    $error_message = "Error fetching return collections: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Return Orders</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .card {
            margin-bottom: 20px;
        }
        .status-pending {
            color: #856404;
            background-color: #fff3cd;
            border-color: #ffeeba;
        }
        .status-approved {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .status-rejected {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .action-buttons {
            white-space: nowrap;
        }
        .badge-resolution {
            padding: 5px 8px;
            font-size: 0.8rem;
            margin-left: 5px;
        }
        .badge-resolved {
            background-color: #28a745;
            color: white;
        }
        .badge-pending-resolution {
            background-color: #ffc107;
            color: #212529;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2>Sales Return Orders</h2>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['message'])): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($_GET['message']); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Filter Form -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Search & Filter</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="search">Search:</label>
                                        <input type="text" class="form-control" id="search" name="search" 
                                               placeholder="Return #, Invoice # or Customer" 
                                               value="<?php echo htmlspecialchars($search_term); ?>">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="date_from">Date From:</label>
                                        <input type="date" class="form-control" id="date_from" name="date_from" 
                                               value="<?php echo htmlspecialchars($date_from); ?>">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="date_to">Date To:</label>
                                        <input type="date" class="form-control" id="date_to" name="date_to"
                                               value="<?php echo htmlspecialchars($date_to); ?>">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="status">Status:</label>
                                        <select class="form-control" id="status" name="status">
                                            <option value="" <?php echo $status_filter === '' ? 'selected' : ''; ?>>All</option>
                                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                            <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group d-flex align-items-end h-100">
                                        <button type="submit" class="btn btn-primary mr-2">
                                            <i class="fas fa-search"></i> Search
                                        </button>
                                        <a href="Return_rep_order.php" class="btn btn-secondary">
                                            <i class="fas fa-redo"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Results Table -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Return Orders</h5>
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
                                        <th>Rep</th>
                                        <th>Return Qty</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($return_collections)): ?>
                                        <tr>
                                            <td colspan="9" class="text-center">No return orders found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($return_collections as $return): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($return['return_bill_number']); ?></td>
                                                <td><?php echo htmlspecialchars($return['formatted_date']); ?></td>
                                                <td><?php echo htmlspecialchars($return['original_invoice_number']); ?></td>
                                                <td><?php echo htmlspecialchars($return['display_customer_name']); ?></td>
                                                <td><?php echo htmlspecialchars($return['rep_name']); ?></td>
                                                <td>
                                                    <?php echo $return['total_qty']; ?>
                                                    <?php if ($return['is_resolved']): ?>
                                                        <span class="badge badge-resolution badge-resolved">Resolved</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-resolution badge-pending-resolution">Pending</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>Rs. <?php echo number_format($return['total_amount'], 2); ?></td>
                                                <td>
                                                    <?php if ($return['status'] === 'pending'): ?>
                                                        <span class="badge status-pending">Pending</span>
                                                    <?php elseif ($return['status'] === 'approved'): ?>
                                                        <span class="badge status-approved">Approved</span>
                                                    <?php elseif ($return['status'] === 'rejected'): ?>
                                                        <span class="badge status-rejected">Rejected</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="action-buttons">
                                                    <button type="button" class="btn btn-sm btn-info view-return-btn" 
                                                       data-return-id="<?php echo $return['id']; ?>" 
                                                       data-return-bill="<?php echo $return['return_bill_number']; ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    
                                                    <?php if ($return['status'] === 'pending'): ?>
                                                        <form method="post" action="" style="display:inline;">
                                                            <input type="hidden" name="return_id" value="<?php echo $return['id']; ?>">
                                                            <input type="hidden" name="action" value="accept">
                                                            <button type="submit" class="btn btn-sm btn-success" 
                                                                    onclick="return confirm('Are you sure you want to approve this return?')">
                                                                <i class="fas fa-check"></i> Accept
                                                            </button>
                                                        </form>
                                                        
                                                        <form method="post" action="" style="display:inline;">
                                                            <input type="hidden" name="return_id" value="<?php echo $return['id']; ?>">
                                                            <input type="hidden" name="action" value="discard">
                                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                                    onclick="return confirm('Are you sure you want to reject this return?')">
                                                                <i class="fas fa-times"></i> Discard
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($return['status'] === 'approved' && !$return['is_resolved']): ?>
                                                        <button type="button" class="btn btn-sm btn-warning handle-stock-btn" 
                                                           data-return-id="<?php echo $return['id']; ?>">
                                                            <i class="fas fa-boxes"></i> Handle Stock
                                                        </button>
                                                    <?php endif; ?>
                                                    
                                                    <a href="/internship project/risi_rasa_products/app/views/invoice/pos_return_receipt.php?return_bill=<?php echo urlencode($return['return_bill_number']); ?>" 
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
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search_term); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&status=<?php echo urlencode($status_filter); ?>">
                                                Previous
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search_term); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&status=<?php echo urlencode($status_filter); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search_term); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>&status=<?php echo urlencode($status_filter); ?>">
                                                Next
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
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
    
    <!-- Modal for handling stock -->
    <div class="modal fade" id="handleStockModal" tabindex="-1" role="dialog" aria-labelledby="handleStockModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="handleStockModalLabel">Handle Return Stock</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="handleStockContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p>Loading return items...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal for adding stock -->
    <div class="modal fade" id="addStockModal" tabindex="-1" role="dialog" aria-labelledby="addStockModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStockModalLabel">Add to Stock</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" action="" id="addStockForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_stock">
                        <input type="hidden" name="item_id" id="add_stock_item_id">
                        <input type="hidden" name="return_id" id="add_stock_return_id">
                        
                        <div class="form-group">
                            <label for="product_name">Product:</label>
                            <input type="text" class="form-control" id="product_name" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="available_qty">Available Return Quantity:</label>
                            <input type="text" class="form-control" id="available_qty" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="qty_to_add">Quantity to Add to Stock:</label>
                            <input type="number" class="form-control" id="qty_to_add" name="qty_to_add" min="1" required>
                        </div>
                        
                        <div class="alert alert-info">
                            <small>You can add up to the available return quantity to stock.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add to Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Required JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Handle view return button clicks
        $('.view-return-btn').click(function() {
            const returnId = $(this).data('return-id');
            const returnBill = $(this).data('return-bill');
            
            // Set the print button URL
            $('#printReturnBtn').attr('href', '../invoice/pos_return_receipt.php?return_bill=' + returnBill);
            
            // Show the modal
            $('#returnDetailsModal').modal('show');
            
            // Load return details via AJAX
            $.ajax({
                url: '../Rep/process/get_return_details.php',
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
        
        // Handle stock button clicks
        $('.handle-stock-btn').click(function() {
            const returnId = $(this).data('return-id');
            
            // Show the modal
            $('#handleStockModal').modal('show');
            
            // Load return items via AJAX
            $.ajax({
                url: 'process/get_return_items.php',
                method: 'GET',
                data: { return_id: returnId },
                dataType: 'html',
                success: function(response) {
                    $('#handleStockContent').html(response);
                },
                error: function(xhr, status, error) {
                    $('#handleStockContent').html(`
                        <div class="alert alert-danger">
                            Error loading return items: ${error}
                        </div>
                    `);
                }
            });
        });
        
        // Handle add stock button clicks from the stock modal
        $(document).on('click', '.add-stock-btn', function() {
            const itemId = $(this).data('item-id');
            const returnId = $(this).data('return-id');
            const productName = $(this).data('product-name');
            const returnQty = $(this).data('return-qty');
            
            $('#add_stock_item_id').val(itemId);
            $('#add_stock_return_id').val(returnId);
            $('#product_name').val(productName);
            $('#available_qty').val(returnQty);
            $('#qty_to_add').attr('max', returnQty);
            $('#qty_to_add').val(returnQty); // Default to full quantity
            
            $('#handleStockModal').modal('hide');
            $('#addStockModal').modal('show');
        });
        
        // Validate add stock form
        $('#addStockForm').submit(function() {
            const qtyToAdd = parseInt($('#qty_to_add').val());
            const availableQty = parseInt($('#available_qty').val());
            
            if (isNaN(qtyToAdd) || qtyToAdd <= 0) {
                alert('Please enter a valid quantity.');
                return false;
            }
            
            if (qtyToAdd > availableQty) {
                alert('Cannot add more than the available return quantity.');
                return false;
            }
            
            return true;
        });
        
        // Date range validation
        $('form').submit(function() {
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
</body>
</html>
