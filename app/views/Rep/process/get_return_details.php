<?php
/**
 * Get Return Details Process
 * This file fetches the details of a specific return for display in the modal
 */

// Database connection
require_once '../../../../config/databade.php';

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
    $items_query = "SELECT * FROM return_collection_items WHERE return_id = ?";
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
            <h6 class="mb-0">Return Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Return Number:</strong> <?php echo htmlspecialchars($return['return_bill_number']); ?></p>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars($return['formatted_date']); ?></p>
                    <p><strong>Original Invoice:</strong> <?php echo htmlspecialchars($return['original_invoice_number']); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Customer:</strong> <?php echo htmlspecialchars($return['customer_name']); ?></p>
                    <p><strong>Sales Rep:</strong> <?php echo htmlspecialchars($return['rep_name']); ?></p>
                    <p><strong>Total Amount:</strong> Rs. <?php echo number_format($return['total_amount'], 2); ?></p>
                </div>
            </div>
            
            <div class="mt-3">
                <p><strong>Return Reason:</strong> <?php echo htmlspecialchars($return['reason']); ?></p>
                <?php if (!empty($return['notes'])): ?>
                <p><strong>Notes:</strong> <?php echo htmlspecialchars($return['notes']); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-light">
            <h6 class="mb-0">Returned Items</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="4" class="text-center">No items found</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($item['return_qty']); ?></td>
                                <td class="text-right">Rs. <?php echo number_format($item['unit_price'], 2); ?></td>
                                <td class="text-right">Rs. <?php echo number_format($item['return_amount'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-right">Total:</th>
                            <th class="text-right">Rs. <?php echo number_format($return['total_amount'], 2); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <?php
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>