<?php
/**
 * Get Return Items Process
 * This file fetches the items of a specific return for the handle stock modal
 */

// Database connection
require_once '../../../../config/databade.php';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get return ID from request
$return_id = isset($_GET['return_id']) ? (int)$_GET['return_id'] : 0;

// Validate return ID
if ($return_id <= 0) {
    echo '<div class="alert alert-danger">Invalid return ID provided</div>';
    exit;
}

try {
    // Fetch return header information
    $header_query = "SELECT r.*, 
                    DATE_FORMAT(r.created_at, '%Y-%m-%d %h:%i %p') as formatted_date,
                    CONCAT(s.username) as rep_name
                    FROM return_collections r
                    LEFT JOIN signup s ON r.rep_id = s.id
                    WHERE r.id = ?";
                    
    $stmt = $conn->prepare($header_query);
    if (!$stmt) {
        throw new Exception('Failed to prepare header query: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $return_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo '<div class="alert alert-warning">Return record not found</div>';
        exit;
    }
    
    $return = $result->fetch_assoc();
    
    // Fetch return items
    $items_query = "SELECT ri.*, 
                   (ri.return_qty - COALESCE(ri.stock_added, 0)) as remaining_qty,
                   CASE WHEN ri.resolved = 1 THEN 
                       CONCAT(s.username, ' on ', DATE_FORMAT(ri.resolved_at, '%Y-%m-%d %h:%i %p'))
                   ELSE '' END as resolved_by_info
                   FROM return_collection_items ri
                   LEFT JOIN signup s ON ri.resolved_by = s.id
                   WHERE ri.return_id = ?
                   ORDER BY ri.id ASC";
    $stmt = $conn->prepare($items_query);
    if (!$stmt) {
        throw new Exception('Failed to prepare items query: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $return_id);
    $stmt->execute();
    $items_result = $stmt->get_result();
    
    $items = [];
    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }
    
    // Output HTML content for modal
    ?>
    <div class="card mb-3">
        <div class="card-header bg-light">
            <h6 class="mb-0">Return #<?php echo htmlspecialchars($return['return_bill_number']); ?></h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Customer:</strong> <?php echo htmlspecialchars($return['customer_name']); ?></p>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars($return['formatted_date']); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Original Invoice:</strong> <?php echo htmlspecialchars($return['original_invoice_number']); ?></p>
                    <p><strong>Total Amount:</strong> Rs. <?php echo number_format($return['total_amount'], 2); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-light">
            <h6 class="mb-0">Handle Return Items</h6>
        </div>
        <div class="card-body">
            <?php if (empty($items)): ?>
                <div class="alert alert-info">No items found in this return.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Return Qty</th>
                                <th>Added to Stock</th>
                                <th>Remaining</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr class="<?php echo $item['resolved'] == 1 ? 'table-success' : ''; ?>">
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['return_qty']); ?></td>
                                    <td><?php echo $item['stock_added'] ? htmlspecialchars($item['stock_added']) : '0'; ?></td>
                                    <td><?php echo htmlspecialchars($item['remaining_qty']); ?></td>
                                    <td>
                                        <?php if ($item['resolved'] == 1): ?>
                                            <span class="badge badge-success">Resolved</span>
                                            <small class="d-block text-muted mt-1" style="font-size: 80%;">
                                                By: <?php echo htmlspecialchars($item['resolved_by_info']); ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($item['resolved'] != 1 && $item['remaining_qty'] > 0): ?>
                                            <button type="button" class="btn btn-sm btn-success add-stock-btn" 
                                                data-item-id="<?php echo $item['id']; ?>"
                                                data-return-id="<?php echo $return_id; ?>"
                                                data-product-name="<?php echo htmlspecialchars($item['product_name']); ?>"
                                                data-return-qty="<?php echo $item['remaining_qty']; ?>">
                                                <i class="fas fa-plus-circle"></i> Add Stock
                                            </button>
                                            
                                            <form method="post" action="../../process/Return_rep_order.php" class="discard-item-form" style="display:inline;">
                                                <input type="hidden" name="action" value="discard_item">
                                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                                <input type="hidden" name="return_id" value="<?php echo $return_id; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-ban"></i> Discard
                                                </button>
                                            </form>
                                        <?php elseif ($item['resolved'] == 1): ?>
                                            <!-- Add reset button for resolved items -->
                                            <form method="post" action="../../process/Return_rep_order.php" class="reset-item-form" style="display:inline;">
                                                <input type="hidden" name="action" value="reset_item">
                                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                                <input type="hidden" name="return_id" value="<?php echo $return_id; ?>">
                                                <button type="submit" class="btn btn-sm btn-warning reset-item-btn">
                                                    <i class="fas fa-redo"></i> Reset
                                                </button>
                                                
                                                <?php if ($item['stock_added'] > 0): ?>
                                                <div class="mt-1 text-info small">
                                                    <i class="fas fa-info-circle"></i> 
                                                    Clicking Reset will remove <?php echo $item['stock_added']; ?> items from inventory and set status to pending.
                                                </div>
                                                <?php endif; ?>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted">No action needed</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Note:</strong> 
                    <ul class="mb-0">
                        <li>Use "Add Stock" to add products back to inventory.</li>
                        <li>Use "Discard" if products should not be returned to inventory.</li>
                        <li>Use "Reset" to undo a previous action. This will remove any previously added stock from inventory and reset the status to pending.</li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>
